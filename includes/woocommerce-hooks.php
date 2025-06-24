<?php
defined('ABSPATH') or die('No script kiddies please!');

// بررسی فعال بودن ووکامرس
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

// ثبت فیلد متای اختصاصی "فعال بودن لایسنس" در پنل محصول
add_action('woocommerce_product_options_general_product_data', 'lm_add_license_product_field');
function lm_add_license_product_field() {
    woocommerce_wp_checkbox([
        'id' => '_lm_license_enabled',
        'label' => __('Enable License', 'license-manager'),
        'description' => __('Enable license generation for this product.', 'license-manager'),
        'default' => 'no',
    ]);
}

add_action('woocommerce_process_product_meta', 'lm_save_license_product_field');
function lm_save_license_product_field($post_id) {
    $enabled = isset($_POST['_lm_license_enabled']) ? 'yes' : 'no';
    update_post_meta($post_id, '_lm_license_enabled', $enabled);
}

// هوک تولید لایسنس پس از تکمیل سفارش
add_action('woocommerce_order_status_completed', 'lm_generate_licenses_for_order');
function lm_generate_licenses_for_order($order_id) {
    if (!$order_id) return;

    $order = wc_get_order($order_id);
    if (!$order) return;

    $user_id = $order->get_user_id();
    if (!$user_id) return;

    global $wpdb;
    $licenses_table = $wpdb->prefix . 'lm_licenses';

    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();

        // فقط محصولات فعال برای لایسنس
        $license_enabled = get_post_meta($product_id, '_lm_license_enabled', true);
        if ($license_enabled !== 'yes') continue;

        // چک کردن وجود لایسنس قبلی برای کاربر و محصول
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $licenses_table WHERE user_id=%d AND product_id=%d AND status='active'",
            $user_id, $product_id
        ));
        if ($exists) continue; // اگر قبلا لایسنس فعال داشت، رد شود

        // تولید لایسنس جدید (استفاده از تابع اختصاصی افزونه)
        if (function_exists('lm_create_license')) {
            lm_create_license($user_id, $product_id);
        }
    }
}
