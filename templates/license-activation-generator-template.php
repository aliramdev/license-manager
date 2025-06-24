<?php
defined('ABSPATH') || exit;

$users = get_users(['fields' => ['ID', 'display_name', 'user_email']]);
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
    if (!userId) return;

    jQuery.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'lm_get_user_licenses',
            user_id: userId
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
        }
    });
}
</script>
