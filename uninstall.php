<?php
// امنیت فایل: جلوگیری از دسترسی مستقیم
defined('WP_UNINSTALL_PLUGIN') || exit;

// دسترسی به پایگاه داده وردپرس
global $wpdb;

// نام جدول‌ها
$licenses_table = $wpdb->prefix . 'lm_licenses';
$activation_table = $wpdb->prefix . 'lm_activation_codes';

// حذف جدول‌ها
$wpdb->query("DROP TABLE IF EXISTS $licenses_table");
$wpdb->query("DROP TABLE IF EXISTS $activation_table");

// حذف گزینه‌های تنظیمات افزونه
delete_option('lm_secret_key');
delete_option('lm_api_key');
delete_option('lm_default_duration');
delete_option('lm_allowed_domains');

// اگر تنظیمات را با get_option ذخیره کرده‌ایم و کلید خاص دیگری داشتیم، آن‌ها را هم پاک کنیم
delete_option('lm_custom_plugin_settings');
