<?php
$title = "عضویت موفقیت‌آمیز";
include('./includes/header.php');

// بررسی اینکه کاربر از طریق فرم ثبت نام آمده است
if (!isset($_SESSION["state_login"]) || $_SESSION["state_login"] !== true) {
    header("Location: index.php");
    exit();
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i> ثبت نام موفق</h4>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        کاربر گرامی <?php echo htmlspecialchars($_SESSION["realname"]); ?> (با نام کاربری <?php echo htmlspecialchars($_SESSION["username"]); ?>)، عضویت شما در فروشگاه با موفقیت انجام شد.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    
                    <div class="mt-4">
                        <div class="mb-3">
                            <i class="bi bi-person-check-fill text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h5>خوش آمدید!</h5>
                        <p class="text-muted">حالا می‌توانید از تمام امکانات فروشگاه استفاده کنید.</p>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-house-door me-2"></i> ورود به صفحه اصلی
                            </a>
                            <a href="edit_profile.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-gear me-2"></i> تکمیل پروفایل کاربری
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-5 pt-4 border-top">
                        <h6>پیشنهاد ویژه برای شما:</h6>
                        <div class="row g-3 mt-2">
                            <div class="col-6">
                                <div class="card border-primary">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-gift text-primary fs-1"></i>
                                        <h6 class="mt-2">کد تخفیف اولین خرید</h6>
                                        <p class="mb-0">WELCOME10</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card border-success">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-star-fill text-success fs-1"></i>
                                        <h6 class="mt-2">عضویت در باشگاه مشتریان</h6>
                                        <p class="mb-0">امتیاز کسب کنید</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // انیمیشن برای کارت
    const card = document.querySelector('.card');
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        card.style.transition = 'all 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 100);
    
    // کپی کردن کد تخفیف با کلیک
    const discountCode = document.querySelector('.card.border-primary .card-body p');
    if (discountCode) {
        discountCode.style.cursor = 'pointer';
        discountCode.addEventListener('click', function() {
            navigator.clipboard.writeText(this.textContent).then(() => {
                const originalText = this.textContent;
                this.textContent = 'کپی شد!';
                this.classList.add('text-success');
                
                setTimeout(() => {
                    this.textContent = originalText;
                    this.classList.remove('text-success');
                }, 2000);
            });
        });
    }
});
</script>

<style>
.card {
    border-radius: 0.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.btn {
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
}

.alert {
    border-radius: 0.5rem;
    border: none;
}

.border-primary {
    transition: all 0.3s ease;
}

.border-primary:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 123, 255, 0.1);
}

.border-success:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(40, 167, 69, 0.1);
}
</style>

<?php include('./includes/footer.php'); ?>