<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// تنظیمات
define('ENV', 'production'); // 'development' برای فعال‌سازی لاگ‌های بیشتر
$debugPrefix = 'log'; // در صورت نیاز مسیر لاگ‌ها را اینجا قرار دهید
$target_dir = __DIR__ . '/assets/images/';
$response = ['success' => false, 'message' => ''];

// تابع کمکی برای تشخیص AJAX
function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// تابع لاگ (فقط در development یا برای خطاها)
function dbg($filename, $content) {
    global $debugPrefix;
    if (defined('ENV') && ENV === 'development') {
        file_put_contents($debugPrefix . $filename, $content . PHP_EOL, FILE_APPEND);
    }
}

// لاگ اولیه (در development)
dbg('debug_post0.txt', print_r($_POST, true));
dbg('debug_files0.txt', print_r($_FILES, true));
dbg('debug_response0.txt', print_r($response, true));

// بررسی لاگین و دسترسی
if (!isset($_SESSION['state_login']) || $_SESSION['state_login'] !== true || (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'nonadmin')) {
    $response['message'] = 'دسترسی غیرمجاز.';
    echo json_encode($response);
    exit();
}

// اتصال به دیتابیس (mysqli)
include_once __DIR__ . '/includes/db_link.php';
if (!isset($link) || !($link instanceof mysqli)) {
    $response['message'] = 'اتصال به دیتابیس برقرار نیست.';
    dbg('debug_response_dbconn.txt', print_r($response, true));
    echo json_encode($response);
    exit();
}

// عملکرد DELETE
if (isset($_GET['action']) && $_GET['action'] === 'DELETE') {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $response['message'] = 'شناسه نامعتبر.';
        echo json_encode($response);
        exit();
    }
    $id = (int)$_GET['id'];

    // گرفتن نام تصویر قبلی
    $sel = $link->prepare("SELECT `image` FROM `products` WHERE `id` = ?");
    if (!$sel) {
        $response['message'] = 'خطای پایگاه‌داده.';
        dbg('debug_sql_error.txt', $link->error);
        echo json_encode($response);
        exit();
    }
    $sel->bind_param('i', $id);
    if (!$sel->execute()) {
        $response['message'] = 'خطا در بازیابی اطلاعات محصول.';
        dbg('debug_sql_error.txt', $sel->error);
        echo json_encode($response);
        $sel->close();
        exit();
    }
    $res = $sel->get_result();
    $row = $res->fetch_assoc();
    $imagefile = $row ? $row['image'] : '';
    $sel->close();

    // حذف از دیتابیس
    $del = $link->prepare("DELETE FROM `products` WHERE `id` = ?");
    if (!$del) {
        $response['message'] = 'خطای پایگاه‌داده.';
        dbg('debug_sql_error.txt', $link->error);
        echo json_encode($response);
        exit();
    }
    $del->bind_param('i', $id);
    if ($del->execute()) {
        // حذف فایل تصویر (در صورت وجود)
        if (!empty($imagefile)) {
            $path = $target_dir . basename($imagefile);
            if (file_exists($path)) @unlink($path);
        }
        $response['success'] = true;
        $response['message'] = 'کالا با موفقیت حذف شد.';
    } else {
        $response['message'] = 'خطایی در حین حذف کالا رخ داد.';
        dbg('debug_sql_error.txt', $del->error);
    }
    $del->close();

    // رفتار پس از حذف: AJAX -> JSON، غیر AJAX -> ریدایرکت
    if (is_ajax_request()) {
        echo json_encode($response);
        exit();
    } else {
        if ($response['success']) {
            header('Location: /admin_products.php');
            exit();
        } else {
            echo json_encode($response);
            exit();
        }
    }
}

