<?php
if (!defined('ABSPATH')) exit;

// مقدارهای فعلی را می‌گیریم
$secret_key = get_option('lm_secret_key', '');
$allowed_domains = get_option('lm_allowed_domains', '');
$default_duration = get_option('lm_default_duration', 12);
$enable_cors = get_option('lm_enable_cors', false);
?>

<div class="wrap">
    <h1><i class="fas fa-cog"></i> تنظیمات افزونه مدیریت لایسنس</h1>
    <form method="post" action="options.php" class="mt-4">
        <?php settings_fields('lm_settings_group'); ?>
        <div class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="lm_secret_key" class="form-label fw-bold">کلید مخفی (برای تولید کد فعال‌سازی):</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="lm_secret_key" id="lm_secret_key" value="<?php echo esc_attr($secret_key); ?>" />
                    <button class="btn btn-outline-secondary" type="button" onclick="generateSecretKey()"><i class="fas fa-magic"></i> تولید رمز قوی</button>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('lm_secret_key')"><i class="fas fa-copy"></i></button>
                </div>
            </div>

            <div class="mb-3">
                <label for="lm_allowed_domains" class="form-label fw-bold">دامنه‌های مجاز (جدا شده با کاما):</label>
                <input type="text" class="form-control" name="lm_allowed_domains" id="lm_allowed_domains" value="<?php echo esc_attr($allowed_domains); ?>" />
                <div class="form-text">مثال: example.com,myapp.com</div>
            </div>

            <div class="mb-3">
                <label for="lm_default_duration" class="form-label fw-bold">مدت پیش‌فرض اعتبار لایسنس (ماه):</label>
                <input type="number" class="form-control" name="lm_default_duration" id="lm_default_duration" value="<?php echo esc_attr($default_duration); ?>" min="1" />
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="lm_enable_cors" name="lm_enable_cors" value="1" <?php checked($enable_cors, 1); ?>>
                <label class="form-check-label" for="lm_enable_cors">اجازه دسترسی به API از دامنه‌های مجاز (CORS)</label>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> ذخیره تنظیمات</button>
        </div>
    </form>
</div>

<script>
function generateSecretKey() {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=[]{}";
    let key = '';
    for (let i = 0; i < 32; i++) {
        key += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById("lm_secret_key").value = key;
}

function copyToClipboard(id) {
    const el = document.getElementById(id);
    el.select();
    el.setSelectionRange(0, 99999); // For mobile devices
    navigator.clipboard.writeText(el.value).then(() => {
        alert("کپی شد!");
    });
}
</script>