<?php
// شروع سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// include کردن فایل توابع که شامل تابع require_admin است
include_once('./includes/functions.php');

// بررسی دسترسی مدیر (هم ورود کاربر و هم سطح دسترسی را بررسی می‌کند)
require_admin();

// اگر فرم ارسال شده است، پردازش را انجام بده
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error_messages = [];
    
    // دریافت و اعتبارسنجی داده‌ها
    $realname = trim($_POST['realname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $user_type = isset($_POST['user_type']) ? (int)$_POST['user_type'] : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // اعتبارسنجی فیلدها
    if (empty($realname)) {
        $error_messages['realname'] = "نام واقعی را وارد کنید.";
    } elseif (!preg_match('/^[\x{0600}-\x{06FF}\s]+$/u', $realname)) {
        $error_messages['realname'] = "نام واقعی باید فقط شامل حروف فارسی باشد.";
    }
    
    if (empty($username)) {
        $error_messages['username'] = "نام کاربری را وارد کنید.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_messages['username'] = "نام کاربری باید فقط شامل حروف لاتین، اعداد و خط زیر باشد.";
    }
    
    if (empty($password)) {
        $error_messages['password'] = "کلمه عبور را وارد کنید.";
    } elseif (strlen($password) < 8) {
        $error_messages['password'] = "کلمه عبور باید حداقل ۸ کاراکتر باشد.";
    }
    
    if (empty($email)) {
        $error_messages['email'] = "پست الکترونیکی را وارد کنید.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_messages['email'] = "پست الکترونیکی معتبر نیست.";
    }
    
    // اگر خطایی وجود نداشت
    if (empty($error_messages)) {
        try {
            $conn = new PDO("mysql:host=localhost;dbname=iranianshop", "root", "");
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // بررسی تکراری نبودن نام کاربری
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error_messages['username'] = "نام کاربری قبلاً ثبت شده است.";
            } else {
                // بررسی تکراری نبودن ایمیل
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $error_messages['email'] = "پست الکترونیکی قبلاً ثبت شده است.";
                } else {
                    // هش کردن رمز عبور
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // افزودن کاربر جدید
                    $stmt = $conn->prepare("INSERT INTO users (username, password, realname, email, user_type, is_active, created_at) 
                                          VALUES (:username, :password, :realname, :email, :user_type, :is_active, NOW())");
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':realname', $realname);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':user_type', $user_type);
                    $stmt->bindParam(':is_active', $is_active);
                    
                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "کاربر جدید با موفقیت افزوده شد.";
                        header("Location: admin_users.php");
                        exit();
                    } else {
                        $error_messages[] = "خطا در افزودن کاربر.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error_messages[] = "خطای پایگاه داده: " . $e->getMessage();
        }
    }
    
    // اگر خطا وجود داشت، آن را در سشن ذخیره کن و به صفحه خود هدایت کن
    if (!empty($error_messages)) {
        $_SESSION['error_messages'] = $error_messages;
        $_SESSION['form_data'] = $_POST;
        header("Location: admin_add_user.php");
        exit();
    }
}

// اگر به اینجا رسیدیم یعنی فرم ارسال نشده است یا خطا وجود داشته که به همین صفحه برگشته‌ایم
$title = "افزودن کاربر جدید";
include('./includes/header.php');

