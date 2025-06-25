<?php
defined('ABSPATH') || exit;

// پاسخ به درخواست Ajax برای دریافت لیست لایسنس‌های یک کاربر خاص
add_action('wp_ajax_lm_get_user_licenses', function () {
    check_ajax_referer('lm_get_user_licenses_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز');
    }

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error('شناسه کاربر معتبر نیست');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'lm_licenses';

    $licenses = $wpdb->get_results($wpdb->prepare(
        "SELECT id, license_code, product_id FROM $table_name WHERE user_id = %d ORDER BY id DESC",
        $user_id
    ));

    if (!$licenses) {
        wp_send_json_success([]);
    }

    $data = [];
    foreach ($licenses as $license) {
        $product_name = get_the_title($license->product_id);
        $data[] = [
            'id' => $license->id,
            'license_key' => $license->license_code,
            'product_name' => $product_name,
        ];
    }

    wp_send_json_success($data);
});

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

