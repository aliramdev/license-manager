<?php
defined('ABSPATH') || exit;

// تغییر وضعیت لایسنس
add_action('wp_ajax_lm_toggle_license_status', function () {
    check_ajax_referer('lm_toggle_status_nonce');

    $id = intval($_POST['license_id']);
    $new_status = sanitize_text_field($_POST['new_status']);

    global $wpdb;
    $table = $wpdb->prefix . 'lm_licenses';
    $updated = $wpdb->update($table, ['status' => $new_status], ['id' => $id]);

    if ($updated !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
});

// حذف لایسنس
add_action('wp_ajax_lm_delete_license', function () {
    check_ajax_referer('lm_delete_license_nonce');

    $id = intval($_POST['license_id']);
    global $wpdb;
    $table = $wpdb->prefix . 'lm_licenses';
    $deleted = $wpdb->delete($table, ['id' => $id]);

    if ($deleted !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
});