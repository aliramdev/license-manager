// فایل license-manager.php
<?php
/*
Plugin Name: مدیریت لایسنس
Description: افزونه اختصاصی برای مدیریت لایسنس محصولات ووکامرس
Version: 1.0
Author: شما
*/

// بارگذاری فایل‌ها
define('LM_PATH', plugin_dir_path(__FILE__));

include_once LM_PATH . 'includes/functions.php';
include_once LM_PATH . 'includes/settings-page.php';
include_once LM_PATH . 'includes/license-api.php';
include_once LM_PATH . 'includes/license-generator.php';
include_once LM_PATH . 'includes/license-list.php';

// منوهای پنل مدیریت
add_action('admin_menu', function() {
  add_menu_page('مدیریت لایسنس', '🎫 لایسنس‌ها', 'manage_options', 'license_manager_settings', 'lm_settings_page');
  add_submenu_page('license_manager_settings', 'تولید لایسنس', '➕ تولید لایسنس', 'manage_options', 'license_manager_generate', 'lm_license_generator_page');
  add_submenu_page('license_manager_settings', 'لیست لایسنس‌ها', '📄 لیست لایسنس‌ها', 'manage_options', 'license_manager_list', 'lm_license_list_page');
});