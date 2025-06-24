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


