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

    if (!$license_id || !$system_code) {
        return new WP_Error('missing_fields', 'License ID and system code are required.', ['status' => 400]);
    }

    $secret_key = get_option('lm_secret_key', '');
    $hash = hash('sha256', $secret_key . $system_code);

    // اگر قبلاً ثبت شده بود، بازگردانی
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}lm_activation_codes WHERE license_id = %d AND system_code = %s",
        $license_id, $system_code
    ));

    if ($existing) {
        return [
            'activation_hash' => $existing->activation_hash,
            'expires_at' => $existing->expires_at,
            'status' => 'existing'
        ];
    }

    if (empty($expires_at)) {
        $expires_at = date('Y-m-d H:i:s', strtotime('+6 months'));
    }

    $wpdb->insert($wpdb->prefix . 'lm_activation_codes', [
        'license_id' => $license_id,
        'system_code' => $system_code,
        'activation_hash' => $hash,
        'domain' => $domain,
        'expires_at' => $expires_at,
        'created_at' => $created_at,
        'status' => 'active'
    ]);

    return [
        'activation_hash' => $hash,
        'expires_at' => $expires_at,
        'status' => 'created'
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