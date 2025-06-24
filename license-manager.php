<?php
/*
Plugin Name: License Manager
Plugin URI: https://zarinafzar.com
Description: افزونه مدیریت لایسنس محصولات ووکامرس با پشتیبانی کامل از API و امنیت بالا.
Version: 1.0
Author: علی رمضانی
Author URI: https://aliram.ir
License: GPLv2 or later
Text Domain: license-manager
*/

defined('ABSPATH') or die('No script kiddies please!');

define('LM_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once LM_PLUGIN_DIR . 'includes/functions.php';
require_once LM_PLUGIN_DIR . 'includes/license-api.php';
require_once LM_PLUGIN_DIR . 'includes/license-generator.php';
require_once LM_PLUGIN_DIR . 'includes/license-list.php';
require_once LM_PLUGIN_DIR . 'includes/license-activation-list.php';
require_once LM_PLUGIN_DIR . 'includes/user-registration-api.php';
require_once LM_PLUGIN_DIR . 'includes/settings-page.php';

// افزودن منوهای مدیریت
add_action('admin_menu', function () {
    add_menu_page('مدیریت لایسنس', 'مدیریت لایسنس', 'manage_options', 'lm_license_list', 'lm_render_license_list_page', 'dashicons-admin-network', 25);
    add_submenu_page('lm_license_list', 'کدهای فعالسازی', 'کدهای فعالسازی', 'manage_options', 'lm_activation_list', 'lm_render_activation_list_page');
    add_submenu_page('lm_license_list', 'تنظیمات', 'تنظیمات', 'manage_options', 'lm_settings', 'lm_render_settings_page');
});

// فعال‌سازی و ساخت جداول دیتابیس
register_activation_hook(__FILE__, function () {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $licenses_table = $wpdb->prefix . 'lm_licenses';
    $activations_table = $wpdb->prefix . 'lm_activation_codes';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $sql1 = "CREATE TABLE IF NOT EXISTS $licenses_table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        license_code VARCHAR(255) NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'active',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql2 = "CREATE TABLE IF NOT EXISTS $activations_table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        license_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        system_code VARCHAR(255) NOT NULL,
        activation_code VARCHAR(255) NOT NULL,
        domain VARCHAR(255) DEFAULT '',
        status VARCHAR(50) NOT NULL DEFAULT 'active',
        expires_at DATE DEFAULT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql1);
    dbDelta($sql2);
});
