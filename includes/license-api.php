<?php
add_action('rest_api_init', function () {
  register_rest_route('licensemanager/v1', '/activate', [
    'methods' => 'POST',
    'callback' => 'lm_api_activate_license',
    'permission_callback' => '__return_true'
  ]);
  register_rest_route('licensemanager/v1', '/validate', [
    'methods' => 'POST',
    'callback' => 'lm_api_validate_license',
    'permission_callback' => '__return_true'
  ]);
  register_rest_route('licensemanager/v1', '/renew', [
    'methods' => 'POST',
    'callback' => 'lm_api_renew_license',
    'permission_callback' => '__return_true'
  ]);
  register_rest_route('licensemanager/v1', '/user-licenses', [
    'methods' => 'POST',
    'callback' => 'lm_api_user_licenses',
    'permission_callback' => '__return_true'
  ]);
});

function lm_api_check_origin() {
  $allowed = array_map('trim', explode("\n", get_option('lm_allowed_origins', '')));
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
  return !$origin || in_array($origin, $allowed);
}

function lm_api_activate_license($req) {
  if (!lm_api_check_origin()) return new WP_Error('forbidden', 'دسترسی غیرمجاز', ['status' => 403]);

  $data = $req->get_json_params();
  $email = sanitize_email($data['user_email'] ?? '');
  $product_id = intval($data['product_id'] ?? 0);
  $system_code = sanitize_text_field($data['system_code'] ?? '');
  $domain = sanitize_text_field($data['domain'] ?? '');

  $user = get_user_by('email', $email);
  if (!$user || !$product_id || !$system_code) return new WP_Error('invalid', 'اطلاعات نامعتبر', ['status' => 400]);

  global $wpdb;
  $table = $wpdb->prefix . 'license_manager_licenses';
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d AND product_id=%d AND system_code=%s", $user->ID, $product_id, $system_code));

  if ($row) {
    if ($domain) {
      $history = json_decode($row->domain_history ?: '[]', true);
      if (!in_array($domain, $history)) {
        $history[] = $domain;
        $wpdb->update($table, ['domain_history' => json_encode($history)], ['id' => $row->id]);
      }
    }
    return [
      'activation_hash' => $row->activation_hash,
      'expires_at' => $row->expires_at,
      'status' => $row->status
    ];
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
    return [
      'activation_hash' => $hash,
      'expires_at' => $expires,
      'status' => 'valid'
    ];
  }
}

function lm_api_validate_license($req) {
  if (!lm_api_check_origin()) return new WP_Error('forbidden', 'دسترسی غیرمجاز', ['status' => 403]);
  $data = $req->get_json_params();
  $email = sanitize_email($data['user_email'] ?? '');
  $product_id = intval($data['product_id'] ?? 0);
  $system_code = sanitize_text_field($data['system_code'] ?? '');

  $user = get_user_by('email', $email);
  if (!$user) return new WP_Error('invalid', 'کاربر یافت نشد');

  global $wpdb;
  $table = $wpdb->prefix . 'license_manager_licenses';
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d AND product_id=%d AND system_code=%s", $user->ID, $product_id, $system_code));

  if (!$row) return new WP_Error('notfound', 'لایسنس وجود ندارد');
  $expired = strtotime($row->expires_at) < time();
  return [
    'status' => $expired ? 'expired' : 'valid',
    'activation_hash' => $row->activation_hash,
    'expires_at' => $row->expires_at,
    'days_remaining' => $expired ? 0 : floor((strtotime($row->expires_at) - time()) / 86400)
  ];
}

function lm_api_renew_license($req) {
  if (!lm_api_check_origin()) return new WP_Error('forbidden', 'دسترسی غیرمجاز', ['status' => 403]);
  $data = $req->get_json_params();
  $email = sanitize_email($data['user_email'] ?? '');
  $product_id = intval($data['product_id'] ?? 0);
  $system_code = sanitize_text_field($data['system_code'] ?? '');
  $user = get_user_by('email', $email);

  global $wpdb;
  $table = $wpdb->prefix . 'license_manager_licenses';
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d AND product_id=%d AND system_code=%s", $user->ID, $product_id, $system_code));
  if (!$row) return new WP_Error('notfound', 'لایسنس یافت نشد');

  $new_expiry = lm_get_expiry_date(lm_get_license_duration($product_id));
  $wpdb->update($table, ['expires_at' => $new_expiry], ['id' => $row->id]);

  return [
    'expires_at' => $new_expiry,
    'status' => 'valid'
  ];
}

function lm_api_user_licenses($req) {
  if (!lm_api_check_origin()) return new WP_Error('forbidden', 'دسترسی غیرمجاز', ['status' => 403]);
  $email = sanitize_email($req->get_param('user_email') ?? '');
  $user = get_user_by('email', $email);
  if (!$user) return [];

  global $wpdb;
  $table = $wpdb->prefix . 'license_manager_licenses';
  $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE user_id=%d", $user->ID));
  return array_map(function ($r) {
    return [
      'product_id' => $r->product_id,
      'system_code' => $r->system_code,
      'activation_hash' => $r->activation_hash,
      'status' => $r->status,
      'expires_at' => $r->expires_at,
      'domain_history' => json_decode($r->domain_history ?: '[]')
    ];
  }, $rows);
}
