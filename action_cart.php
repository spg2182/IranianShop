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
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;
        
        if (!$product_id) {
            $response['message'] = 'محصول نامعتبر است';
            echo json_encode($response);
            exit;
        }
        
        // بررسی وجود محصول
        $stmt = $link->prepare("SELECT id, name, qty, price FROM products WHERE id = ?");
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
        
        $product = $result->fetch_assoc();
        
        // بررسی موجودی محصول
        if ($product['qty'] < $quantity) {
            $response['message'] = 'موجودی محصول کافی نیست';
            echo json_encode($response);
            exit;
        }
        
        // اگر کاربر لاگین کرده از دیتابیس استفاده کن، در غیر این صورت از سشن
        if (isset($_SESSION['state_login']) && $_SESSION['state_login'] === true && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            switch ($action) {
                case 'add':
                    // بررسی وجود محصول در سبد خرید
                    $stmt = $link->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $user_id, $product_id);
                    $stmt->execute();
                    $cart_result = $stmt->get_result();
                    
                    if ($cart_result->num_rows > 0) {
                        $cart_item = $cart_result->fetch_assoc();
                        $new_quantity = $cart_item['quantity'] + $quantity;
                        
                        if ($new_quantity > $product['qty']) {
                            $response['message'] = 'موجودی محصول کافی نیست';
                            echo json_encode($response);
                            exit;
                        }
                        
                        $stmt = $link->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                        $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                    } else {
                        $stmt = $link->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                    }
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'محصول به سبد خرید اضافه شد';
                        $response['cart_count'] = getCartCount($user_id);
                    } else {
                        $response['message'] = 'خطا در افزودن محصول به سبد خرید';
                    }
                    break;
                    
                case 'update':
                    if ($quantity < 1) {
                        $response['message'] = 'تعداد نامعتبر است';
                        echo json_encode($response);
                        exit;
                    }
                    
                    if ($quantity > $product['qty']) {
                        $response['message'] = 'موجودی محصول کافی نیست';
                        echo json_encode($response);
                        exit;
                    }
                    
                    $stmt = $link->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'سبد خرید به‌روزرسانی شد';
                        $response['cart_count'] = getCartCount($user_id);
                    } else {
                        $response['message'] = 'خطا در به‌روزرسانی سبد خرید';
                    }
                    break;
                    
                case 'remove':
                    $stmt = $link->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $user_id, $product_id);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'محصول از سبد خرید حذف شد';
                        $response['cart_count'] = getCartCount($user_id);
                    } else {
                        $response['message'] = 'خطا در حذف محصول از سبد خرید';
                    }
                    break;
                    
                default:
                    $response['message'] = 'عملیات نامعتبر است';
            }
        } else {
            // کاربر مهمان - استفاده از سشن
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            switch ($action) {
                case 'add':
                    if (isset($_SESSION['cart'][$product_id])) {
                        $_SESSION['cart'][$product_id] += $quantity;
                    } else {
                        $_SESSION['cart'][$product_id] = $quantity;
                    }
                    
                    // بررسی تعداد کل از موجودی بیشتر نشود
                    if ($_SESSION['cart'][$product_id] > $product['qty']) {
                        $_SESSION['cart'][$product_id] = $product['qty'];
                        $response['message'] = 'تعداد درخواستی از موجودی انبار بیشتر است. تعداد به حداکثر موجودی تنظیم شد.';
                    } else {
                        $response['message'] = 'محصول به سبد خرید اضافه شد';
                    }
                    
                    $response['success'] = true;
                    $response['cart_count'] = array_sum($_SESSION['cart']);
                    break;
                    
                case 'update':
                    if ($quantity < 1) {
                        $response['message'] = 'تعداد نامعتبر است';
                        echo json_encode($response);
                        exit;
                    }
                    
                    if ($quantity > $product['qty']) {
                        $quantity = $product['qty'];
                        $response['message'] = 'تعداد درخواستی از موجودی انبار بیشتر است. تعداد به حداکثر موجودی تنظیم شد.';
                    }
                    
                    $_SESSION['cart'][$product_id] = $quantity;
                    $response['success'] = true;
                    $response['message'] = 'سبد خرید به‌روزرسانی شد';
                    $response['cart_count'] = array_sum($_SESSION['cart']);
                    break;
                    
                case 'remove':
                    unset($_SESSION['cart'][$product_id]);
                    $response['success'] = true;
                    $response['message'] = 'محصول از سبد خرید حذف شد';
                    $response['cart_count'] = array_sum($_SESSION['cart']);
                    break;
                    
                default:
                    $response['message'] = 'عملیات نامعتبر است';
            }
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

function getCartCount($user_id) {
    global $link;
    $stmt = $link->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}
?>