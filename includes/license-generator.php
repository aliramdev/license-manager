<?php
function lm_license_list_page() {
  global $wpdb;
  $table = $wpdb->prefix . 'license_manager_licenses';

  $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
  $query = "SELECT * FROM $table WHERE 1=1";
  if ($search) {
    $query .= $wpdb->prepare(" AND (system_code LIKE %s OR domain_history LIKE %s)", "%$search%", "%$search%");
  }
  $results = $wpdb->get_results($query);

  echo '<div class="wrap">';
  echo '<h2>📄 لیست لایسنس‌ها</h2>';
  echo '<form method="get">
    <input type="hidden" name="page" value="license_manager_list">
    <input type="search" name="s" value="' . esc_attr($search) . '" placeholder="جستجو بر اساس کد سیستم یا دامنه">
    <input type="submit" class="button" value="🔍 جستجو">
  </form><br>';

  if ($results) {
    echo '<table class="widefat striped">
      <thead><tr>
        <th>ایمیل</th><th>محصول</th><th>کد سیستم</th><th>هش</th><th>دامنه‌ها</th><th>انقضا</th><th>وضعیت</th><th>عملیات</th>
      </tr></thead><tbody>';
    foreach ($results as $row) {
      $user = get_user_by('id', $row->user_id);
      $domains = implode('<br>', json_decode($row->domain_history ?: '[]', true));
      echo '<tr>
        <td>' . esc_html($user ? $user->user_email : '-') . '</td>
        <td>' . esc_html($row->product_id) . '</td>
        <td>' . esc_html($row->system_code) . '</td>
        <td style="font-family:monospace;direction:ltr">' . esc_html(substr($row->activation_hash, 0, 24)) . '...</td>
        <td>' . $domains . '</td>
        <td>' . esc_html($row->expires_at) . '</td>
        <td>' . esc_html($row->status) . '</td>
        <td>
          <form method="post" style="display:inline-block">
            <input type="hidden" name="delete_license_id" value="' . intval($row->id) . '">
            <button class="button button-small" onclick="return confirm(\'حذف این لایسنس؟\')">🗑 حذف</button>
          </form>
        </td>
      </tr>';
    }
    echo '</tbody></table>';
  } else {
    echo '<p>هیچ لایسنسی یافت نشد.</p>';
  }
  echo '</div>';

  // حذف لایسنس
  if (isset($_POST['delete_license_id'])) {
    $del_id = intval($_POST['delete_license_id']);
    $wpdb->delete($table, ['id' => $del_id]);
    echo '<div class="updated"><p>لایسنس حذف شد.</p></div>';
    echo '<meta http-equiv="refresh" content="1">';
  }
}
