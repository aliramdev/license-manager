<?php
// includes/date-format.php
// تابع کمکی برای فرمت تاریخ بر اساس زبان

function lm_format_date($datetime) {
    if (empty($datetime)) {
        return '';
    }

    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return '';
    }

    if (get_locale() === 'fa_IR') {
        require_once LM_PLUGIN_PATH . 'includes/jdf.php';
        return jdf::jalaliDate('Y/m/d H:i', $timestamp);
    } else {
        return date('Y-m-d H:i', $timestamp);
    }
}