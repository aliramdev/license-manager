<?php
// includes/jdf.php
// کلاس ساده برای تبدیل تاریخ میلادی به شمسی

class jdf {
    public static function jalaliDate($format, $timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }

        $gregorian = getdate($timestamp);
        $gYear = $gregorian['year'];
        $gMonth = $gregorian['mon'];
        $gDay = $gregorian['mday'];

        list($jYear, $jMonth, $jDay) = self::gregorian_to_jalali($gYear, $gMonth, $gDay);

        $format = str_replace('Y', $jYear, $format);
        $format = str_replace('m', str_pad($jMonth, 2, '0', STR_PAD_LEFT), $format);
        $format = str_replace('d', str_pad($jDay, 2, '0', STR_PAD_LEFT), $format);

        $format = str_replace('H', date('H', $timestamp), $format);
        $format = str_replace('i', date('i', $timestamp), $format);
        $format = str_replace('s', date('s', $timestamp), $format);

        return $format;
    }

    public static function gregorian_to_jalali($g_y, $g_m, $g_d) {
        $g_days_in_month = [31,28,31,30,31,30,31,31,30,31,30,31];
        $j_days_in_month = [31,31,31,31,31,31,30,30,30,30,30,29];

        $gy = $g_y-1600;
        $gm = $g_m-1;
        $gd = $g_d-1;

        $g_day_no = 365*$gy + intval(($gy+3)/4) - intval(($gy+99)/100) + intval(($gy+399)/400);
        for ($i=0; $i<$gm; ++$i)
            $g_day_no += $g_days_in_month[$i];
        if ($gm>1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0))) // leap year
            $g_day_no++;
        $g_day_no += $gd;

        $j_day_no = $g_day_no - 79;

        $j_np = intval($j_day_no / 12053);
        $j_day_no %= 12053;

        $jy = 979 + 33*$j_np + 4*intval($j_day_no/1461);

        $j_day_no %= 1461;

        if ($j_day_no >= 366) {
            $jy += intval(($j_day_no-1)/365);
            $j_day_no = ($j_day_no-1)%365;
        }

        for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
            $j_day_no -= $j_days_in_month[$i];
        $jm = $i + 1;
        $jd = $j_day_no + 1;

        return [$jy, $jm, $jd];
    }
}

// تابع کمکی jdate برای راحتی استفاده
if (!function_exists('jdate')) {
    function jdate($format, $timestamp = null) {
        return jdf::jalaliDate($format, $timestamp);
    }
}