<?php 
include('./includes/init.php');
include('./includes/functions.php');
include_once('./includes/PersianCalendar.php');
require_once('./includes/db_link.php'); 

$siteTitle = "فروشگاه ایرانیان";

?>
<!DOCTYPE html>
<html lang="fa-IR" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="فروشگاه ایرانیان - بهترین محصولات با بهترین کیفیت و قیمت مناسب">
    <meta name="keywords" content="فروشگاه,خرید,محصولات,ایرانیان,کالای دیجیتال,لوازم خانگی">
    <meta name="author" content="گروه برنامه نویسی رهنما">
    <meta name="robots" content="index, follow">
    <?php 
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    ?>
    <link rel="canonical" href="<?php echo $currentUrl; ?>">
    
    <title><?php echo isset($title) ? $title . " &ndash; " . $siteTitle : $siteTitle; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $basePath; ?>/assets/images/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $basePath; ?>/assets/images/apple-touch-icon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>/assets/css/global.css">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $currentUrl; ?>">
    <meta property="og:title" content="<?php echo isset($title) ? $title . " &ndash; " . $siteTitle : $siteTitle; ?>">
    <meta property="og:description" content="فروشگاه ایرانیان - بهترین محصولات با بهترین کیفیت و قیمت مناسب">
    <meta property="og:image" content="<?php echo $basePath; ?>/assets/images/og-image.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo $currentUrl; ?>">
    <meta property="twitter:title" content="<?php echo isset($title) ? $title . ' – ' . $siteTitle : $siteTitle; ?>">
    <meta property="twitter:description" content="فروشگاه ایرانیان - بهترین محصولات با بهترین کیفیت و قیمت مناسب">
    <meta property="twitter:image" content="<?php echo $basePath; ?>/assets/images/og-image.jpg">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center animate-on-scroll" href="index.php">
                <img src="<?php echo $basePath; ?>/assets/images/logo.png" alt="Logo" width="50" height="50" class="me-2">
                <span class="fw-bold"><?php echo $siteTitle; ?></span>
            </a>
            <button class="navbar-toggler hamburger" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse nav" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php">
                            <i class="bi bi-house-door me-1"></i> صفحه اصلی
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php">
                            <i class="bi bi-bag me-1"></i> مقالات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="about.php">
                            <i class="bi bi-info-circle me-1"></i> درباره ما
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="contact_us.php">
                            <i class="bi bi-telephone me-1"></i> تماس با ما
                        </a>
                    </li>
                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear me-1"></i> مدیریت
                        </a>
                        <ul class="dropdown-menu dropdown-menu-start">
                            <li>
                                <a class="dropdown-item" href="admin_users.php">
                                    <i class="bi bi-people me-2"></i> مدیریت کاربران
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="admin_products.php">
                                    <i class="bi bi-box-seam me-2"></i> مدیریت کالاها
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="admin_orders.php">
                                    <i class="bi bi-cart-check me-2"></i> مدیریت سفارشات
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
                <!-- جدید اضافه کردم -->
                <ul class="navbar-nav">
                    <?php if (empty($_SESSION['state_login']) || !$_SESSION['state_login']): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="register.php">
                                <i class="bi bi-person-plus me-1"></i> عضویت
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i> ورود
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION["realname"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-start">
                                <li>
                                    <a class="dropdown-item" href="edit_profile.php">
                                        <i class="bi bi-person-gear me-2"></i> پروفایل کاربری
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="orders.php">
                                        <i class="bi bi-clock-history me-2"></i> تاریخچه سفارشات
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="wishlist.php">
                                        <i class="bi bi-heart me-2"></i> لیست علاقه‌مندی‌ها
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="?logout=confirm">
                                        <i class="bi bi-box-arrow-right me-2"></i> خروج
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-white position-relative" href="cart.php">
                            <i class="bi bi-cart3"></i>
                            <?php 
                            // نمایش تعداد محصولات در سبد خرید
                            if (isset($_SESSION['state_login']) && $_SESSION['state_login'] === true) {
                                $user_id = $_SESSION['user_id'];
                                $stmt = $link->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_assoc();
                                $cart_count = $row['total'] ?? 0;
                            } else {
                                $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                            }
                            
                            if ($cart_count > 0) {
                                echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">' . $cart_count . '</span>';
                            }
                            ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <aside class="col-md-3">
                <?php include('./includes/aside.php'); ?>
            </aside>
            <main class="col-md-9">
                <?php if (isset($_GET['logout']) && $_GET['logout'] === 'confirm'): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        آیا مطمئن هستید که می‌خواهید از حساب کاربری خود خارج شوید؟
                        <div class="mt-2">
                            <a href="<?php echo $basePath; ?>/logout.php?confirm=true" class="btn btn-danger btn-sm me-2">
                                <i class="bi bi-check-circle me-1"></i> بله، خروج
                            </a>
                            <a href="index.php" class="btn btn-secondary btn-sm">
                                <i class="bi bi-x-circle me-1"></i> انصراف
                            </a>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>