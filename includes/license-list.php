<?php
function lm_license_generator_page() {
  global $wpdb;
  $message = '';

  if (isset($_POST['generate_license'])) {
    $email = sanitize_email($_POST['user_email']);
    $product_id = intval($_POST['product_id']);
    $system_code = sanitize_text_field($_POST['system_code']);
    $domain = sanitize_text_field($_POST['domain']);

    $user = get_user_by('email', $email);
    if (!$user) {
      $message = '❌ کاربر یافت نشد.';
    } else {
      $table = $wpdb->prefix . 'license_manager_licenses';
      $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d AND product_id=%d AND system_code=%s", $user->ID, $product_id, $system_code));

      if ($existing) {
        $message = '⚠️ این لایسنس قبلاً وجود دارد.';
      } else {
        $hash = lm_generate_activation_hash($system_code);
        $expires = lm_get_expiry_date(lm_get_license_duration($product_id));
        $created = current_time('mysql');
        $wpdb->insert($table, [
          'user_id' => $user->ID,
          'product_id' => $product_id,
          'system_code' => $system_code,
          'activation_hash' => $hash,
          'created_at' => $created,
          'expires_at' => $expires,
          'status' => 'valid',
          'domain_history' => json_encode($domain ? [$domain] : [])
        ]);
        $message = '✅ لایسنس با موفقیت ایجاد شد.';
      }
    }
  }

  echo '<div class="wrap">';
  echo '<h2>➕ تولید لایسنس جدید</h2>';
  if ($message) echo "<p><strong>$message</strong></p>";
  ?>
  <form method="post">
    <table class="form-table">
      <tr>
        <th><label for="user_email">ایمیل کاربر</label></th>
        <td><input type="email" name="user_email" required></td>
      </tr>
      <tr>
        <th><label for="product_id">شناسه محصول (ID)</label></th>
        <td><input type="number" name="product_id" required></td>
      </tr>
      <tr>
        <th><label for="system_code">کد سیستم</label></th>
        <td><input type="text" name="system_code" required></td>
      </tr>
      <tr>
        <th><label for="domain">دامنه (اختیاری)</label></th>
        <td><input type="text" name="domain"></td>
      </tr>
    </table>
    <input type="submit" name="generate_license" class="button button-primary" value="🎫 تولید لایسنس">
  </form>
  <?php
  echo '</div>';
}
