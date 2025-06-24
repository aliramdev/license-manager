<?php
defined('ABSPATH') or die('No script kiddies please!');

add_action('rest_api_init', function () {
    register_rest_route('licensemanager/v1', '/register-user', [
        'methods' => 'POST',
        'callback' => 'lm_api_register_user',
        'permission_callback' => function() { return true; }, // ثبت کاربر برای همه آزاد است
    ]);
});

function lm_api_register_user(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));
    $password = sanitize_text_field($request->get_param('password'));
    $first_name = sanitize_text_field($request->get_param('first_name'));
    $last_name = sanitize_text_field($request->get_param('last_name'));

    if (!$email || !$password) {
        return new WP_REST_Response(['error' => 'Email and password required'], 400);
    }

    if (email_exists($email)) {
        return new WP_REST_Response(['error' => 'Email already registered'], 409);
    }

    $user_id = wp_create_user($email, $password, $email);

    if (is_wp_error($user_id)) {
        return new WP_REST_Response(['error' => $user_id->get_error_message()], 500);
    }

    wp_update_user([
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'role' => 'customer',
    ]);

    return ['message' => 'User registered successfully', 'user_id' => $user_id];
}
