<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_menu_page(
        'مدیریت لایسنس',
        'مدیریت لایسنس',
        'manage_options',
        'lm_settings',
        'lm_render_settings_page',
        'dashicons-shield-alt',
        81
    );
});

function lm_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('دسترسی غیرمجاز!');
    }

    if (isset($_POST['lm_save_settings']) && check_admin_referer('lm_settings_nonce')) {
        update_option('lm_secret_key', sanitize_text_field($_POST['lm_secret_key']));
        update_option('lm_allowed_origins', sanitize_textarea_field($_POST['lm_allowed_origins']));
        echo '<div class="notice notice-success is-dismissible"><p>تنظیمات با موفقیت ذخیره شد.</p></div>';
    }

    $secret_key = get_option('lm_secret_key', '');
    $allowed_origins = get_option('lm_allowed_origins', '');
    ?>
    <div class="wrap">
        <h1>تنظیمات افزونه مدیریت لایسنس</h1>
        <form method="post">
            <?php wp_nonce_field('lm_settings_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="lm_secret_key">کلید مخفی (Secret Key)</label></th>
                    <td><input type="text" name="lm_secret_key" id="lm_secret_key" value="<?= esc_attr($secret_key) ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="lm_allowed_origins">دامنه‌های مجاز (Allowed Origins) - هر خط یک دامنه</label></th>
                    <td><textarea name="lm_allowed_origins" id="lm_allowed_origins" rows="5" class="large-text"><?= esc_textarea($allowed_origins) ?></textarea></td>
                </tr>
            </table>
            <p><input type="submit" name="lm_save_settings" class="button button-primary" value="ذخیره تنظیمات" /></p>
        </form>
    </div>
    <?php
}
