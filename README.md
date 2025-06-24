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

- Generate license keys per user and product  
- Bind licenses to system codes for hardware-based activation  
- License expiration management per product  
- Full REST API support: activate, validate, renew, and list licenses  
- Domain restriction and history tracking  
- Admin UI for managing licenses and settings  

---

## Installation

1. Upload the `license-manager` folder to `/wp-content/plugins/`  
2. Activate the plugin via the WordPress Plugins page  
3. Go to **License Manager > Settings** to configure API keys, expiration durations, and allowed domains  
4. Use the REST API endpoints to integrate with your external software  

---

## API Endpoints

Base URL: `/wp-json/licensemanager/v1/`

### Activate License

- URL: `/activate`  
- Method: POST  
- Payload:
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

### Validate License

- URL: `/validate`  
- Method: POST  
- Payload:
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

### Renew License

- URL: `/renew`  
- Method: POST  
- Payload:
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

### List User Licenses

- URL: `/user-licenses`  
- Method: POST  
- Payload:
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

- Set secret key for license hash generation
- Define default license duration (in months)
- Specify allowed domains for API access (CORS-like restrictions)
- Generate and manage API keys
- Create, view, search, and delete licenses from admin panel

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

## Persian (فارسی)

### توضیحات
افزونه مدیریت لایسنس، ابزاری سبک و حرفه‌ای برای ساخت، مدیریت و اعتبارسنجی لایسنس نرم‌افزار از طریق وردپرس و ووکامرس است.
امکانات اصلی:

- تولید لایسنس برای کاربران و محصولات
- اتصال لایسنس به کد سیستم (شناسه سخت‌افزار)
- مدیریت تاریخ انقضای لایسنس
- API کامل برای فعالسازی، اعتبارسنجی، تمدید و دریافت لایسنس‌ها
- کنترل دامنه‌های مجاز و ثبت تاریخچه دامنه‌ها
- پنل مدیریتی با امکانات ایجاد، جستجو و حذف لایسنس‌ها

### راه‌اندازی
۱. پوشه license-manager را در مسیر /wp-content/plugins/ آپلود کنید.
۲. افزونه را در صفحه افزونه‌های وردپرس فعال کنید.
۳. به بخش «مدیریت لایسنس > تنظیمات» رفته و کلیدهای API، مدت اعتبار و دامنه‌های مجاز را تنظیم کنید.
۴. از طریق API افزونه به راحتی در نرم‌افزار خارجی خود استفاده کنید.

### API ها
مسیر پایه: /wp-json/licensemanager/v1/
و توضیحات دقیق مشابه بخش انگلیسی بالا.

### نمونه درخواست سمت کلاینت (C#)
کد نمونه مشابه بخش انگلیسی.

