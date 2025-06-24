<?php
defined('ABSPATH') or die('No script kiddies please!');

function lm_render_settings_page() {
    if (!current_user_can('manage_options')) wp_die('You do not have sufficient permissions.');

    if (isset($_POST['lm_save_settings'])) {
        check_admin_referer('lm_settings_nonce');
        $secret_key = sanitize_text_field($_POST['lm_secret_key'] ?? '');
        update_option('lm_secret_key', $secret_key);
        add_settings_error('lm_messages', 'lm_message', 'تنظیمات ذخیره شد.', 'updated');
    }

    $secret_key = get_option('lm_secret_key', '');

    ?>
    <div class="wrap container mt-4">
        <h1><i class="fas fa-cogs"></i> تنظیمات مدیریت لایسنس</h1>
        <?php settings_errors('lm_messages'); ?>
        <form method="post" action="">
            <?php wp_nonce_field('lm_settings_nonce'); ?>
            <div class="mb-3">
                <label for="lm_secret_key" class="form-label">کلید مخفی API</label>
                <div class="input-group">
                    <input type="text" name="lm_secret_key" id="lm_secret_key" class="form-control" value="<?php echo esc_attr($secret_key); ?>" />
                    <button type="button" class="btn btn-outline-secondary" id="generateSecretKeyBtn"><i class="fas fa-key"></i> تولید رمز قوی</button>
                    <button type="button" class="btn btn-outline-secondary" id="copySecretKeyBtn"><i class="fas fa-copy"></i> کپی به کلیپ‌بورد</button>
                </div>
                <small class="form-text text-muted">از این کلید برای امن کردن ارتباط API استفاده می‌شود.</small>
            </div>
            <button type="submit" name="lm_save_settings" class="btn btn-primary">ذخیره تنظیمات</button>
        </form>
    </div>
    <script>
    jQuery(function($){
        $('#generateSecretKeyBtn').click(function(){
            let randomKey = [...Array(32)].map(() => Math.random().toString(36)[2]).join('');
            $('#lm_secret_key').val(randomKey);
        });
        $('#copySecretKeyBtn').click(function(){
            navigator.clipboard.writeText($('#lm_secret_key').val()).then(() => {
                alert('کلید مخفی کپی شد!');
            });
        });
    });
    </script>
    <?php
}
