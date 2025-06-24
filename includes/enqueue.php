<?php
defined('ABSPATH') || exit;

function lm_admin_enqueue_scripts($hook) {
    if (strpos($hook, 'lm_') === false) {
        return;
    }

    $plugin_url = plugin_dir_url(__FILE__) . '../assets/';

    wp_enqueue_style('lm-bootstrap-css', $plugin_url . 'css/bootstrap.min.css');
    wp_enqueue_style('lm-fontawesome-css', $plugin_url . 'css/fontawesome.min.css');
    wp_enqueue_style('lm-admin-style', $plugin_url . 'css/admin-style.css');

    wp_enqueue_script('lm-bootstrap-js', $plugin_url . 'js/bootstrap.bundle.min.js', ['jquery'], null, true);
    wp_enqueue_script('lm-custom-js', $plugin_url . 'js/custom.js', ['jquery'], null, true);
}
add_action('admin_enqueue_scripts', 'lm_admin_enqueue_scripts');

function lm_frontend_enqueue_scripts() {
    $plugin_url = plugin_dir_url(__FILE__) . '../assets/';

    wp_enqueue_style('lm-bootstrap-css', $plugin_url . 'css/bootstrap.min.css');
    wp_enqueue_style('lm-fontawesome-css', $plugin_url . 'css/fontawesome.min.css');
    // اگر استایل مخصوص frontend دارید اضافه کنید
    // wp_enqueue_style('lm-frontend-style', $plugin_url . 'css/frontend-style.css');

    wp_enqueue_script('lm-bootstrap-js', $plugin_url . 'js/bootstrap.bundle.min.js', ['jquery'], null, true);
    // اگر اسکریپت مخصوص frontend دارید اضافه کنید
    // wp_enqueue_script('lm-frontend-js', $plugin_url . 'js/frontend.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'lm_frontend_enqueue_scripts');
