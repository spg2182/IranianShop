<?php
// شروع سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// include کردن فایل توابع که شامل تابع require_admin است
include_once('./includes/functions.php');

// بررسی دسترسی مدیر (هم ورود کاربر و هم سطح دسترسی را بررسی می‌کند)
require_admin();

// تنظیم هدر برای پاسخ JSON
header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=localhost;dbname=iranianshop", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // بررسی وجود ID کاربر
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'شناسه کاربر مشخص نشده است.'
        ]);
        exit;
    }
    
    $user_id = (int)$_GET['id'];
    
    // بررسی اینکه کاربر مورد نظر وجود دارد
    $stmt = $conn->prepare("SELECT id, username, user_type FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'کاربر مورد نظر یافت نشد.'
        ]);
        exit;
    }
    
    // بررسی اینکه کاربر مدیر اصلی سیستم نباشد
    if ($user_id == 1) {
        echo json_encode([
            'success' => false,
            'message' => 'مدیر اصلی سیستم قابل حذف نیست.'
        ]);
        exit;
    }
    
    // بررسی اینکه کاربر مدیر نباشد
    if ($user['user_type'] == 1) {
        echo json_encode([
            'success' => false,
            'message' => 'کاربران مدیر سیستم قابل حذف نیستند.'
        ]);
        exit;
    }
    
    // حذف کاربر
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => "کاربر {$user['username']} با موفقیت حذف شد.",
            'redirect' => 'admin_users.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در حذف کاربر. لطفاً دوباره تلاش کنید.'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطای پایگاه داده: ' . $e->getMessage()
    ]);
}
?>