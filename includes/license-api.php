<?php
defined('ABSPATH') || exit;

add_action('rest_api_init', function () {
    register_rest_route('licensemanager/v1', '/activate', [
        'methods' => 'POST',
        'callback' => 'lm_api_activate_license',
        'permission_callback' => 'lm_api_permission_check',
    ]);

    register_rest_route('licensemanager/v1', '/validate', [
        'methods' => 'POST',
        'callback' => 'lm_api_validate_license',
        'permission_callback' => 'lm_api_permission_check',
    ]);

    register_rest_route('licensemanager/v1', '/renew', [
        'methods' => 'POST',
        'callback' => 'lm_api_renew_license',
        'permission_callback' => 'lm_api_permission_check',
    ]);

    register_rest_route('licensemanager/v1', '/user-licenses', [
        'methods' => 'POST',
        'callback' => 'lm_api_list_user_licenses',
        'permission_callback' => 'lm_api_permission_check',
    ]);
});

function lm_api_permission_check($request) {
    $headers = $request->get_headers();
    if (empty($headers['lm-secret-key'])) {
        return new WP_Error('forbidden', 'Secret key missing', ['status' => 403]);
    }
    $provided_key = $headers['lm-secret-key'][0];
    if (!lm_verify_secret_key($provided_key)) {
        return new WP_Error('forbidden', 'Invalid secret key', ['status' => 403]);
    }
    return true;
}

// Callback برای لیست لایسنس‌های کاربر بر اساس user_id
function lm_api_list_user_licenses(WP_REST_Request $request) {
    $params = $request->get_json_params();

    if (empty($params['user_id'])) {
        return new WP_Error('missing_user_id', 'user_id is required', ['status' => 400]);
    }

    $user_id = intval($params['user_id']);
    if (!get_userdata($user_id)) {
        return new WP_Error('invalid_user', 'Invalid user_id', ['status' => 404]);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'lm_licenses';

    $licenses = $wpdb->get_results($wpdb->prepare(
        "SELECT id, license_code, product_id FROM $table_name WHERE user_id = %d AND status = 'active' ORDER BY id DESC",
        $user_id
    ));

    if (!$licenses) {
        return rest_ensure_response([]);
    }

    $result = [];
    foreach ($licenses as $license) {
        $product_name = get_the_title($license->product_id);
        $result[] = [
            'id' => $license->id,
            'license_key' => $license->license_code,
            'product_name' => $product_name,
        ];
    }

    return rest_ensure_response($result);
}