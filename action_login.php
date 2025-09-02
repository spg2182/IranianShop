<?php
// اطمینان از اینکه هیچ خروجی قبلی ارسال نشده است
if (ob_get_level()) {
    ob_end_clean();
}

// شروع سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// محدودیت نرخ درخواست (حداکثر 5 تلاش در 5 دقیقه)
$rate_limit_key = 'login_attempts_' . $_SERVER['REMOTE_ADDR'];
$currentTime = time();

if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = ['count' => 0, 'time' => $currentTime];
}

$rate_limit = $_SESSION[$rate_limit_key];

// اگر 5 دقیقه گذشته باشد، شمارنده را ریست کن
if (($currentTime - $rate_limit['time']) >= 300) {
    $_SESSION[$rate_limit_key] = ['count' => 1, 'time' => $currentTime];
} else {
    $_SESSION[$rate_limit_key]['count']++;
}

// بررسی محدودیت نرخ درخواست
if ($rate_limit['count'] >= 5 && ($currentTime - $rate_limit['time']) < 300) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(429);
    die(json_encode(['success' => false, 'message' => 'تعداد درخواست‌های شما بیش از حد مجاز است. لطفاً 5 دقیقه دیگر دوباره تلاش کنید.']));
}

// تابع برای تنظیم پیام‌های فلش
function set_flash($type, $text) {
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}

// بررسی درخواست POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'متد درخواست نامعتبر است.']));
}

// بررسی CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'درخواست نامعتبر است. لطفاً صفحه را رفرش کنید.']));
}

// بررسی فیلدهای ضروری
if (
    !isset($_POST['username']) || empty(trim($_POST['username'])) ||
    !isset($_POST['password']) || empty(trim($_POST['password']))
) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'لطفاً تمام فیلدها را تکمیل کنید.']));
}

// پاکسازی و اعتبارسنجی ورودی‌ها
$username = trim($_POST['username']);
$password = $_POST['password'];

// اعتبارسنجی نام کاربری (فقط حروف انگلیسی و اعداد)
if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'نام کاربری فقط می‌تواند شامل حروف انگلیسی و اعداد باشد.']));
}

// محدود کردن طول نام کاربری
if (strlen($username) < 3 || strlen($username) > 20) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'نام کاربری باید بین 3 تا 20 کاراکتر باشد.']));
}

// اتصال به پایگاه داده
include("./includes/db_link.php");

