# License Manager

**Author:** Ali Ramezani (Zarinafzar Company)
**Tags:** license, api, activation, software license, WooCommerce
**Requires:** WordPress 5.6+, PHP 7.4+

---

## Description

License Manager is a lightweight WordPress plugin to generate, validate, and renew software license keys for WooCommerce products.
It supports associating licenses with unique system codes (e.g. hardware IDs), storing domain history, and full REST API integration for external apps.

---

## Features

* Generate license keys per user and product
* Bind licenses to system codes for hardware-based activation
* License expiration management per product (defined in product meta or manually)
* Full REST API support: activate, validate, renew, and list licenses
* Domain restriction and history tracking per activation code
* Admin UI for managing licenses, activation codes, API keys, and settings
* Search licenses by user, product, system code, domain
* Generate and manage API keys with secret keys for secure API access

---

## Installation

1. Upload the `license-manager` folder to `/wp-content/plugins/`
2. Activate the plugin via the WordPress Plugins page
3. Go to **License Manager > Settings** to configure API keys, secret key, expiration durations, and allowed domains
4. Use the REST API endpoints to integrate with your external software

---

## API Endpoints

Base URL: `/wp-json/licensemanager/v1/`

### Activate License

* URL: `/activate`
* Method: POST
* Payload:

```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "UNIQUE_SYSTEM_ID",
  "domain": "example.com" // optional
}
```

Response:

```json
{
  "activation_hash": "abc123def456...",
  "expires_at": "2025-12-31 23:59:59",
  "status": "valid"
}
```

---

### Validate License

* URL: `/validate`
* Method: POST
* Payload:

```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "UNIQUE_SYSTEM_ID"
}
```

Response:

```json
{
  "status": "valid",
  "activation_hash": "abc123def456...",
  "expires_at": "2025-12-31 23:59:59",
  "days_remaining": 120
}
```

---

### Renew License

* URL: `/renew`
* Method: POST
* Payload:

```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "UNIQUE_SYSTEM_ID"
}
```

Response:

```json
{
  "expires_at": "2026-12-31 23:59:59",
  "status": "valid"
}
```

---

### List User Licenses

* URL: `/user-licenses`
* Method: POST
* Payload:

```json
{
  "user_email": "user@example.com"
}
```

Response:

```json
[
  {
    "product_id": 123,
    "system_code": "UNIQUE_SYSTEM_ID",
    "activation_hash": "abc123def456...",
    "status": "valid",
    "expires_at": "2025-12-31 23:59:59",
    "domain_history": ["example.com", "another.com"]
  }
]
```

---

## Admin Settings

* Set secret key for license hash generation
* Define default license duration (in months) for new activations
* Specify allowed domains for API access (CORS-like restrictions)
* Generate, view, and revoke API keys for secure access
* Manage licenses and activation codes with search, pagination, and bulk delete
* Manual generation of activation codes linked to system codes with expiration and domain

---

## Sample Client Request (C#)

```csharp
var client = new HttpClient();
var data = new {
    user_email = "user@example.com",
    product_id = 123,
    system_code = "UNIQUE_CODE_001",
    domain = "example.com"
};
var json = JsonConvert.SerializeObject(data);
var content = new StringContent(json, Encoding.UTF8, "application/json");
var response = await client.PostAsync("https://yourdomain.com/wp-json/licensemanager/v1/activate", content);
var responseString = await response.Content.ReadAsStringAsync();
```

---

## Persian (فارسی)

### توضیحات

افزونه مدیریت لایسنس، ابزاری سبک و حرفه‌ای برای ساخت، مدیریت و اعتبارسنجی لایسنس نرم‌افزار از طریق وردپرس و ووکامرس است.
این افزونه قابلیت تولید لایسنس به ازای هر کاربر و محصول، اتصال لایسنس‌ها به کدهای سیستم یکتا (شناسه سخت‌افزار)، مدیریت تاریخ انقضا (که از متای محصول خوانده می‌شود یا دستی تنظیم می‌شود) و ثبت تاریخچه دامنه‌ها را داراست.
همچنین یک API کامل برای فعالسازی، اعتبارسنجی، تمدید و مشاهده لایسنس‌ها ارائه می‌دهد.

