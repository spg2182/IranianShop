<?php
// includes/functions.php
date_default_timezone_set("Asia/Tehran");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
/**
 * تابع برای فرمت‌سازی قیمت به تومان
 *
 * @param int|float $number
 * @param bool $toman
 * @return string
 */
if (!function_exists('toman')) {
    function toman($number, $toman = false) {
        return number_format((float)$number, 0, "", ",") . ($toman ? " تومان" : "");
    }
}

// تابع برای نمایش کد پیگیری
if (!function_exists('formatTrackCode')) {
    function formatTrackCode($trackCode) {
        // اگر مقدار null یا خالی بود، رشته خالی برگردان
        if (empty($trackCode)) {
            return '';
        }
        
        // اگر کد پیگیری با TRK شروع می‌شود
        if (strpos($trackCode, 'TRK') === 0) {
            return substr($trackCode, 0, 3) . '-' . substr($trackCode, 3, 4) . '-' . substr($trackCode, 7, 4) . '-' . substr($trackCode, 11);
        }
        
        // برای فرمت‌های دیگر
        return $trackCode;
    }
}


/**
 * تابع برای محدود کردن متن و اضافه کردن ...
 *
 * @param string $string
 * @param int|null $length
 * @return string
 */
if (!function_exists('details')) {
    function details($string, $length = null) {
        $length = is_null($length) ? 100 : (int)$length;
        $str = mb_substr((string)$string, 0, $length);
        return $str . (mb_strlen($string) > $length ? "..." : "");
    }
}
/**
 * بررسی ورود کاربر
 */
if (!function_exists('require_login')) {
    function require_login() {
        if (empty($_SESSION["state_login"]) || $_SESSION["state_login"] !== true) {
            header("Location: not_logged_in.php");
            exit;
        }
    }
}
/**
 * بررسی دسترسی مدیر
 */
if (!function_exists('require_admin')) {
    function require_admin() {
        require_login();
        if (empty($_SESSION["user_type"]) || $_SESSION["user_type"] !== "admin") {
            header("Location: not_logged_in.php");
            exit;
        }
    }
}
/**
 * دریافت مسیر تصویر محصول
 *
 * @param string|null $image_name
 * @return string
 */
if (!function_exists('getProductImage')) {
    function getProductImage($image_name = null) {
        $default = "/assets/images/no-image.png";
        if (empty($image_name) || strtoupper($image_name) === 'NULL') {
            return $default;
        }
        // جلوگیری از مسیرهای ناامن (path traversal)
        $image_name = basename($image_name);
        $image_path = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/' . $image_name;
        if (is_file($image_path) && is_readable($image_path)) {
            return "/assets/images/" . $image_name;
        }
        return $default;
    }
}