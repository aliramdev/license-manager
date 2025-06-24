<?php
function lm_settings_page() {
  if (isset($_POST['lm_save_settings'])) {
    update_option('lm_secret_key', sanitize_text_field($_POST['lm_secret_key']));
    update_option('lm_default_months', intval($_POST['lm_default_months']));
    update_option('lm_allowed_origins', sanitize_textarea_field($_POST['lm_allowed_origins']));
    echo '<div class="updated"><p>تنظیمات ذخیره شد.</p></div>';
  }

  $key = get_option('lm_secret_key', '');
  $months = get_option('lm_default_months', 1);
  $origins = get_option('lm_allowed_origins', '');

  ?>
  <div class="wrap">
    <h2>⚙️ تنظیمات افزونه مدیریت لایسنس</h2>
    <form method="post">
      <table class="form-table">
        <tr>
          <th><label for="lm_secret_key">🔑 کلید هش (مخفی)</label></th>
          <td>
            <input type="text" name="lm_secret_key" value="<?php echo esc_attr($key); ?>" style="width: 300px">
            <button type="button" onclick="document.getElementsByName('lm_secret_key')[0].value = Math.random().toString(36).substring(2, 34);">🎲 تولید</button>
          </td>
        </tr>
        <tr>
          <th><label for="lm_default_months">📆 مدت اعتبار پیش‌فرض (ماه)</label></th>
          <td><input type="number" name="lm_default_months" value="<?php echo esc_attr($months); ?>"></td>
        </tr>
        <tr>
          <th><label for="lm_allowed_origins">🌐 دامنه‌های مجاز (هر خط یک دامنه)</label></th>
          <td><textarea name="lm_allowed_origins" rows="5" cols="50"><?php echo esc_textarea($origins); ?></textarea></td>
        </tr>
      </table>
      <input type="submit" name="lm_save_settings" class="button button-primary" value="💾 ذخیره تنظیمات">
    </form>
  </div>
<?php
}
