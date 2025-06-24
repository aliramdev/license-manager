در اینجا نسخه‌ی نهایی و به‌روز شده فایل `readme.txt` برای افزونه‌ی مدیریت لایسنس وردپرس/ووکامرس بر اساس تمام تغییرات و امکانات نهایی آمده است:

---

````txt
=== License Manager ===
Contributors: Ali Ramazani, aliram.ir, zarinafzar.com  
Tags: license, api, activation, software license, WooCommerce, REST API, system code, domain history  
Requires at least: 5.6  
Tested up to: 6.5  
Requires PHP: 7.4  
Stable tag: 1.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

A powerful and flexible license management plugin to generate, validate, and renew activation codes for WordPress/WooCommerce users. Supports REST API, domain restrictions, subscriptions, and activation logs.

== Description ==

**License Manager** is a complete licensing solution for WooCommerce products. It allows site administrators to generate and manage licenses and activation codes for users and their purchased products, bind them to specific system codes or domains, and interact via secure REST APIs.

Built by *Ali Ramazani* from *Zarinafzar Co.*, this plugin is perfect for developers selling software and desktop apps needing activation keys.

=== Key Features ===

- Generate and manage licenses per user and WooCommerce product
- Bind license to system code (e.g. hardware ID)
- Define expiration per product or set custom expiry
- Track domain usage history
- Auto-generate activation hashes
- REST API for license/activation management
- Secure API with secret key validation
- Responsive and modern admin UI (Bootstrap + FontAwesome)
- User dashboard to list user-specific licenses & activation codes
- Manual and automatic activation code generation
- Subscription-based license support
- License renewal and expiration validation
- API access control via allowed IPs, domains, or software source

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/license-manager`
2. Activate through the Plugins menu in WordPress
3. Go to **License Manager > Settings** to configure:
   - Secret Key for hashing
   - API Key for validation
   - Allowed origins
   - Expiry durations
4. Begin generating and validating licenses via the UI or REST API.

== REST API Documentation ==

**Base URL:** `/wp-json/licensemanager/v1/`  
**Authentication:** Requires a valid API key (`secret_key`) as a header or parameter.

### POST /activate  
Creates or fetches an activation hash for a user/product/system combo.  
```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "ABC123DEF456",
  "domain": "example.com",
  "secret_key": "YOUR_SECRET_KEY"
}
````

### POST /validate

Checks if a given activation is valid.

```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "ABC123DEF456",
  "secret_key": "YOUR_SECRET_KEY"
}
```

### POST /renew

Renews a valid license and updates expiry date.

```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "ABC123DEF456",
  "secret_key": "YOUR_SECRET_KEY"
}
```

### POST /user-licenses

Returns all licenses for a given user.

```json
{
  "user_email": "user@example.com",
  "secret_key": "YOUR_SECRET_KEY"
}
```

\== Persian (فارسی) ==

**افزونه مدیریت لایسنس** یک راهکار کامل برای ساخت، اعتبارسنجی، تمدید و مدیریت کدهای فعال‌سازی نرم‌افزار در وردپرس و ووکامرس است. ساخته‌شده توسط *علی رمضانی* از شرکت *زرین‌افزار*.

ویژگی‌ها:

* تولید لایسنس برای کاربران ووکامرس
* اتصال لایسنس به شناسه سخت‌افزاری (کد سیستم)
* تولید خودکار کد فعال‌سازی (هش‌شده با کلید مخفی)
* تعیین تاریخ انقضا بر اساس محصول یا دستی
* مشاهده تاریخچه دامنه‌های استفاده‌شده
* فعال‌سازی از طریق دامنه، نرم‌افزار یا موبایل با محدودیت دسترسی
* پنل مدیریت حرفه‌ای و ریسپانسیو
* نمایش لیست لایسنس‌ها و کدهای فعال‌سازی در پنل کاربری کاربر
* قابلیت ابطال یا حذف لایسنس‌ها
* پشتیبانی از API برای ثبت‌نام کاربران با اطلاعات ووکامرس

\== تغییرات (Changelog) ==

\= 1.0 =

* نسخه اولیه شامل مدیریت کامل لایسنس و فعال‌سازی
* پنل مدیریت ریسپانسیو با Bootstrap
* REST API برای فعال‌سازی، اعتبارسنجی، تمدید و لیست کاربران
* قابلیت اتصال به محصولات ووکامرس
* ذخیره دامنه‌ها، تاریخ انقضا و کدهای هش
* امنیت بالا با secret\_key و محدودسازی API

\== License ==
This plugin is licensed under the GPLv2 or later.
[https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

