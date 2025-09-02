<?php
// غیرفعال کردن نمایش خطاها در محیط تولید
error_reporting(0);
ini_set('display_errors', 0);

require_once('includes/init.php');
require_once('includes/db_link.php');

// تنظیم هدر برای JSON
header('Content-Type: application/json');

// جلوگیری از هرگونه خروجی قبل از JSON
ob_start();

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
        
        if (!$product_id) {
            $response['message'] = 'محصول نامعتبر است';
            echo json_encode($response);
            exit;
        }
        
        // بررسی وجود محصول
        $stmt = $link->prepare("SELECT id FROM products WHERE id = ?");
        if (!$stmt) {
            throw new Exception('خطا در آماده‌سازی پرس‌وجو');
        }
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response['message'] = 'محصول یافت نشد';
            echo json_encode($response);
            exit;
        }
        
        // بررسی ورود کاربر
        if (!isset($_SESSION['state_login']) || $_SESSION['state_login'] !== true || !isset($_SESSION['user_id'])) {
            $response['message'] = 'برای استفاده از این قابلیت باید وارد شوید';
            echo json_encode($response);
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        
        switch ($action) {
            case 'add':
                // بررسی وجود محصول در لیست علاقه‌مندی‌ها
                $stmt = $link->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $wishlist_result = $stmt->get_result();
                
                if ($wishlist_result->num_rows > 0) {
                    $response['message'] = 'محصول قبلاً به لیست علاقه‌مندی‌ها اضافه شده است';
                } else {
                    $stmt = $link->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $user_id, $product_id);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'محصول به لیست علاقه‌مندی‌ها اضافه شد';
                    } else {
                        $response['message'] = 'خطا در افزودن محصول به لیست علاقه‌مندی‌ها';
                    }
                }
                break;
                
            case 'remove':
                $stmt = $link->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'محصول از لیست علاقه‌مندی‌ها حذف شد';
                } else {
                    $response['message'] = 'خطا در حذف محصول از لیست علاقه‌مندی‌ها';
                }
                break;
                
            default:
                $response['message'] = 'عملیات نامعتبر است';
        }
    } else {
        $response['message'] = 'متد درخواست نامعتبر است';
    }
} catch (Exception $e) {
    $response['message'] = 'خطا: ' . $e->getMessage();
}

// اطمینان از اینکه هیچ خروجی دیگری وجود ندارد
ob_end_clean();
   if ($response !== true) 
        echo json_encode($response);
?>