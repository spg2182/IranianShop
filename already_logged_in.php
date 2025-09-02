<?php
// بررسی وضعیت ورود کاربر
if (!isset($_SESSION["state_login"]) || $_SESSION["state_login"] !== true) {
    header("Location: login.php");
    exit;
}

$title = "شما قبلا وارد شدید";
include("./includes/header.php");

// نمایش پیام خروج موفق در صورت وجود
$logoutMessage = '';
if (isset($_GET['status']) && $_GET['status'] == 'logged_out') {
    $logoutMessage = '
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                با موفقیت از حساب کاربری خود خارج شدید.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    ';
}

// تعیین آیکون و رنگ بر اساس نوع کاربری
$userTypeIcon = $_SESSION["user_type"] === 'admin' ? 'shield-fill-check' : 'person-fill';
$userTypeColor = $_SESSION["user_type"] === 'admin' ? 'danger' : 'primary';
$userTypeText = $_SESSION["user_type"] === 'admin' ? 'مدیر' : 'کاربر عادی';
?>
<?php echo $logoutMessage; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-person-check" style="font-size: 4rem; color: #28a745;"></i>
                        </div>
                        <h3 class="card-title fw-bold mb-2">شما قبلاً وارد شده‌اید</h3>
                        <p class="text-muted">خوش آمدید! شما در حال حاضر وارد حساب کاربری خود هستید.</p>
                    </div>
                    
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        کاربر گرامی <strong><?php echo htmlspecialchars($_SESSION["realname"]); ?></strong>،<br>
                        شما قبلاً ثبت نام کرده و وارد شده‌اید.
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="bi bi-person-badge me-2"></i>اطلاعات حساب کاربری
                            </h5>
                            <div class="mb-3">
                                <div class="row text-start">
                                    <div class="col-5">
                                        <strong><i class="bi bi-person me-1"></i> نام کاربری:</strong>
                                    </div>
                                    <div class="col-7">
                                        <span class="fw-semibold"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="row text-start">
                                    <div class="col-5">
                                        <strong><i class="bi bi-<?php echo $userTypeIcon; ?> me-1"></i> نوع کاربری:</strong>
                                    </div>
                                    <div class="col-7">
                                        <span class="badge bg-<?php echo $userTypeColor; ?> fs-6">
                                            <?php echo $userTypeText; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php if (isset($_SESSION["email"])): ?>
                            <div class="mb-2">
                                <div class="row text-start">
                                    <div class="col-5">
                                        <strong><i class="bi bi-envelope me-1"></i> ایمیل:</strong>
                                    </div>
                                    <div class="col-7">
                                        <span class="text-muted"><?php echo htmlspecialchars($_SESSION["email"]); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="index.php" class="btn btn-primary py-2">
                            <i class="bi bi-house-door me-2"></i> رفتن به صفحه اصلی
                        </a>
                        <?php if ($_SESSION["user_type"] === 'admin'): ?>
                        <a href="admin_products.php" class="btn btn-outline-primary py-2">
                            <i class="bi bi-box-seam me-2"></i> مدیریت کالاها
                        </a>
                        <?php endif; ?>
                        <a href="edit_profile.php" class="btn btn-outline-secondary py-2">
                            <i class="bi bi-person-gear me-2"></i> ویرایش پروفایل
                        </a>
                        <a href="orders.php" class="btn btn-outline-info py-2">
                            <i class="bi bi-clock-history me-2"></i> تاریخچه سفارشات
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger py-2">
                            <i class="bi bi-box-arrow-right me-2"></i> خروج از حساب کاربری
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("./includes/footer.php"); ?>