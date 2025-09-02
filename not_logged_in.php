<?php
$title = "دسترسی محدود";
include('./includes/header.php');
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="bi bi-shield-lock" style="font-size: 4rem; color: #6c757d;"></i>
                    </div>
                    
                    <h3 class="card-title mb-4">دسترسی محدود</h3>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        شما اجازه دسترسی به این صفحه را ندارید.
                    </div>
                    
                    <p class="text-muted mb-4">
                        این صفحه فقط برای کاربران سایت قابل مشاهده است.
                    </p>
                    
                    <div class="d-grid gap-2">
                        <a href="login.php" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i> ورود به حساب کاربری
                        </a>
                        <a href="register.php" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus me-2"></i> ثبت نام در سایت
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <a href="index.php" class="text-decoration-none">
                            <i class="bi bi-house-door me-1"></i> بازگشت به صفحه اصلی
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('./includes/footer.php'); ?>