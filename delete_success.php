<?php
$title = "حذف پروفایل کاربری";
include('./includes/header.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="bi bi-x-circle me-2"></i> حذف پروفایل کاربری</h4>
                </div>
                <div class="card-body p-4 text-center">
                    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            پروفایل کاربری شما با موفقیت غیرفعال شد.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        
                        <div class="mt-4">
                            <div class="mb-3">
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h5>حساب کاربری شما غیرفعال شد</h5>
                            <p class="text-muted">اطلاعات شما در سیستم ما حفظ می‌شوند، اما دسترسی شما به حساب کاربری محدود شده است.</p>
                            
                            <div class="d-grid gap-2 mt-4">
                                <a href="index.php" class="btn btn-primary">
                                    <i class="bi bi-house-door me-2"></i> بازگشت به صفحه اصلی
                                </a>
                                <a href="contact.php" class="btn btn-outline-primary">
                                    <i class="bi bi-envelope me-2"></i> تماس با پشتیبانی
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            درخواست حذف پروفایل نامعتبر است.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-house-door me-2"></i> بازگشت به صفحه اصلی
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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
</style>

<?php include('./includes/footer.php'); ?>