---

### امکانات

* تولید لایسنس برای کاربران و محصولات
* اتصال لایسنس به کد سیستم (شناسه سخت‌افزار)
* مدیریت تاریخ انقضای لایسنس (بر اساس متای محصول یا دستی)
* API کامل برای فعالسازی، اعتبارسنجی، تمدید و دریافت لایسنس‌ها
* ثبت و کنترل دامنه‌های مجاز و تاریخچه دامنه‌ها
* پنل مدیریتی برای ایجاد، جستجو، حذف لایسنس‌ها و مدیریت کدهای فعالسازی
* تعریف و مدیریت کلیدهای API و کلید مخفی برای امنیت
* قابلیت جستجوی لایسنس‌ها بر اساس کاربر، محصول، کد سیستم و دامنه

---

### راه‌اندازی

۱. پوشه license-manager را در مسیر `/wp-content/plugins/` آپلود کنید.
۲. افزونه را از صفحه افزونه‌های وردپرس فعال کنید.
۳. به بخش «مدیریت لایسنس > تنظیمات» بروید و کلیدهای API، کلید مخفی، مدت اعتبار پیش‌فرض و دامنه‌های مجاز را تنظیم کنید.
۴. از API افزونه برای اتصال نرم‌افزارهای خارجی خود استفاده کنید.

---

### API ها

مسیر پایه: `/wp-json/licensemanager/v1/`

### فعالسازی لایسنس

* مسیر: `/activate`
* متد: POST
* ورودی:

```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "شناسه_سیستم_منحصر_به_فرد",
  "domain": "example.com" // اختیاری
}
```

خروجی:

```json
{
  "activation_hash": "کد_فعالسازی_هش_شده",
  "expires_at": "تاریخ_انقضا",
  "status": "valid"
}
```

---

### اعتبارسنجی لایسنس

* مسیر: `/validate`
* متد: POST
* ورودی:

```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "شناسه_سیستم_منحصر_به_فرد"
}
```

خروجی:

```json
{
  "status": "valid",
  "activation_hash": "کد_فعالسازی_هش_شده",
  "expires_at": "تاریخ_انقضا",
  "days_remaining": 120
}
```

---

### تمدید لایسنس

* مسیر: `/renew`
* متد: POST
* ورودی:

```json
{
  "user_email": "user@example.com",
  "product_id": 123,
  "system_code": "شناسه_سیستم_منحصر_به_فرد"
}
```

خروجی:

```json
{
  "expires_at": "تاریخ_جدید_انقضا",
  "status": "valid"
}
```

---

### دریافت لیست لایسنس‌های کاربر

* مسیر: `/user-licenses`
* متد: POST
* ورودی:

```json
{
  "user_email": "user@example.com"
}
```

خروجی:

```json
[
  {
    "product_id": 123,
    "system_code": "شناسه_سیستم_منحصر_به_فرد",
    "activation_hash": "کد_فعالسازی_هش_شده",
    "status": "valid",
    "expires_at": "تاریخ_انقضا",
    "domain_history": ["example.com", "another.com"]
  }
]
```

---

### نمونه کد کلاینت (C#)

```csharp
var client = new HttpClient();
var data = new {
    user_email = "user@example.com",
    product_id = 123,
    system_code = "UNIQUE_CODE_001",
    domain = "example.com"
};
var json = JsonConvert.SerializeObject(data);
var content = new StringContent(json, Encoding.UTF8, "application/json");
var response = await client.PostAsync("https://yourdomain.com/wp-json/licensemanager/v1/activate", content);
var responseString = await response.Content.ReadAsStringAsync();
```

---

