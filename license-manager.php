<?php
/**
 * Plugin Name: License Manager
 * Description: Advanced license key manager for WooCommerce products with REST API and activation hash logic.
 * Version: 1.1
 * Author: Ali Ramezani
 * Author URI: https://zarinafzar.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: license-manager
 */

if (!defined('ABSPATH')) exit;

// Define paths if not defined
if (!defined('LM_PLUGIN_PATH')) define('LM_PLUGIN_PATH', plugin_dir_path(__FILE__));
if (!defined('LM_PLUGIN_URL')) define('LM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load translations
add_action('plugins_loaded', function () {
    load_plugin_textdomain('license-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// Includes
require_once LM_PLUGIN_PATH . 'includes/enqueue.php';
require_once LM_PLUGIN_PATH . 'includes/user-registration.php';
require_once LM_PLUGIN_PATH . 'includes/license-generator.php';
require_once LM_PLUGIN_PATH . 'includes/license-activation.php';
require_once LM_PLUGIN_PATH . 'includes/license-api.php';
require_once LM_PLUGIN_PATH . 'includes/license-hooks.php';

require_once LM_PLUGIN_PATH . 'includes/jdf.php';
require_once LM_PLUGIN_PATH . 'includes/date-format.php';

// Admin Menu
add_action('admin_menu', function () {
    add_menu_page('License Manager', 'لیست لایسنس‌ها', 'manage_options', 'lm_license_list', function () {
        include LM_PLUGIN_PATH . 'templates/license-list-template.php';
    }, 'dashicons-admin-network');

    add_submenu_page('lm_license_list', 'تولید لایسنس', 'تولید لایسنس', 'manage_options', 'lm_license_generator', function () {
        include LM_PLUGIN_PATH . 'templates/license-generator-template.php';
    });

    add_submenu_page('lm_license_list', 'تولید کد فعالسازی', 'کد فعالسازی', 'manage_options', 'lm_activation_generator', function () {
        include LM_PLUGIN_PATH . 'templates/license-activation-generator-template.php';
    });

    add_submenu_page('lm_license_list', 'لیست کدهای فعالسازی', 'لیست کدهای فعالسازی', 'manage_options', 'lm_activation_list', function () {
        include LM_PLUGIN_PATH . 'templates/license-activation-list-template.php';
    });

    add_submenu_page('lm_license_list', 'تنظیمات', 'تنظیمات', 'manage_options', 'lm_settings', function () {
        include LM_PLUGIN_PATH . 'templates/settings-template.php';
    });
});

// Create license table on activation
if (!function_exists('lm_create_license_table')) {
    function lm_create_license_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'lm_licenses';
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            product_id BIGINT(20) UNSIGNED NOT NULL,
            license_code VARCHAR(255) NOT NULL,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            start_date DATETIME DEFAULT NULL,
            expiry_date DATETIME DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'lm_create_license_table');