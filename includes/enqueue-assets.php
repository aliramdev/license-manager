<?php
defined('ABSPATH') or die('No script kiddies please!');

add_action('admin_enqueue_scripts', function ($hook) {
    // فقط برای صفحات افزونه بارگذاری شود
    if (strpos($hook, 'lm_') === false) return;

    // Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0');

    // FontAwesome CSS
    wp_enqueue_style('fontawesome-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0');

    // استایل اختصاصی افزونه
    wp_enqueue_style('lm-admin-style', plugin_dir_url(__FILE__) . '../assets/css/admin-style.css', [], '1.0');

    // Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', ['jquery'], '5.3.0', true);

    // اسکریپت اختصاصی افزونه
    wp_enqueue_script('lm-custom-js', plugin_dir_url(__FILE__) . '../assets/js/custom.js', ['jquery'], '1.0', true);
});
