<?php
defined('ABSPATH') || exit;

// توابع عمومی

// تولید کد هش شده بر اساس کد سیستم و کلید مخفی
function lm_generate_activation_hash($system_code, $secret_key) {
    return hash_hmac('sha256', $system_code, $secret_key);
}

// پاکسازی رشته‌ها
function lm_sanitize_text($text) {
    return sanitize_text_field(trim($text));
}

// بررسی دسترسی API بر اساس کلید مخفی ارسال شده
function lm_verify_secret_key($provided_key) {
    $secret = get_option('lm_secret_key', '');
    return hash_equals($secret, $provided_key);
}
