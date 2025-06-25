<?php
defined('ABSPATH') || exit;

// توابع مربوط به لیست و مدیریت لایسنس‌ها و کدهای فعالسازی

function lm_get_licenses_by_user($user_id) {
    global $wpdb;
    $license_table = $wpdb->prefix . 'lm_licenses';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $license_table WHERE user_id = %d", $user_id));
    return $results;
}

function lm_get_activation_codes_by_license($license_id) {
    global $wpdb;
    $activation_table = $wpdb->prefix . 'lm_activation_codes';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $activation_table WHERE license_id = %d", $license_id));
    return $results;
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