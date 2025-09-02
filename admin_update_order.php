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
$status = filter_input(INPUT_POST, 'status');
$trackcode = filter_input(INPUT_POST, 'trackcode');

if (!$order_id || $status === null) {
    echo json_encode(['success' => false, 'message' => 'پارامترهای نامعتبر']);
    exit;
}


// به‌روزرسانی سفارش
$stmt = $link->prepare("UPDATE orders SET status = ?, trackcode = ? WHERE id = ?");
$stmt->bind_param("ssi", $status, $trackcode, $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'سفارش با موفقیت به‌روزرسانی شد']);
} else {
    echo json_encode(['success' => false, 'message' => 'خطا در به‌روزرسانی سفارش']);
}
?>