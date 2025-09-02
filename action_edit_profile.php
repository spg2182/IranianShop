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

// بررسی ارسال فرم
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: edit_profile.php");
    exit;
}

$error_messages = [];

// دریافت و اعتبارسنجی داده‌ها
$realname = trim($_POST['realname'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');

// اعتبارسنجی فیلدها
if (empty($realname)) {
    $error_messages['realname'] = "نام واقعی را وارد کنید.";
} elseif (!preg_match('/^[\x{0600}-\x{06FF}\s]+$/u', $realname)) {
    $error_messages['realname'] = "نام واقعی باید فقط شامل حروف فارسی باشد.";
}

if (empty($username)) {
    $error_messages['username'] = "نام کاربری را وارد کنید.";
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $error_messages['username'] = "نام کاربری باید فقط شامل حروف لاتین، اعداد و خط زیر باشد.";
}

if (empty($email)) {
    $error_messages['email'] = "پست الکترونیکی را وارد کنید.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_messages['email'] = "پست الکترونیکی معتبر نیست.";
}

// اگر خطایی وجود نداشت
if (empty($error_messages)) {
    try {
        $conn = new PDO("mysql:host=localhost;dbname=iranianshop", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // بررسی تکراری نبودن نام کاربری (برای کاربران دیگر)
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $error_messages['username'] = "نام کاربری قبلاً ثبت شده است.";
        } else {
            // بررسی تکراری نبودن ایمیل (برای کاربران دیگر)
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error_messages['email'] = "پست الکترونیکی قبلاً ثبت شده است.";
            } else {
                // به‌روزرسانی اطلاعات کاربر
                $stmt = $conn->prepare("UPDATE users SET 
                                      realname = :realname, 
                                      username = :username, 
                                      email = :email, 
                                      updated_at = NOW() 
                                      WHERE id = :id");
                $stmt->bindParam(':realname', $realname);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    // به‌روزرسانی سشن
                    $_SESSION['realname'] = $realname;
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    
                    $_SESSION['success_message'] = "اطلاعات پروفایل شما با موفقیت به‌روزرسانی شد.";
                    header("Location: edit_profile.php");
                    exit();
                } else {
                    $error_messages[] = "خطا در به‌روزرسانی اطلاعات.";
                }
            }
        }
    } catch (PDOException $e) {
        $error_messages[] = "خطای پایگاه داده: " . $e->getMessage();
    }
}

// اگر خطا وجود داشت، آن را در سشن ذخیره کن و به صفحه ویرایش پروفایل هدایت کن
if (!empty($error_messages)) {
    $_SESSION['error_messages'] = $error_messages;
    header("Location: edit_profile.php");
    exit();
}
?>