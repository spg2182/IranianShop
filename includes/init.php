<?php
// includes/init.php

// بررسی تعریف نشدن ثابت‌ها برای جلوگیری از خطا
if (!defined('BASE_PATH')) {
    // شروع سشن
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // تولید CSRF Token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // بررسی کوکی "به خاطر سپردن کاربر"
    if (!isset($_SESSION["state_login"]) && isset($_COOKIE['remember_token'])) {
        include('./db_link.php');
        
        $token = $_COOKIE['remember_token'];
        $query = "SELECT u.id, u.username, u.password, u.realname, u.email, u.user_type, u.is_active 
                  FROM users u 
                  JOIN user_tokens t ON u.id = t.user_id 
                  WHERE t.token = ? AND t.expiry > NOW()";
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            if ($user['is_active'] == 1) {
                session_regenerate_id(true);
                $_SESSION["state_login"] = true;
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["realname"] = $user["realname"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["user_type"] = $user["user_type"] == 1 ? "admin" : "nonadmin";
                
                // به‌روزرسانی آخرین ورود
                $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
                $update_stmt = mysqli_prepare($link, $update_query);
                mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
            }
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($link);
    }

    // تعریف مسیر پایه برای استفاده در سراسر سایت
    $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    define('BASE_PATH', $basePath);

    // تعریف ثابت برای بررسی بارگذاری فایل
    define('HEADER_LOADED', true);
}
?>