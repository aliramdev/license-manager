<?php
defined('ABSPATH') || exit;

$users = get_users(['fields' => ['ID', 'display_name', 'user_email']]);
$nonce = wp_create_nonce('lm_get_user_licenses_nonce');
?>
<?php
defined('ABSPATH') || exit;

// بررسی ارسال فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lm_action']) && $_POST['lm_action'] === 'generate_activation') {
    global $wpdb;

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $license_id = isset($_POST['license_id']) ? intval($_POST['license_id']) : 0;
    $system_code = sanitize_text_field($_POST['system_code'] ?? '');
    $domain = sanitize_text_field($_POST['domain'] ?? '');
    $expires_at = !empty($_POST['expires_at']) ? sanitize_text_field($_POST['expires_at']) : null;

    if (!$user_id || !$license_id || !$system_code) {
        echo '<div class="notice notice-error"><p>لطفاً همه فیلدهای ضروری را پر کنید.</p></div>';
    } else {
        // گرفتن اطلاعات لایسنس
        $license = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}lm_licenses WHERE id = %d",
            $license_id
        ));

        if (!$license) {
            echo '<div class="notice notice-error"><p>لایسنس معتبر یافت نشد.</p></div>';
        } else {
            $product_id = $license->product_id;
            $table_activation = $wpdb->prefix . 'lm_activation_codes';
            $created_at = current_time('mysql');
            $updated_at = current_time('mysql');

            $secret_key = get_option('lm_secret_key', 'secret123');
            $activation_code = hash_hmac('sha256', $system_code, $secret_key);

            // بررسی تکراری نبودن
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_activation} WHERE license_id = %d AND system_code = %s",
                $license_id,
                $system_code
            ));

            if ($existing) {
                echo '<div class="notice notice-warning"><p>برای این لایسنس و کد سیستم قبلاً کد فعال‌سازی ایجاد شده است.</p></div>';
            } else {
                $inserted = $wpdb->insert($table_activation, [
                    'license_id' => $license_id,
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'system_code' => $system_code,
                    'activation_code' => $activation_code,
                    'domain' => $domain,
                    'expires_at' => $expires_at ? date('Y-m-d', strtotime($expires_at)) : null,
                    'created_at' => $created_at,
                    'updated_at' => $updated_at,
                    'status' => 'active',
                ]);

                if ($inserted) {
                    echo '<div class="notice notice-success"><p>✅ کد فعال‌سازی با موفقیت ذخیره شد.</p></div>';
                } else {
                    echo '<div class="notice notice-error"><p>❌ خطا در ذخیره‌سازی کد فعال‌سازی. لطفاً لاگ بررسی شود.</p></div>';
                }
            }
        }
    }
}

?>


<div class="wrap">
    <h1 class="lm-header"><i class="fas fa-lock"></i> تولید کد فعال‌سازی</h1>

    <form method="post" action="" class="lm-form">
        <input type="hidden" name="lm_action" value="generate_activation">

        <div class="mb-3">
            <label for="user_id" class="form-label">انتخاب کاربر:</label>
            <select name="user_id" id="user_id" class="form-select" required onchange="loadUserLicenses(this.value)">
                <option value="">— انتخاب کنید —</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= esc_attr($user->ID); ?>">
                        <?= esc_html($user->display_name . " ({$user->user_email})"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3" id="licenseContainer">
            <label for="license_id" class="form-label">لایسنس مربوطه:</label>
            <select name="license_id" id="license_id" class="form-select" required>
                <option value="">لطفاً ابتدا کاربر را انتخاب کنید</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="system_code" class="form-label">کد سیستم (System ID):</label>
            <input type="text" name="system_code" id="system_code" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="domain" class="form-label">دامنه (اختیاری):</label>
            <input type="text" name="domain" id="domain" class="form-control" placeholder="example.com">
        </div>

        <div class="mb-3">
            <label for="expires_at" class="form-label">تاریخ انقضا (اختیاری):</label>
            <input type="date" name="expires_at" id="expires_at" class="form-control">
        </div>

        <?php submit_button('تولید کد فعال‌سازی', 'primary'); ?>
    </form>
</div>

<script>
    function loadUserLicenses(userId) {
        if (!userId) {
            const select = document.getElementById('license_id');
            select.innerHTML = '<option value="">لطفاً ابتدا کاربر را انتخاب کنید</option>';
            return;
        }

        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'lm_get_user_licenses',
                user_id: userId,
                _ajax_nonce: '<?= $nonce ?>'
            },
            success: function (response) {
                const select = document.getElementById('license_id');
                select.innerHTML = '';
                if (response.success && response.data.length > 0) {
                    response.data.forEach(function (item) {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.product_name + ' - ' + item.license_key;
                        select.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.textContent = 'لایسنسی یافت نشد';
                    select.appendChild(opt);
                }
            },
            error: function (xhr, status, error) {
                alert('خطا در دریافت لیست لایسنس‌ها: ' + error);
                console.error(xhr.responseText);
            }
        });
    }
</script>