// اگر از صفحه‌ای با خطا برگشته‌ایم، داده‌های فرم را از سشن بخوان
$form_data = $_SESSION['form_data'] ?? [];
$error_messages = $_SESSION['error_messages'] ?? [];
unset($_SESSION['form_data'], $_SESSION['error_messages']);
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-plus-fill me-2"></i> افزودن کاربر جدید</h2>
        <a href="admin_users.php" class="btn btn-secondary">
            <i class="bi bi-arrow-right-circle me-1"></i> بازگشت به لیست کاربران
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">فرم افزودن کاربر</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_messages)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>خطا!</strong> لطفاً موارد زیر را بررسی کنید:
                            <ul class="mb-0 mt-2">
                                <?php foreach ($error_messages as $message): ?>
                                    <li><?= htmlspecialchars($message) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="" id="addUserForm" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="realname" class="form-label">نام واقعی <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="realname" name="realname" 
                                           value="<?= htmlspecialchars($form_data['realname'] ?? '') ?>" 
                                           placeholder="نام و نام خانوادگی" required>
                                </div>
                                <div class="invalid-feedback">
                                    لطفاً نام واقعی را وارد کنید.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">نام کاربری <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-at"></i></span>
                                    <input type="text" class="form-control ltr" id="username" name="username" 
                                           value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" 
                                           placeholder="نام کاربری (فقط لاتین)" required>
                                </div>
                                <div class="invalid-feedback">
                                    لطفاً نام کاربری معتبر وارد کنید.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">کلمه عبور <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control ltr" id="password" name="password" 
                                           placeholder="کلمه عبور" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <div class="progress" style="height: 5px;">
                                        <div id="password-strength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small id="password-strength-text" class="text-muted"></small>
                                </div>
                                <div class="invalid-feedback">
                                    لطفاً کلمه عبور معتبر وارد کنید.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">پست الکترونیکی <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control ltr" id="email" name="email" 
                                           value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" 
                                           placeholder="example@email.com" required>
                                </div>
                                <div class="invalid-feedback">
                                    لطفاً ایمیل معتبر وارد کنید.
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">نوع کاربر <span class="text-danger">*</span></label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="user_type" id="user_type" value="1" <?= (isset($form_data['user_type']) && $form_data['user_type'] == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="user_type">
                                        مدیر سیستم
                                    </label>
                                </div>
                                <small class="text-muted">کاربر عادی: <span class="badge bg-info">عادی</span> | مدیر سیستم: <span class="badge bg-warning text-dark">مدیر</span></small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">وضعیت کاربر <span class="text-danger">*</span></label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?= (!isset($form_data['is_active']) || $form_data['is_active'] == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">
                                        فعال
                                    </label>
                                </div>
                                <small class="text-muted">کاربر فعال: <span class="badge bg-success">فعال</span> | کاربر غیرفعال: <span class="badge bg-danger">غیرفعال</span></small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="admin_users.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> انصراف
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> افزودن کاربر
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">راهنمای افزودن کاربر</h5>
                </div>
                <div class="card-body">
                    <h6><i class="bi bi-info-circle me-2"></i> نکات مهم:</h6>
                    <ul>
                        <li>نام کاربری باید فقط شامل حروف لاتین، اعداد و خط زیر باشد.</li>
                        <li>کلمه عبور باید حداقل ۸ کاراکتر باشد.</li>
                        <li>ایمیل باید معتبر و قبلاً ثبت نشده باشد.</li>
                        <li>کاربران مدیر به تمام بخش‌های مدیریت دسترسی دارند.</li>
                        <li>کاربران غیرفعال نمی‌توانند وارد سیستم شوند.</li>
                    </ul>
                    
                    <h6 class="mt-3"><i class="bi bi-shield-check me-2"></i> امنیت:</h6>
                    <p>تمامی رمز عبورها با استفاده از الگوریتم bcrypt هش می‌شوند.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // بررسی اعتبار فرم
    const form = document.getElementById('addUserForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
    
    // نمایش/مخفی کردن رمز عبور
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });
    
    // بررسی قدرت رمز عبور
    const passwordInput = document.getElementById('password');
    const passwordStrength = document.getElementById('password-strength');
    const passwordStrengthText = document.getElementById('password-strength-text');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]+/)) strength += 25;
        if (password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9]+/)) strength += 25;
        
        passwordStrength.style.width = strength + '%';
        
        if (strength <= 25) {
            passwordStrength.className = 'progress-bar bg-danger';
            passwordStrengthText.textContent = 'ضعیف';
            passwordStrengthText.className = 'text-danger';
        } else if (strength <= 50) {
            passwordStrength.className = 'progress-bar bg-warning';
            passwordStrengthText.textContent = 'متوسط';
            passwordStrengthText.className = 'text-warning';
        } else if (strength <= 75) {
            passwordStrength.className = 'progress-bar bg-info';
            passwordStrengthText.textContent = 'خوب';
            passwordStrengthText.className = 'text-info';
        } else {
            passwordStrength.className = 'progress-bar bg-success';
            passwordStrengthText.textContent = 'بسیار خوب';
            passwordStrengthText.className = 'text-success';
        }
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

.progress {
    height: 5px !important;
    margin-top: 8px;
}
</style>

<?php include('./includes/footer.php'); ?>