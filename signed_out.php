<?php
session_start();

// بررسی متد درخواست
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // خاتمه نشست و حذف اطلاعات نشست
    session_unset();
    session_destroy();

    // هدایت به صفحه ورود با پیام موفقیت
    header("Location: login.php?status=logged_out");
    exit;
}

// جلوگیری از کش شدن صفحات
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خروج از سایت</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="logout-container">
        <h2>آیا مطمئن هستید که می‌خواهید از حساب کاربری خود خارج شوید؟</h2>
        <form method="post" action="">
            <button type="submit" class="btn-logout">خروج از حساب</button>
            <a href="index.php" class="btn-cancel">انصراف</a>
        </form>
    </div>

    <script>
        // نمایش پیام تایید خروج با استفاده از confirm
        if (confirm("آیا مطمئن هستید که می‌خواهید از حساب کاربری خود خارج شوید؟")) {
            document.querySelector("form").submit();
        } else {
            window.location.href = "index.php";
        }
    </script>
</body>
</html>
