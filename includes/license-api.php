<?php
defined('ABSPATH') or die('No script kiddies please!');

add_action('rest_api_init', function () {
    register_rest_route('licensemanager/v1', '/activate', [
        'methods' => 'POST',
        'callback' => 'lm_api_activate_license',
        'permission_callback' => 'lm_api_check_secret_key',
    ]);
    register_rest_route('licensemanager/v1', '/validate', [
        'methods' => 'POST',
        'callback' => 'lm_api_validate_license',
        'permission_callback' => 'lm_api_check_secret_key',
    ]);
    register_rest_route('licensemanager/v1', '/renew', [
        'methods' => 'POST',
        'callback' => 'lm_api_renew_license',
        'permission_callback' => 'lm_api_check_secret_key',
    ]);
    register_rest_route('licensemanager/v1', '/user-licenses', [
        'methods' => 'POST',
        'callback' => 'lm_api_get_user_licenses',
        'permission_callback' => 'lm_api_check_secret_key',
    ]);
});

// بررسی کلید مخفی
function lm_api_check_secret_key(WP_REST_Request $request) {
    $secret_key = get_option('lm_secret_key', '');
    $passed_key = $request->get_header('X-LM-Secret') ?? $request->get_param('secret_key');
    return $passed_key === $secret_key;
}

// فعالسازی لایسنس
function lm_api_activate_license(WP_REST_Request $request) {
    global $wpdb;
    $activations_table = $wpdb->prefix . 'lm_activation_codes';

    $user_id = intval($request->get_param('user_id'));
    $product_id = intval($request->get_param('product_id'));
    $system_code = sanitize_text_field($request->get_param('system_code'));
    $domain = sanitize_text_field($request->get_param('domain'));
    $now = current_time('mysql');

    if (!$user_id || !$product_id || !$system_code) {
        return new WP_REST_Response(['error' => 'Missing parameters'], 400);
    }

    $secret_key = get_option('lm_secret_key', '');
    $activation_code = hash('sha256', $secret_key . $system_code);

    $exists = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $activations_table WHERE user_id=%d AND product_id=%d AND system_code=%s",
        $user_id, $product_id, $system_code
    ));

    if ($exists) {
        if ($exists->status !== 'active') {
            return new WP_REST_Response(['error' => 'Activation is inactive'], 403);
        }
        return new WP_REST_Response([
            'activation_code' => $exists->activation_code,
            'expires_at' => $exists->expires_at,
            'status' => $exists->status,
        ]);
    }

    $license = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}lm_licenses WHERE user_id=%d AND product_id=%d",
        $user_id, $product_id
    ));
    if (!$license) {
        return new WP_REST_Response(['error' => 'License not found'], 404);
    }

    $expires_at = null;
    $product_expire = get_post_meta($product_id, '_lm_license_expire_months', true);
    if ($product_expire) {
        $expires_at = date('Y-m-d', strtotime("+$product_expire months"));
    }

    $wpdb->insert($activations_table, [
        'license_id' => $license->id,
        'user_id' => $user_id,
        'product_id' => $product_id,
        'system_code' => $system_code,
        'activation_code' => $activation_code,
        'domain' => $domain,
        'status' => 'active',
        'expires_at' => $expires_at,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    return new WP_REST_Response([
        'activation_code' => $activation_code,
        'expires_at' => $expires_at,
        'status' => 'active',
    ]);
}

// اعتبارسنجی لایسنس
function lm_api_validate_license(WP_REST_Request $request) {
    global $wpdb;
    $activations_table = $wpdb->prefix . 'lm_activation_codes';

    $user_id = intval($request->get_param('user_id'));
    $product_id = intval($request->get_param('product_id'));
    $system_code = sanitize_text_field($request->get_param('system_code'));

    if (!$user_id || !$product_id || !$system_code) {
        return new WP_REST_Response(['error' => 'Missing parameters'], 400);
    }

    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $activations_table WHERE user_id=%d AND product_id=%d AND system_code=%s AND status='active'",
        $user_id, $product_id, $system_code
    ));

    if (!$row) {
        return new WP_REST_Response(['valid' => false], 200);
    }

    if ($row->expires_at && strtotime($row->expires_at) < current_time('timestamp')) {
        return new WP_REST_Response(['valid' => false, 'reason' => 'expired'], 200);
    }

    return new WP_REST_Response(['valid' => true], 200);
}

// تمدید لایسنس
function lm_api_renew_license(WP_REST_Request $request) {
    global $wpdb;
    $activations_table = $wpdb->prefix . 'lm_activation_codes';

    $activation_code = sanitize_text_field($request->get_param('activation_code'));
    $additional_months = intval($request->get_param('months'));

    if (!$activation_code || !$additional_months) {
        return new WP_REST_Response(['error' => 'Missing parameters'], 400);
    }

    $activation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $activations_table WHERE activation_code=%s",
        $activation_code
    ));

    if (!$activation) {
        return new WP_REST_Response(['error' => 'Activation code not found'], 404);
    }

    $new_expiry = $activation->expires_at ? date('Y-m-d', strtotime("+$additional_months months", strtotime($activation->expires_at))) : date('Y-m-d', strtotime("+$additional_months months"));

    $wpdb->update($activations_table, [
        'expires_at' => $new_expiry,
        'updated_at' => current_time('mysql'),
    ], ['id' => $activation->id]);

    return new WP_REST_Response(['message' => 'License renewed', 'new_expiry' => $new_expiry]);
}

// دریافت لیست لایسنس‌های کاربر
function lm_api_get_user_licenses(WP_REST_Request $request) {
    global $wpdb;
    $licenses_table = $wpdb->prefix . 'lm_licenses';

    $user_id = intval($request->get_param('user_id'));
    if (!$user_id) {
        return new WP_REST_Response(['error' => 'Missing user_id'], 400);
    }

    $licenses = $wpdb->get_results($wpdb->prepare("SELECT * FROM $licenses_table WHERE user_id = %d", $user_id));
    $result = [];
    foreach ($licenses as $license) {
        $result[] = [
            'id' => $license->id,
            'product_id' => $license->product_id,
            'license_code' => $license->license_code,
            'status' => $license->status,
            'created_at' => $license->created_at,
            'updated_at' => $license->updated_at,
        ];
    }
    return $result;
}
