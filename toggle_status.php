<?php
session_start();
// اتصال به پایگاه داده با استفاده از PDO
try {
    $conn = new PDO("mysql:host=localhost;dbname=iranianshop", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("اتصال به پایگاه داده ناموفق:\n " . $e->getMessage());
} // اتصال به پایگاه داده

if (!isset($_SESSION["state_login"]) || $_SESSION["state_login"] !== true) {
    echo "<script>location.replace('./not_logged_in.php');</script>";
    exit;
}

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    // دریافت وضعیت فعلی کاربر
    $stmt = $conn->prepare("SELECT is_active FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // تغییر وضعیت کاربر
        $new_status = $user['is_active'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE users SET is_active = :status, deactivated_at = NOW() WHERE id = :id");
        $stmt->bindParam(':status', $new_status, PDO::PARAM_INT);
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // هدایت به صفحه مدیریت کاربران با پیام موفقیت
        $_SESSION['message'] = 'وضعیت کاربر با موفقیت تغییر یافت.';
        header("Location: admin_users.php");
        exit;
    } else {
        echo "کاربر یافت نشد.";
    }
} else {
    echo "شناسه کاربر مشخص نشده است.";
}
?>
