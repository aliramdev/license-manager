<?php
defined('ABSPATH') || exit;

// بارگذاری تنظیمات فعلی
$secret_key = get_option('lm_secret_key', '');
$api_key = get_option('lm_api_key', '');
$allowed_origins = get_option('lm_allowed_origins', '');
$restrict_api_by_origin = get_option('lm_restrict_api_by_origin', 'no');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('lm_settings_save', 'lm_settings_nonce')) {
    // ذخیره تنظیمات
    $secret_key_new = sanitize_text_field($_POST['lm_secret_key'] ?? '');
    $api_key_new = sanitize_text_field($_POST['lm_api_key'] ?? '');
    $allowed_origins_new = sanitize_textarea_field($_POST['lm_allowed_origins'] ?? '');
    $restrict_api_by_origin_new = ($_POST['lm_restrict_api_by_origin'] ?? 'no') === 'yes' ? 'yes' : 'no';

    update_option('lm_secret_key', $secret_key_new);
    update_option('lm_api_key', $api_key_new);
    update_option('lm_allowed_origins', $allowed_origins_new);
    update_option('lm_restrict_api_by_origin', $restrict_api_by_origin_new);

    echo '<div class="notice notice-success is-dismissible"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';

    // بارگذاری مجدد برای نمایش فرم
    $secret_key = $secret_key_new;
    $api_key = $api_key_new;
    $allowed_origins = $allowed_origins_new;
    $restrict_api_by_origin = $restrict_api_by_origin_new;
}

?>

<div class="wrap">
    <h1><i class="fas fa-cogs"></i> تنظیمات مدیریت لایسنس</h1>

    <form method="post" action="">
        <?php wp_nonce_field('lm_settings_save', 'lm_settings_nonce'); ?>

        <table class="form-table">
            <tr>
                <th scope="row"><label for="lm_secret_key">کلید مخفی (Secret Key)</label></th>
                <td>
                    <input type="text" id="lm_secret_key" name="lm_secret_key" class="regular-text" value="<?= esc_attr($secret_key); ?>" />
                    <button type="button" id="generate-secret-key" class="button">تولید رمز قوی</button>
                    <button type="button" id="copy-secret-key" class="button">کپی به کلیپ‌بورد</button>
                    <p class="description">کلیدی که برای تولید هش کد فعالسازی استفاده می‌شود.</p>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="lm_api_key">کلید API (API Key)</label></th>
                <td>
                    <input type="text" id="lm_api_key" name="lm_api_key" class="regular-text" value="<?= esc_attr($api_key); ?>" />
                    <button type="button" id="generate-api-key" class="button">تولید رمز قوی</button>
                    <button type="button" id="copy-api-key" class="button">کپی به کلیپ‌بورد</button>
                    <p class="description">کلید برای احراز هویت درخواست‌های API.</p>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="lm_restrict_api_by_origin">محدود کردن API بر اساس دامنه (CORS)</label></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="lm_restrict_api_by_origin" value="yes" <?= $restrict_api_by_origin === 'yes' ? 'checked' : ''; ?> />
                            فعال‌سازی محدودیت دامنه
                        </label>
                    </fieldset>
                    <textarea name="lm_allowed_origins" id="lm_allowed_origins" rows="5" class="large-text" placeholder="یک دامنه در هر خط وارد کنید"><?= esc_textarea($allowed_origins); ?></textarea>
                    <p class="description">دامنه‌هایی که اجازه دسترسی به API را دارند (هر خط یک دامنه).</p>
                </td>
            </tr>
        </table>

        <?php submit_button('ذخیره تنظیمات'); ?>
    </form>
</div>

<script>
document.getElementById('generate-secret-key').addEventListener('click', function() {
    const key = Array.from(crypto.getRandomValues(new Uint8Array(16))).map(b => b.toString(16).padStart(2, '0')).join('');
    document.getElementById('lm_secret_key').value = key;
});

document.getElementById('copy-secret-key').addEventListener('click', function() {
    const val = document.getElementById('lm_secret_key').value;
    navigator.clipboard.writeText(val).then(() => alert('کلید مخفی کپی شد!'));
});

document.getElementById('generate-api-key').addEventListener('click', function() {
    const key = Array.from(crypto.getRandomValues(new Uint8Array(16))).map(b => b.toString(16).padStart(2, '0')).join('');
    document.getElementById('lm_api_key').value = key;
});

document.getElementById('copy-api-key').addEventListener('click', function() {
    const val = document.getElementById('lm_api_key').value;
    navigator.clipboard.writeText(val).then(() => alert('کلید API کپی شد!'));
});
</script>
