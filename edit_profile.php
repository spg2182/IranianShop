<?php
$title = "بروزرسانی نمایه کاربری";
include('./includes/header.php');

// بررسی وضعیت ورود کاربر
if (!isset($_SESSION["state_login"]) || $_SESSION["state_login"] !== true) {
    header("Location: not_logged_in.php");
    exit;
}

// دریافت اطلاعات کاربر از پایگاه داده
try {
    $conn = new PDO("mysql:host=localhost;dbname=iranianshop", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-gear me-2"></i> ویرایش پروفایل کاربری</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo $_SESSION['success_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_messages'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>خطا!</strong> لطفاً موارد زیر را بررسی کنید:
                            <ul class="mb-0 mt-2">
                                <?php foreach ($_SESSION['error_messages'] as $message): ?>
                                    <li><?php echo htmlspecialchars($message); ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_messages']); ?>
                    <?php endif; ?>
                    
                    <form method="post" action="action_edit_profile.php" id="editProfileForm" novalidate>
                        <div class="mb-3">
                            <label for="realname" class="form-label">نام واقعی <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="realname" name="realname" 
                                       value="<?= htmlspecialchars($user['realname']) ?>" 
                                       placeholder="نام و نام خانوادگی" required>
                            </div>
                            <div class="invalid-feedback">
                                لطفاً نام واقعی را وارد کنید.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">نام کاربری <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                <input type="text" class="form-control ltr" id="username" name="username" 
                                       value="<?= htmlspecialchars($user['username']) ?>" 
                                       placeholder="نام کاربری (فقط لاتین)" required>
                            </div>
                            <div class="invalid-feedback">
                                لطفاً نام کاربری معتبر وارد کنید.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">پست الکترونیکی <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control ltr" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" 
                                       placeholder="example@email.com" required>
                            </div>
                            <div class="invalid-feedback">
                                لطفاً ایمیل معتبر وارد کنید.
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="current_password" class="form-label">رمز عبور فعلی <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control ltr" id="current_password" name="current_password" 
                                       placeholder="رمز عبور فعلی خود را وارد کنید" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">برای تغییر اطلاعات، رمز عبور فعلی خود را وارد کنید.</div>
                            <div class="invalid-feedback">
                                لطفاً رمز عبور فعلی را وارد کنید.
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i> به‌روزرسانی اطلاعات
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i> بازنشانی فرم
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <h6>عملیات دیگر</h6>
                        <p>می‌توانید پروفایل کاربری خود را حذف کنید.</p>
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteProfileModal">
                            <i class="bi bi-trash me-2"></i> حذف پروفایل کاربری
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal حذف پروفایل -->
<div class="modal fade" id="deleteProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">تأیید حذف پروفایل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>هشدار!</strong> این عملیات غیرقابل بازگشت است.
                </div>
                <p>آیا از حذف پروفایل کاربری خود مطمئن هستید؟</p>
                <p>تمام اطلاعات شما شامل تاریخچه سفارشات، آدرس‌ها و تنظیمات حذف خواهند شد.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <a href="action_delete_user.php" class="btn btn-danger">حذف پروفایل</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // بررسی اعتبار فرم
    const form = document.getElementById('editProfileForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
    
    // نمایش/مخفی کردن رمز عبور فعلی
    const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
    const currentPassword = document.getElementById('current_password');
    
    toggleCurrentPassword.addEventListener('click', function() {
        const type = currentPassword.getAttribute('type') === 'password' ? 'text' : 'password';
        currentPassword.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });
});
</script>

<style>
.ltr {
    direction: ltr;
    text-align: left;
}

.form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}

.form-control {
    border-left: none;
}

.form-control:focus + .input-group-text {
    border-color: #86b7fe;
}

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