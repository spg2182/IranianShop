<?php
include_once('./includes/functions.php');
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'روش درخواست نامعتبر است']);
    exit;
}

$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);

if (!$item_id) {
    echo json_encode(['success' => false, 'message' => 'پارامترهای نامعتبر']);
    exit;
}

include('includes/db_link.php');

// دریافت اطلاعات آیتم سفارش
$stmt = $link->prepare("SELECT * FROM order_items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'آیتم سفارش یافت نشد']);
    exit;
}

// حذف آیتم
$stmt = $link->prepare("DELETE FROM order_items WHERE id = ?");
$stmt->bind_param("i", $item_id);

if ($stmt->execute()) {
    // بازگرداندن موجودی محصول
    $stmt = $link->prepare("UPDATE products SET qty = qty + ? WHERE id = ?");
    $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'محصول با موفقیت از سفارش حذف شد']);
} else {
    echo json_encode(['success' => false, 'message' => 'خطا در حذف محصول از سفارش']);
}
?>