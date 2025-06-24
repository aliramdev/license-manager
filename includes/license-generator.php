<?php
defined('ABSPATH') or die('No script kiddies please!');

function lm_generate_license_code() {
    return wp_generate_password(20, false, false);
}

function lm_create_license($user_id, $product_id) {
    global $wpdb;
    $licenses_table = $wpdb->prefix . 'lm_licenses';

    $code = lm_generate_license_code();
    $now = current_time('mysql');

    $wpdb->insert($licenses_table, [
        'user_id' => $user_id,
        'product_id' => $product_id,
        'license_code' => $code,
        'status' => 'active',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return $wpdb->insert_id;
}

function lm_render_license_generator_page() {
    if (!current_user_can('manage_options')) wp_die('You do not have sufficient permissions.');

    $users = lm_get_users_list();
    $products = lm_get_products_list();
    $message = '';

    if (isset($_POST['lm_generate_license'])) {
        check_admin_referer('lm_generate_license_nonce');

        $user_id = intval($_POST['user_id'] ?? 0);
        $product_id = intval($_POST['product_id'] ?? 0);

        if (!$user_id || !$product_id) {
            $message = '<div class="alert alert-danger">لطفاً کاربر و محصول را انتخاب کنید.</div>';
        } else {
            $license_id = lm_create_license($user_id, $product_id);
            if ($license_id) {
                $message = '<div class="alert alert-success">لایسنس با موفقیت ایجاد شد.</div>';
            } else {
                $message = '<div class="alert alert-danger">خطا در ایجاد لایسنس.</div>';
            }
        }
    }

    ?>
    <div class="wrap container mt-4">
        <h1><i class="fas fa-plus-circle"></i> تولید لایسنس جدید</h1>
        <?php echo $message; ?>
        <form method="post" action="">
            <?php wp_nonce_field('lm_generate_license_nonce'); ?>
            <div class="mb-3">
                <label for="user_id" class="form-label">انتخاب کاربر</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">-- انتخاب کاربر --</option>
                    <?php foreach ($users as $id => $name) : ?>
                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="product_id" class="form-label">انتخاب محصول</label>
                <select name="product_id" id="product_id" class="form-select" required>
                    <option value="">-- انتخاب محصول --</option>
                    <?php foreach ($products as $id => $title) : ?>
                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="lm_generate_license" class="btn btn-success"><i class="fas fa-check"></i> تولید</button>
        </form>
    </div>
    <?php
}