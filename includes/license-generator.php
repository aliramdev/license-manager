<?php
defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'functions.php';

function lm_api_activate_license($request) {
    $params = $request->get_json_params();

    $user_email = lm_sanitize_text($params['user_email'] ?? '');
    $product_id = intval($params['product_id'] ?? 0);
    $system_code = lm_sanitize_text($params['system_code'] ?? '');
    $domain = lm_sanitize_text($params['domain'] ?? '');

    if (!$user_email || !$product_id || !$system_code) {
        return new WP_Error('invalid_data', 'Missing required fields', ['status' => 400]);
    }

    // دریافت شناسه کاربر
    $user = get_user_by('email', $user_email);
    if (!$user) {
        return new WP_Error('user_not_found', 'User not found', ['status' => 404]);
    }

    // جستجوی لایسنس موجود یا ساخت جدید (پایگاه داده و متا)
    $license = lm_get_or_create_license($user->ID, $product_id, $system_code);

    // ساخت کد فعالسازی هش شده
    $secret = get_option('lm_secret_key', '');
    $activation_code = lm_generate_activation_hash($system_code, $secret);

    // ذخیره کد فعالسازی و دامنه و تاریخ انقضا
    $expires_at = lm_get_product_expiry_date($product_id);
    lm_save_activation_code($license->id, $activation_code, $domain, $expires_at);

    return [
        'activation_hash' => $activation_code,
        'expires_at' => $expires_at,
        'status' => 'valid',
    ];
}

// توابع کمکی زیر را نیز باید تعریف کنیم:
// lm_get_or_create_license($user_id, $product_id, $system_code)
// lm_get_product_expiry_date($product_id)
// lm_save_activation_code($license_id, $activation_code, $domain, $expires_at)
