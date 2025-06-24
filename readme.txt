=== License Manager ===
Contributors: Ali Ramazani, aliram.ir, zarinafzar.com
Tags: license, api, activation, software license, WooCommerce
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A flexible license management plugin to generate, validate, and renew activation codes for WordPress/WooCommerce users. Includes full REST API support.

== Description ==

**License Manager** is a lightweight plugin for managing software licenses via WordPress and WooCommerce. It allows administrators to generate and manage license codes based on system IDs, associate them with WooCommerce products, and expose them via API.

- Generate license for a user and product.
- Bind license to a unique system code (e.g., hardware ID).
- Generate hash codes for license activation.
- Define license expiration duration per product.
- REST API to activate, validate, and renew licenses.
- Save domain history of license usage.
- Admin settings page with secret key, duration, and allowed origins.

== Features ==

- Admin panel for creating and listing licenses.
- Searchable license list (by system code or domain).
- API key and secret key generation.
- Auto and manual license generation support.
- Domain restriction support (like CORS).
- Fully extendable for custom needs.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/license-manager` directory.
2. Activate the plugin through the ‘Plugins’ screen in WordPress.
3. Go to **License Manager > Settings** to configure your API key, duration, and domain restrictions.
4. Use the API endpoint to integrate with external software.

== Persian (فارسی) توضیحات ==

**افزونه مدیریت لایسنس** توسط *علی رمضانی* از شرکت *زرین افزار* طراحی شده است و یک ابزار کامل برای ساخت، اعتبارسنجی و تمدید کدهای فعال‌سازی نرم‌افزار در وردپرس است.

ویژگی‌ها:
- تولید کد لایسنس برای کاربران وردپرس
- اتصال لایسنس به کد سیستم یا دامنه
- تعیین مدت اعتبار بر اساس محصول
- بررسی و تمدید از طریق API
- ثبت تاریخچه دامنه‌های استفاده‌کننده
- تنظیمات پیشرفته برای کلید هش، مدت زمان، دامنه‌های مجاز

== مسیرهای API ==

- `/wp-json/licensemanager/v1/activate`
- `/wp-json/licensemanager/v1/validate`
- `/wp-json/licensemanager/v1/renew`
- `/wp-json/licensemanager/v1/user-licenses`

همه‌ی APIها از متد `POST` استفاده می‌کنند و ورودی را به‌صورت JSON دریافت می‌کنند.

== Changelog ==

= 1.0 =
* اولین نسخه با پنل مدیریت و پشتیبانی کامل از REST API.

== License ==

This plugin is licensed under the GPLv2 or later.
