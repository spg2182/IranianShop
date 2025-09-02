<?php
// شروع سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// بررسی وضعیت ورود کاربر
if (!isset($_SESSION["state_login"]) || $_SESSION["state_login"] !== true) {
    header("Location: not_logged_in.php");
    exit;
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=iranianshop", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // غیرفعال کردن کاربر به جای حذف کامل
    $stmt = $conn->prepare("UPDATE users SET 
                          is_active = 0, 
                          deactivated_at = NOW() 
                          WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // ثبت تاریخچه غیرفعال شدن
        $stmt = $conn->prepare("INSERT INTO user_deactivation_log (user_id, reason, deactivated_at) 
                               VALUES (:user_id, 'User requested deletion', NOW())");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        
        // حذف سشن و خروج کاربر
        session_unset();
        session_destroy();
        
        // هدیت به صفحه اصلی با پیام موفقیت
        header("Location: index.php?deleted=success");
        exit();
    } else {
        $_SESSION['error_message'] = "خطا در غیرفعال کردن پروفایل. لطفاً با پشتیبانی تماس بگیرید.";
        header("Location: edit_profile.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "خطای پایگاه داده: " . $e->getMessage();
    header("Location: edit_profile.php");
    exit();
}
?>