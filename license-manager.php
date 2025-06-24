<?php
/**
 * Plugin Name: License Manager
 * Plugin URI: https://zarinafzar.com
 * Description: A flexible license management plugin to generate, validate, and manage activation codes for WooCommerce products with full REST API support.
 * Version: 1.0
 * Author: Ali Ramezani (Zarinafzar)
 * Author URI: https://aliram.ir
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: license-manager
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// تعریف ثابت‌ها برای مسیرها
define('LM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LM_PLUGIN_URL', plugin_dir_url(__FILE__));

// بررسی نصب بودن ووکامرس
add_action('plugins_loaded', function () {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>License Manager:</strong> افزونه ووکامرس باید نصب و فعال باشد.</p></div>';
        });
        return;
    }

    // بارگذاری فایل‌های اصلی
    require_once LM_PLUGIN_PATH . 'includes/enqueue.php';
    require_once LM_PLUGIN_PATH . 'includes/functions.php';
    require_once LM_PLUGIN_PATH . 'includes/license-generator.php';
    require_once LM_PLUGIN_PATH . 'includes/license-api.php';
    require_once LM_PLUGIN_PATH . 'includes/license-hooks.php';
    require_once LM_PLUGIN_PATH . 'includes/user-registration.php';
    require_once LM_PLUGIN_PATH . 'includes/woocommerce-hooks.php';

    // بارگذاری صفحات مدیریت
    require_once LM_PLUGIN_PATH . 'templates/admin-settings.php';
    require_once LM_PLUGIN_PATH . 'templates/admin-user-registration.php';
    require_once LM_PLUGIN_PATH . 'templates/admin-license-list.php';
    require_once LM_PLUGIN_PATH . 'templates/admin-activation-codes.php';

    // ایجاد جدول‌های لازم در نصب افزونه
    register_activation_hook(__FILE__, 'lm_create_plugin_tables');

    // بارگذاری ترجمه‌ها در صورت نیاز
    load_plugin_textdomain('license-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

// ساخت جدول‌های لازم در نصب اولیه افزونه
function lm_create_plugin_tables() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $licenses_table = $wpdb->prefix . 'lm_licenses';
    $activation_table = $wpdb->prefix . 'lm_activation_codes';

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql1 = "CREATE TABLE IF NOT EXISTS $licenses_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    $sql2 = "CREATE TABLE IF NOT EXISTS $activation_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        license_id BIGINT UNSIGNED NOT NULL,
        system_code VARCHAR(255) NOT NULL,
        activation_hash VARCHAR(255) NOT NULL,
        domain VARCHAR(255),
        expires_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    dbDelta($sql1);
    dbDelta($sql2);
}
