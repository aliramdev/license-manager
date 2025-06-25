<?php
// includes/license-generator.php

defined('ABSPATH') || exit;

function lm_generate_license($user_id, $product_id, $start_date = null, $expiry_date = null) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'lm_licenses';

    // تولید کد یکتای لایسنس
    $license_code = sha1($user_id . '-' . $product_id . '-' . time() . wp_generate_password(5, false, false));

    $data = [
        'user_id'      => intval($user_id),
        'product_id'   => intval($product_id),
        'license_code' => sanitize_text_field($license_code),
        'status'       => 'active',
        'created_at'   => current_time('mysql')
    ];

    if (!empty($start_date)) {
        $data['start_date'] = sanitize_text_field($start_date);
    }

    if (!empty($expiry_date)) {
        $data['expiry_date'] = sanitize_text_field($expiry_date);
    }

    $inserted = $wpdb->insert($table_name, $data);

    if ($inserted) {
        return [
            'success' => true,
            'license_key' => $license_code,
            'message' => __('License generated successfully.', 'license-manager')
        ];
    } else {
        return [
            'success' => false,
            'message' => __('DB Error: ', 'license-manager') . $wpdb->last_error
        ];
    }
}

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

