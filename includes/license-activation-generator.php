<?php
defined('ABSPATH') or die('No script kiddies please!');

function lm_render_activation_generator_page() {
    if (!current_user_can('manage_options')) wp_die('You do not have sufficient permissions.');

    global $wpdb;
    $licenses_table = $wpdb->prefix . 'lm_licenses';
    $activations_table = $wpdb->prefix . 'lm_activation_codes';

    $users = lm_get_users_list();
    $products = lm_get_products_list();

    $message = '';
    $selected_user = intval($_POST['user_id'] ?? 0);
    $selected_license = intval($_POST['license_id'] ?? 0);
    $system_code = sanitize_text_field($_POST['system_code'] ?? '');
    $expires_at = sanitize_text_field($_POST['expires_at'] ?? '');
    $domain = sanitize_text_field($_POST['domain'] ?? '');

    $licenses_for_user = [];
    if ($selected_user) {
        $licenses_for_user = $wpdb->get_results($wpdb->prepare("SELECT * FROM $licenses_table WHERE user_id = %d", $selected_user));
    }

    if (isset($_POST['lm_generate_activation'])) {
        check_admin_referer('lm_generate_activation_nonce');

        if (!$selected_user || !$selected_license || !$system_code) {
            $message = '<div class="alert alert-danger">لطفاً تمام فیلدهای ضروری را تکمیل کنید.</div>';
        } else {
            $secret_key = get_option('lm_secret_key', '');
            $activation_code = hash('sha256', $secret_key . $system_code);

            // تاریخ انقضا را اگر وارد نشده از متای محصول بگیریم
            if (!$expires_at) {
                $license = $wpdb->get_row($wpdb->prepare("SELECT * FROM $licenses_table WHERE id = %d", $selected_license));
                if ($license) {
                    $product_expire = get_post_meta($license->product_id, '_lm_license_expire_months', true);
                    if ($product_expire) {
                        $expires_at = date('Y-m-d', strtotime("+$product_expire months"));
                    }
                }
            }

            $now = current_time('mysql');

            // ثبت در جدول فعالسازی
            $wpdb->insert($activations_table, [
                'license_id' => $selected_license,
                'user_id' => $selected_user,
                'product_id' => $products[$license->product_id] ?? 0,
                'system_code' => $system_code,
                'activation_code' => $activation_code,
                'domain' => $domain,
                'status' => 'active',
                'expires_at' => $expires_at ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $message = '<div class="alert alert-success">کد فعالسازی ایجاد شد: <code>' . esc_html($activation_code) . '</code></div>';
        }
    }

    ?>
    <div class="wrap container mt-4">
        <h1><i class="fas fa-key"></i> تولید کد فعالسازی</h1>
        <?php echo $message; ?>
        <form method="post" action="">
            <?php wp_nonce_field('lm_generate_activation_nonce'); ?>
            <div class="mb-3">
                <label for="user_id" class="form-label">انتخاب کاربر</label>
                <select name="user_id" id="user_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- انتخاب کاربر --</option>
                    <?php foreach ($users as $id => $name) : ?>
                    <option value="<?php echo esc_attr($id); ?>" <?php selected($selected_user, $id); ?>><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($selected_user): ?>
            <div class="mb-3">
                <label for="license_id" class="form-label">انتخاب لایسنس</label>
                <select name="license_id" id="license_id" class="form-select" required>
                    <option value="">-- انتخاب لایسنس --</option>
                    <?php foreach ($licenses_for_user as $lic): ?>
                    <option value="<?php echo esc_attr($lic->id); ?>" <?php selected($selected_license, $lic->id); ?>>
                        <?php echo esc_html($products[$lic->product_id] ?? '') . " | " . esc_html($lic->license_code); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="system_code" class="form-label">کد سیستم (Hardware ID)</label>
                <input type="text" name="system_code" id="system_code" class="form-control" value="<?php echo esc_attr($system_code); ?>" required />
            </div>
            <div class="mb-3">
                <label for="domain" class="form-label">دامنه (اختیاری)</label>
                <input type="text" name="domain" id="domain" class="form-control" value="<?php echo esc_attr($domain); ?>" />
            </div>
            <div class="mb-3">
                <label for="expires_at" class="form-label">تاریخ انقضا (YYYY-MM-DD)</label>
                <input type="date" name="expires_at" id="expires_at" class="form-control" value="<?php echo esc_attr($expires_at); ?>" />
                <small class="form-text text-muted">اگر وارد نکنید از متای محصول گرفته می‌شود.</small>
            </div>

            <button type="submit" name="lm_generate_activation" class="btn btn-success"><i class="fas fa-check"></i> تولید کد فعالسازی</button>
            <?php endif; ?>
        </form>
    </div>
    <?php
}
