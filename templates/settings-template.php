<?php
defined('ABSPATH') || exit;

$api_key = get_option('lm_api_key');
$secret_key = get_option('lm_secret_key');
$allowed_domains = get_option('lm_allowed_domains');
?>

<div class="wrap">
    <h1 class="lm-header"><i class="fas fa-cogs"></i> تنظیمات افزونه مدیریت لایسنس</h1>

    <form method="post" action="options.php" class="lm-form">
        <?php settings_fields('lm_settings_group'); ?>
        <?php do_settings_sections('lm_settings_group'); ?>

        <div class="lm-box">
            <label for="lm_api_key">کلید API</label>
            <div class="input-group">
                <input type="text" id="lm_api_key" name="lm_api_key" class="form-control" value="<?php echo esc_attr($api_key); ?>">
                <button type="button" class="btn btn-outline-secondary lm-generate-btn" data-target="#lm_api_key">
                    <i class="fas fa-magic"></i> تولید رمز
                </button>
                <button type="button" class="btn btn-outline-primary lm-copy-btn" data-target="#lm_api_key">
                    <i class="fas fa-copy"></i> کپی
                </button>
            </div>
        </div>

        <div class="lm-box">
            <label for="lm_secret_key">کلید مخفی هش</label>
            <input type="text" id="lm_secret_key" name="lm_secret_key" class="form-control" value="<?php echo esc_attr($secret_key); ?>">
        </div>

        <div class="lm-box">
            <label for="lm_allowed_domains">دامنه‌های مجاز (جدا شده با کاما)</label>
            <textarea id="lm_allowed_domains" name="lm_allowed_domains" class="form-control" rows="2"><?php echo esc_textarea($allowed_domains); ?></textarea>
        </div>

        <?php submit_button('ذخیره تنظیمات', 'primary lm-btn'); ?>
    </form>
</div>
