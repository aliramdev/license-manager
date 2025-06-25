<?php
defined('ABSPATH') || exit;

// پاسخ به درخواست Ajax برای دریافت لیست لایسنس‌های یک کاربر خاص
add_action('wp_ajax_lm_get_user_licenses', function () {
    // بررسی امنیتی nonce
    check_ajax_referer('lm_get_user_licenses_nonce');

    // بررسی دسترسی
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز');
    }

    // گرفتن user_id از درخواست
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    if (!$user_id) {
        wp_send_json_error('شناسه کاربر معتبر نیست');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'lm_licenses';

    // گرفتن لایسنس‌های کاربر از دیتابیس
    $licenses = $wpdb->get_results($wpdb->prepare(
        "SELECT id, license_code, product_id FROM $table_name WHERE user_id = %d ORDER BY id DESC",
        $user_id
    ));

    if (!$licenses) {
        // اگر لایسنسی نیست، آرایه خالی بازگردان
        wp_send_json_success([]);
    }

    // آماده سازی داده‌ها برای ارسال به کلاینت
    $data = [];
    foreach ($licenses as $license) {
        $product_name = get_the_title($license->product_id);
        $data[] = [
            'id' => $license->id,
            'license_key' => $license->license_code,
            'product_name' => $product_name,
        ];
    }

    // ارسال پاسخ موفقیت‌آمیز با داده‌ها
    wp_send_json_success($data);
});