<?php
include_once('./includes/functions.php');
include('includes/db_link.php');

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'روش درخواست نامعتبر است']);
    exit;
}

$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$order_id || !$product_id || !$quantity || $quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'پارامترهای نامعتبر']);
    exit;
}


// بررسی موجودی محصول
$stmt = $link->prepare("SELECT qty, price, name, image FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product || $product['qty'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'موجودی محصول کافی نیست']);
    exit;
}

// افزودن محصول به سفارش
$stmt = $link->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiid", $order_id, $product_id, $quantity, $product['price']);

if ($stmt->execute()) {
    $item_id = $link->insert_id;
    
    // به‌روزرسانی موجودی محصول
    $stmt = $link->prepare("UPDATE products SET qty = qty - ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();
    
    // بازگرداندن اطلاعات کامل برای افزودن به جدول
    echo json_encode([
        'success' => true, 
        'message' => 'محصول با موفقیت به سفارش اضافه شد',
        'item_id' => $item_id,
        'product_id' => $product_id,
        'name' => $product['name'],
        'image' => $product['image'],
        'price' => $product['price'],
        'quantity' => $quantity,
        'stock' => $product['qty'] - $quantity
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'خطا در افزودن محصول به سفارش']);
}
?>