<?php
defined('ABSPATH') or die('No script kiddies please!');

function lm_get_users_list() {
    $users = get_users(['fields' => ['ID', 'user_email', 'display_name']]);
    $result = [];
    foreach ($users as $user) {
        $result[$user->ID] = $user->display_name ? $user->display_name . ' (' . $user->user_email . ')' : $user->user_email;
    }
    return $result;
}

function lm_get_products_list() {
    if (!class_exists('WooCommerce')) {
        return [];
    }
    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];
    $query = new WP_Query($args);
    $products = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $products[get_the_ID()] = get_the_title();
        }
        wp_reset_postdata();
    }
    return $products;
}

function lm_generate_activation_hash($system_code) {
    $secret_key = get_option('lm_secret_key', '');
    return hash('sha256', $secret_key . $system_code);
}
