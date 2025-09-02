<?php
// شروع سشن اگر قبلاً شروع نشده باشد
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// پاک کردن کوکی "به خاطر سپردن کاربر" اگر وجود داشته باشد
if (isset($_COOKIE['remember_token'])) {
    // حذف توکن از پایگاه داده
    include('./includes/db_link.php');
    $token = $_COOKIE['remember_token'];
    $query = "DELETE FROM user_tokens WHERE token = ?";
    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($link);
    
    // حذف کوکی
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// نابودی کامل سشن
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// جلوگیری از کش شدن صفحات
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// هدیت به صفحه ورود با پیام موفقیت
header("Location: login.php?status=logged_out");
exit;
?>