// استفاده از Prepared Statements برای جلوگیری از SQL Injection
$query = "SELECT id, username, password, realname, email, user_type, is_active, login_attempts, last_login_attempt FROM users WHERE username = ?";
$stmt = mysqli_prepare($link, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user_found = false;
$user = null;
$login_success = false;

// بررسی وجود کاربر
if ($result && mysqli_num_rows($result) > 0) {
    $user_found = true;
    $user = mysqli_fetch_assoc($result);
    
    // بررسی قفل بودن حساب کاربری
    if ($user['login_attempts'] >= 5) {
        $last_attempt = new DateTime($user['last_login_attempt']);
        $now = new DateTime();
        $interval = $now->diff($last_attempt);
        
        // اگر 30 دقیقه از آخرین تلاش ناموفق گذشته باشد، تلاش‌ها را ریست کن
        if ($interval->i >= 30) {
            $reset_query = "UPDATE users SET login_attempts = 0 WHERE id = ?";
            $reset_stmt = mysqli_prepare($link, $reset_query);
            mysqli_stmt_bind_param($reset_stmt, "i", $user['id']);
            mysqli_stmt_execute($reset_stmt);
            mysqli_stmt_close($reset_stmt);
            $user['login_attempts'] = 0;
        } else {
            mysqli_close($link);
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(423);
            die(json_encode(['success' => false, 'message' => 'حساب کاربری شما به دلیل تلاش‌های متعدد موقتاً قفل شده است. لطفاً 30 دقیقه دیگر دوباره تلاش کنید.']));
        }
    }
    
    // بررسی رمز عبور
    if (password_verify($password, $user['password'])) {
        // بررسی وضعیت فعال بودن کاربر
        if ($user['is_active'] == 0) {
            mysqli_close($link);
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(403);
            die(json_encode(['success' => false, 'message' => 'حساب کاربری شما غیرفعال است. لطفاً با مدیریت تماس بگیرید.']));
        }
        
        // ریست کردن تلاش‌های ورود ناموفق
        $reset_query = "UPDATE users SET login_attempts = 0 WHERE id = ?";
        $reset_stmt = mysqli_prepare($link, $reset_query);
        mysqli_stmt_bind_param($reset_stmt, "i", $user['id']);
        mysqli_stmt_execute($reset_stmt);
        mysqli_stmt_close($reset_stmt);
        
        // به‌روزرسانی آخرین ورود
        $update_query = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $update_stmt = mysqli_prepare($link, $update_query);
        mysqli_stmt_bind_param($update_stmt, "i", $user['id']);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
        
        // ایجاد سشن جدید برای جلوگیری از session fixation
        session_regenerate_id(true);
        
        // تنظیم متغیرهای سشن
        $_SESSION["state_login"] = true;
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["realname"] = $user["realname"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["user_type"] = $user["user_type"] == 1 ? "admin" : "nonadmin";
        
        // تنظیم کوکی برای به خاطر سپردن کاربر (اختیاری)
        if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + 30 * 24 * 3600; // 30 روز
            
            // ذخیره توکن در پایگاه داده
            $token_query = "INSERT INTO user_tokens (user_id, token, expiry) VALUES (?, ?, ?)";
            $token_stmt = mysqli_prepare($link, $token_query);
            $expiry_date = date('Y-m-d H:i:s', $expiry);
            mysqli_stmt_bind_param($token_stmt, "iss", $user['id'], $token, $expiry_date);
            mysqli_stmt_execute($token_stmt);
            mysqli_stmt_close($token_stmt);
            
            // تنظیم کوکی با امنیت بالا
            setcookie('remember_token', $token, [
                'expires' => $expiry,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        
        $login_success = true;
    } else {
        // افزایش شمارنده تلاش‌های ناموفق
        $attempts = $user['login_attempts'] + 1;
        $update_query = "UPDATE users SET login_attempts = ?, last_login_attempt = NOW() WHERE id = ?";
        $update_stmt = mysqli_prepare($link, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ii", $attempts, $user['id']);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);
        
        // پیام مناسب بر اساس تعداد تلاش‌های باقی‌مانده
        $remaining_attempts = 5 - $attempts;
        $message = $remaining_attempts > 0 
            ? "اطلاعات وارد شده صحیح نمی‌باشد. {$remaining_attempts} تلاش دیگر باقی مانده است."
            : 'حساب کاربری شما به دلیل تلاش‌های متعدد موقتاً قفل شده است. لطفاً 30 دقیقه دیگر دوباره تلاش کنید.';
        
        mysqli_close($link);
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($remaining_attempts > 0 ? 400 : 423);
        die(json_encode(['success' => false, 'message' => $message]));
    }
} else {
    mysqli_close($link);
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'اطلاعات وارد شده صحیح نمی‌باشد.']));
}

// ثبت لاگ ورود
$log_query = "INSERT INTO login_logs (user_id, username, ip_address, user_agent, success) VALUES (?, ?, ?, ?, ?)";
$log_stmt = mysqli_prepare($link, $log_query);
mysqli_stmt_bind_param($log_stmt, "isssi", 
    $user_found ? $user['id'] : null, 
    $username, 
    $_SERVER['REMOTE_ADDR'], 
    $_SERVER['HTTP_USER_AGENT'], 
    $login_success
);
mysqli_stmt_execute($log_stmt);
mysqli_stmt_close($log_stmt);

mysqli_close($link);

// اگر ورود موفقیت‌آمیز بود
if ($login_success) {
    header('Content-Type: application/json; charset=utf-8');
    // پس از لاگین موفق و تنظیم سشن‌ها

        // انتقال سبد خرید سشن به دیتابیس
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $user_id = $_SESSION['user_id'];
            
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                // بررسی وجود محصول در دیتابیس
                $stmt = $link->prepare("SELECT id, qty FROM products WHERE id = ?");
                $stmt->bind_param("i", $product_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    
                    // بررسی موجودی محصول
                    if ($quantity > $product['qty']) {
                        $quantity = $product['qty'];
                    }
                    
                    // بررسی وجود محصول در سبد خرید دیتابیس
                    $stmt = $link->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $user_id, $product_id);
                    $stmt->execute();
                    $cart_result = $stmt->get_result();
                    
                    if ($cart_result->num_rows > 0) {
                        // به‌روزرسانی تعداد
                        $cart_item = $cart_result->fetch_assoc();
                        $new_quantity = $cart_item['quantity'] + $quantity;
                        
                        $stmt = $link->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                        $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
                    } else {
                        // افزودن محصول جدید
                        $stmt = $link->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                    }
                    
                    $stmt->execute();
                }
            }
            
            // حذف سبد خرید سشن
            unset($_SESSION['cart']);
        }
    die(json_encode(['success' => true, 'redirect' => 'index.php']));
}
?>