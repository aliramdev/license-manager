<?php
defined('ABSPATH') || exit;

// هوک برای ایجاد خودکار لایسنس پس از خرید ووکامرس

add_action('woocommerce_order_status_completed', 'lm_create_license_after_purchase');

function lm_create_license_after_purchase($order_id) {
    if (!$order_id) return;
    $order = wc_get_order($order_id);

    if (!$order) return;

    $user_id = $order->get_user_id();
    if (!$user_id) return;

    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();

        // بررسی و ساخت لایسنس برای محصول
        lm_get_or_create_license($user_id, $product_id, ''); // system_code خالی چون مشخص نیست

        // در صورت نیاز کد فعالسازی هم می توان تولید کرد یا در API این کار را انجام داد
    }
}