// عملکرد EDIT (اکنون از image_old پشتیبانی می‌کند)
if (isset($_GET['action']) && $_GET['action'] === 'EDIT') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'درخواست نامعتبر.';
        echo json_encode($response);
        exit();
    }
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $response['message'] = 'شناسه نامعتبر.';
        echo json_encode($response);
        exit();
    }

    $id = (int)$_POST['id'];
    $product_code = trim($_POST['product_code'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $qty = intval($_POST['qty'] ?? 0);
    $price = trim($_POST['price'] ?? '');
    $details = trim($_POST['details'] ?? '');
    // مقدار تصویر قدیمی که از فرم ارسال می‌شود (hidden)
    $image_old = trim($_POST['image_old'] ?? '');
    $image = $image_old; // پیش‌فرض استفاده از تصویر قدیمی

    if ($product_code === '' || $name === '') {
        $response['message'] = 'لطفا فیلدهای ضروری را تکمیل کنید.';
        echo json_encode($response);
        exit();
    }

    // اگر فایل تصویر جدید ارسال شده باشد، پردازشش کن و جایگزین کن
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'خطا در آپلود فایل.';
            dbg('debug_files_error.txt', print_r($file, true));
            echo json_encode($response);
            exit();
        }

        if ($file['size'] > 500 * 1024) {
            $response['message'] = 'فایل انتخابی بیشتر از ۵۰۰ کیلوبایت است.';
            echo json_encode($response);
            exit();
        }

        $image_filetype = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($image_filetype, $allowed, true)) {
            $response['message'] = 'فقط فایل با پسوند های png و jpg و jpeg مجاز هستند.';
            echo json_encode($response);
            exit();
        }

        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0755, true);
        }

        $new_image = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '_', basename($file['name']));
        $target_file = $target_dir . $new_image;

        if (file_exists($target_file)) {
            $response['message'] = 'فایلی با همین نام در سرور وجود دارد.';
            echo json_encode($response);
            exit();
        }

        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            $response['message'] = 'خطایی در ارسال فایل عکس به سرور رخ داد.';
            dbg('debug_files_move_error.txt', print_r($file, true));
            echo json_encode($response);
            exit();
        }

        // حذف فایل تصویر قدیمی در صورت وجود و متفاوت بودن نام
        if (!empty($image_old) && $image_old !== $new_image) {
            $old_path = $target_dir . basename($image_old);
            if (file_exists($old_path)) @unlink($old_path);
        }

        $image = $new_image;
    }

    // به‌روزرسانی رکورد در دیتابیس
    $stmt = $link->prepare("UPDATE `products` SET `product_code` = ?, `name` = ?, `qty` = ?, `price` = ?, `image` = ?, `details` = ? WHERE `id` = ?");
    if (!$stmt) {
        $response['message'] = 'خطای پایگاه‌داده.';
        dbg('debug_sql_error.txt', $link->error);
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param('ssisssi', $product_code, $name, $qty, $price, $image, $details, $id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'کالا با موفقیت ویرایش شد.';
    } else {
        $response['message'] = 'خطایی در حین ویرایش کالا رخ داد.';
        dbg('debug_sql_error.txt', $stmt->error);
    }
    $stmt->close();

    echo json_encode($response);
    exit();
}

// افزودن کالا (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // فیلدهای ضروری
    $required = ['id', 'product_code', 'name', 'qty', 'price', 'details'];
    foreach ($required as $r) {
        if (!isset($_POST[$r]) || trim($_POST[$r]) === '') {
            $response['message'] = 'لطفا فرم را تکمیل کنید.';
            echo json_encode($response);
            exit();
        }
    }

    $id = intval($_POST['id']);
    $product_code = trim($_POST['product_code']);
    $name = trim($_POST['name']);
    $qty = intval($_POST['qty']);
    $price = trim($_POST['price']);
    $details = trim($_POST['details']);
    $image = '';

    // مدیریت آپلود تصویر
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['message'] = 'خطا در آپلود فایل.';
            dbg('debug_files_error.txt', print_r($file, true));
            echo json_encode($response);
            exit();
        }

        if ($file['size'] > 500 * 1024) {
            $response['message'] = 'فایل انتخابی بیشتر از ۵۰۰ کیلوبایت است.';
            echo json_encode($response);
            exit();
        }

        $image_filetype = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($image_filetype, $allowed, true)) {
            $response['message'] = 'فقط فایل با پسوند های png و jpg و jpeg مجاز هستند.';
            echo json_encode($response);
            exit();
        }

        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0755, true);
        }

        $image = uniqid() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '_', basename($file['name']));
        $target_file = $target_dir . $image;

        if (file_exists($target_file)) {
            $response['message'] = 'فایلی با همین نام در سرور وجود دارد.';
            echo json_encode($response);
            exit();
        }

        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            $response['message'] = 'خطایی در ارسال فایل عکس به سرور رخ داد.';
            dbg('debug_files_move_error.txt', print_r($file, true));
            echo json_encode($response);
            exit();
        }
    }

    // درج در دیتابیس
    $stmt = $link->prepare("INSERT INTO `products` (`id`, `product_code`, `name`, `qty`, `price`, `image`, `details`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $response['message'] = 'خطای پایگاه‌داده.';
        dbg('debug_sql_error.txt', $link->error);
        echo json_encode($response);
        exit();
    }
    $stmt->bind_param('issiiss', $id, $product_code, $name, $qty, $price, $image, $details);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'کالا با موفقیت اضافه شد.';
    } else {
        $response['message'] = 'خطایی در سمت دیتابیس حین ثبت مشخصات کالا رخ داد.';
        dbg('debug_response_db_error.txt', $stmt->error);
        // حذف فایل آپلودشده در صورت خطای دیتابیس
        if (!empty($image)) {
            $path = $target_dir . basename($image);
            if (file_exists($path)) @unlink($path);
        }
    }
    $stmt->close();

    echo json_encode($response);
    exit();
}

// درخواست نامعتبر یا ناقص
$response['message'] = 'لطفا فرم را تکمیل کنید.';
echo json_encode($response);
exit();
?>
