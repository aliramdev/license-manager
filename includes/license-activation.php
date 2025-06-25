<?php
defined('ABSPATH') || exit;

add_action('rest_api_init', function () {
    register_rest_route('licensemanager/v1', '/generate-activation', [
        'methods' => 'POST',
        'callback' => 'lm_api_generate_activation_code',
        'permission_callback' => 'lm_check_api_secret_key',
    ]);

    register_rest_route('licensemanager/v1', '/activation-list', [
        'methods' => 'POST',
        'callback' => 'lm_api_list_activation_codes',
        'permission_callback' => 'lm_check_api_secret_key',
    ]);

    register_rest_route('licensemanager/v1', '/deactivate-code', [
        'methods' => 'POST',
        'callback' => 'lm_api_deactivate_activation_code',
        'permission_callback' => 'lm_check_api_secret_key',
    ]);
});

function lm_api_generate_activation_code($request) {
    global $wpdb;
    $params = $request->get_json_params();

    $license_id   = absint($params['license_id'] ?? 0);
    $system_code  = sanitize_text_field($params['system_code'] ?? '');
    $domain       = sanitize_text_field($params['domain'] ?? '');
    $expires_at   = sanitize_text_field($params['expires_at'] ?? '');
    $created_at   = current_time('mysql');
    $updated_at   = current_time('mysql');

    if (!$license_id || !$system_code) {
        return new WP_Error('missing_fields', 'License ID and system code are required.', ['status' => 400]);
    }

    // گرفتن اطلاعات لایسنس برای استخراج product_id و user_id
    $license = $wpdb->get_row($wpdb->prepare(
        "SELECT product_id, user_id FROM {$wpdb->prefix}lm_licenses WHERE id = %d", $license_id
    ));

    if (!$license) {
        return new WP_Error('invalid_license', 'License not found.', ['status' => 404]);
    }

    $secret_key = get_option('lm_secret_key', '');
    $activation_code = hash_hmac('sha256', $system_code, $secret_key);

    // اگر قبلاً وجود دارد، همان را برگردان
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}lm_activation_codes WHERE license_id = %d AND system_code = %s",
        $license_id, $system_code
    ));

    if ($existing) {
        return [
            'activation_code' => $existing->activation_code,
            'expires_at'      => $existing->expires_at,
            'status'          => 'existing'
        ];
    }

    if (empty($expires_at)) {
        $expires_at = date('Y-m-d', strtotime('+6 months'));
    }

    $inserted = $wpdb->insert($wpdb->prefix . 'lm_activation_codes', [
        'license_id'      => $license_id,
        'user_id'         => $license->user_id,
        'product_id'      => $license->product_id,
        'system_code'     => $system_code,
        'activation_code' => $activation_code,
        'domain'          => $domain,
        'expires_at'      => $expires_at,
        'status'          => 'active',
        'created_at'      => $created_at,
        'updated_at'      => $updated_at,
    ]);

    if (!$inserted) {
        return new WP_Error('db_error', 'Failed to insert activation code.', ['status' => 500]);
    }

    return [
        'activation_code' => $activation_code,
        'expires_at'      => $expires_at,
        'status'          => 'created'
    ];
}


function lm_api_list_activation_codes($request) {
    global $wpdb;
    $params = $request->get_json_params();
    $user_email = sanitize_email($params['user_email'] ?? '');
    $user = get_user_by('email', $user_email);
    if (!$user) return [];

    $licenses = $wpdb->get_results($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}lm_licenses WHERE user_id = %d", $user->ID
    ));

    if (empty($licenses)) return [];

    $license_ids = wp_list_pluck($licenses, 'id');
    $placeholders = implode(',', array_fill(0, count($license_ids), '%d'));

    $query = "SELECT * FROM {$wpdb->prefix}lm_activation_codes WHERE license_id IN ($placeholders)";
    $prepared = $wpdb->prepare($query, ...$license_ids);

    return $wpdb->get_results($prepared);
}

function lm_api_deactivate_activation_code($request) {
    global $wpdb;
    $params = $request->get_json_params();

    $activation_hash = sanitize_text_field($params['activation_hash'] ?? '');
    if (!$activation_hash) {
        return new WP_Error('missing_hash', 'Activation hash is required.', ['status' => 400]);
    }

    $updated = $wpdb->update(
        $wpdb->prefix . 'lm_activation_codes',
        ['status' => 'inactive'],
        ['activation_hash' => $activation_hash]
    );

    return [
        'status' => $updated ? 'deactivated' : 'not_found',
    ];
}

// بررسی کلید مخفی
function lm_check_api_secret_key($request) {
    $headers = getallheaders();
    $provided = $headers['X-Api-Secret'] ?? ($headers['x-api-secret'] ?? '');
    $stored = get_option('lm_secret_key', '');
    return $provided && $provided === $stored;
}

add_action('wp_ajax_lm_get_user_info', function () {
    check_ajax_referer('lm_user_info_nonce');

    $user_id = intval($_POST['user_id'] ?? 0);
    $user = get_userdata($user_id);

    if (!$user) {
        wp_send_json_error();
    }

    $phone = get_user_meta($user_id, 'billing_phone', true);

    wp_send_json_success([
        'display_name' => $user->display_name,
        'email' => $user->user_email,
        'phone' => $phone ?: '—',
    ]);
});