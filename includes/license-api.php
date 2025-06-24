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

// این توابع callback فعالسازی، اعتبارسنجی، تمدید و لیست لایسنس‌ها را باید تعریف کنیم
// (تعریف نمونه lm_api_activate_license و بقیه در includes/license-generator.php)
    