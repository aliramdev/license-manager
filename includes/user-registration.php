<?php
defined('ABSPATH') || exit;

/**
 * ثبت‌نام کاربر جدید با پشتیبانی از فیلدهای ووکامرس (billing, shipping)
 * ورودی: آرایه $user_data شامل:
 *  - user_login
 *  - user_email
 *  - user_pass
 *  - billing_phone
 *  - billing_address_1, billing_address_2, billing_city, billing_postcode, billing_country, billing_state
 *  - shipping_address_1, shipping_address_2, shipping_city, shipping_postcode, shipping_country, shipping_state
 *  - و دیگر فیلدهای ووکامرس (اختیاری)
 * خروجی: شناسه کاربر جدید یا WP_Error
 */
function lm_register_new_user($user_data) {
    if (empty($user_data['user_login']) || empty($user_data['user_email']) || empty($user_data['user_pass'])) {
        return new WP_Error('missing_fields', 'Username, email, and password are required.');
    }

    if (username_exists($user_data['user_login']) || email_exists($user_data['user_email'])) {
        return new WP_Error('user_exists', 'User with this username or email already exists.');
    }

    // ساخت کاربر جدید
    $user_id = wp_create_user(
        sanitize_user($user_data['user_login']),
        $user_data['user_pass'],
        sanitize_email($user_data['user_email'])
    );

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    // به‌روزرسانی نقش به مشتری ووکامرس
    $user = new WP_User($user_id);
    $user->set_role('customer');

    // ذخیره متاهای ووکامرس (billing و shipping)
    $wc_billing_fields = [
        'billing_phone',
        'billing_first_name',
        'billing_last_name',
        'billing_company',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_postcode',
        'billing_country',
        'billing_state',
        'billing_email',
    ];

    $wc_shipping_fields = [
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_postcode',
        'shipping_country',
        'shipping_state',
    ];

    foreach ($wc_billing_fields as $field) {
        if (!empty($user_data[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($user_data[$field]));
        }
    }

    foreach ($wc_shipping_fields as $field) {
        if (!empty($user_data[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($user_data[$field]));
        }
    }

    return $user_id;
}
