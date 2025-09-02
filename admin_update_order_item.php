<?php
include_once('./includes/functions.php');
include('includes/db_link.php');

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'روش درخواست نامعتبر است']);
    exit;
}

$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$item_id || !$quantity || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'پارامترهای نامعتبر']);
    exit;
}


// دریافت اطلاعات آیتم سفارش
$stmt = $link->prepare("SELECT * FROM order_items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'آیتم سفارش یافت نشد']);
    exit;
}

// دریافت اطلاعات محصول
$stmt = $link->prepare("SELECT qty FROM products WHERE id = ?");
$stmt->bind_param("i", $item['product_id']);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'محصول یافت نشد']);
    exit;
}

// محاسبه تفاوت تعداد
$diff = $quantity - $item['quantity'];

// بررسی موجودی
if ($diff > 0 && $product['qty'] < $diff) {
    echo json_encode(['success' => false, 'message' => 'موجودی محصول کافی نیست']);
    exit;
}

// به‌روزرسانی تعداد
$stmt = $link->prepare("UPDATE order_items SET quantity = ? WHERE id = ?");
$stmt->bind_param("ii", $quantity, $item_id);

if ($stmt->execute()) {
    // به‌روزرسانی موجودی محصول
    $stmt = $link->prepare("UPDATE products SET qty = qty - ? WHERE id = ?");
    $stmt->bind_param("ii", $diff, $item['product_id']);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'تعداد محصول با موفقیت به‌روزرسانی شد',
        'new_quantity' => $quantity,
        'new_stock' => $product['qty'] - $diff
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'خطا در به‌روزرسانی تعداد محصول']);
}
?>