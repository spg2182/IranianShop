<?php
// شروع سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// متغیر برای ذخیره پیام‌های خطا
$error_messages = [];
$success_message = '';

// بررسی وجود و پر بودن فیلدهای فرم
if (
    isset($_POST['realname'], $_POST['username'], $_POST['password'], $_POST['repassword'], $_POST['email']) &&
    !empty(trim($_POST['realname'])) && !empty(trim($_POST['username'])) && !empty($_POST['password']) && !empty($_POST['repassword']) && !empty(trim($_POST['email']))
) {
    $realname = htmlspecialchars(trim($_POST['realname']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $repassword = $_POST['repassword'];
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    // ذخیره داده‌ها در سشن
    $_SESSION['form_data'] = $_POST;
    
    // اعتبارسنجی نام واقعی (فقط حروف فارسی)
    if (!preg_match('/^[\x{0600}-\x{06FF}\s]+$/u', $realname)) {
        $error_messages['realname'] = "نام واقعی باید فقط شامل حروف فارسی باشد.";
    }
    
    // اعتبارسنجی نام کاربری (فقط حروف لاتین و اعداد)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_messages['username'] = "نام کاربری باید فقط شامل حروف لاتین، اعداد و خط زیر باشد.";
    }
    
    // اعتبارسنجی رمز عبور (حداقل ۸ کاراکتر)
    if (strlen($password) < 8) {
        $error_messages['password'] = "کلمه عبور باید حداقل ۸ کاراکتر باشد.";
    }
    
    // اعتبارسنجی رمز عبور و تکرار آن
    if ($password !== $repassword) {
        $error_messages['repassword'] = "کلمه عبور و تکرار آن مشابه نیستند.";
    }
    
    // اعتبارسنجی ایمیل
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_messages['email'] = "پست الکترونیکی وارد شده صحیح نمی‌باشد.";
    }
} else {
    $error_messages[] = "لطفاً تمام فیلدهای ضروری را تکمیل کنید.";
}

// اگر خطا داریم، ذخیره و بازگشت به صفحه ثبت نام
if (!empty($error_messages)) {
    $_SESSION['error_messages'] = $error_messages;
    header("Location: register.php");
    exit();
}

require_once('./includes/db_link.php');

// بررسی وجود نام کاربری در پایگاه داده
$query = "SELECT username FROM users WHERE username = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $error_messages['username'] = "نام کاربری وارد شده قبلاً در سیستم ثبت شده است.";
    $_SESSION['error_messages'] = $error_messages;
    header("Location: register.php");
    exit();
}

// بررسی وجود ایمیل در پایگاه داده
$query = "SELECT email FROM users WHERE email = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $error_messages['email'] = "پست الکترونیکی وارد شده قبلاً در سیستم ثبت شده است.";
    $_SESSION['error_messages'] = $error_messages;
    header("Location: register.php");
    exit();
}

// هش کردن رمز عبور با استفاده از bcrypt
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// وارد کردن اطلاعات کاربر جدید به پایگاه داده
$query = "INSERT INTO users (realname, username, password, email, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "ssss", $realname, $username, $hashed_password, $email);

if (mysqli_stmt_execute($stmt)) {
    $user_id = mysqli_insert_id($link);
    
    // ایجاد سشن برای کاربر جدید
    session_regenerate_id(true);
    $_SESSION["state_login"] = true;
    $_SESSION["user_id"] = $user_id;
    $_SESSION["realname"] = $realname;
    $_SESSION["username"] = $username;
    $_SESSION["email"] = $email;
    $_SESSION["user_type"] = "nonadmin";
    
    // به‌روزرسانی آخرین ورود
    $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
    $update_stmt = mysqli_prepare($link, $update_query);
    mysqli_stmt_bind_param($update_stmt, "i", $user_id);
    mysqli_stmt_execute($update_stmt);
    
    // هدایت به صفحه موفقیت
    header("Location: register_success.php");
    exit();
} else {
    $error_messages[] = "عضویت شما در فروشگاه انجام نشد. لطفاً دوباره تلاش کنید.";
    $_SESSION['error_messages'] = $error_messages;
    header("Location: register.php");
    exit();
}

// بستن اتصال
mysqli_stmt_close($stmt);
mysqli_close($link);
?>