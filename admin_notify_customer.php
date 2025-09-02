<?php
include_once('./includes/functions.php');
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'روش درخواست نامعتبر است']);
    exit;
}

$order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$message = filter_input(INPUT_POST, 'message');

if (!$order_id || !$message) {
    echo json_encode(['success' => false, 'message' => 'پارامترهای نامعتبر']);
    exit;
}

include('includes/db_link.php');

// دریافت اطلاعات سفارش و مشتری
$stmt = $link->prepare("
    SELECT o.*, u.email, u.realname 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'سفارش یافت نشد']);
    exit;
}

// در اینجا می‌توانید ایمیل یا پیامک به مشتری ارسال کنید
// برای مثال ارسال ایمیل:
$subject = "به‌روزرسانی سفارش #" . $order_id;
$email_body = "
    <html>
    <head>
        <title>به‌روزرسانی سفارش</title>
    </head>
    <body>
        <p>سلام {$order['realname']}،</p>
        <p>سفارش شما با شماره {$order_id} به‌روزرسانی شد:</p>
        <p>{$message}</p>
        <p>با تشکر،<br>فروشگاه ما</p>
    </body>
    </html>
";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: <noreply@yourstore.com>' . "\r\n";

// ارسال ایمیل
$mail_sent = mail($order['email'], $subject, $email_body, $headers);

if ($mail_sent) {
    // ذخیره پیام در دیتابیس
    $stmt = $link->prepare("INSERT INTO order_notifications (order_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $order_id, $message);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'پیام با موفقیت برای مشتری ارسال شد']);
} else {
    echo json_encode(['success' => false, 'message' => 'خطا در ارسال پیام به مشتری']);
}
?>