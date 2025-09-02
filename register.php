<?php
$title = "عضویت در سایت";
include('./includes/header.php');
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// دریافت پیام‌های خطا و داده‌های فرم از سشن
$error_messages = $_SESSION['error_messages'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['error_messages'], $_SESSION['form_data']);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus-fill me-2"></i> عضویت در فروشگاه ایرانیان</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($error_messages)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>خطا!</strong> لطفاً موارد زیر را بررسی کنید:
                            <ul class="mb-0 mt-2">
                                <?php foreach ($error_messages as $field => $message): ?>
                                    <li><?= htmlspecialchars($message) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="action_register.php" id="registerForm" novalidate>
                        <div class="mb-3">
                            <label for="realname" class="form-label">نام واقعی <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="realname" name="realname" 
                                    value="<?= htmlspecialchars($form_data['realname'] ?? '') ?>" 
                                    placeholder="نام و نام خانوادگی خود را وارد کنید" required>
                            </div>
                            <div class="invalid-feedback">
                                لطفاً نام واقعی خود را وارد کنید.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">نام کاربری <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                <input type="text" class="form-control ltr" id="username" name="username" 
                                    value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" 
                                    placeholder="نام کاربری (فقط حروف لاتین)" required>
                            </div>
                            <div id="username-availability-status" class="form-text mt-1"></div>
                            <div class="invalid-feedback">
                                لطفاً نام کاربری معتبر وارد کنید.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">کلمه عبور <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control ltr" id="password" name="password" 
                                    placeholder="کلمه عبور خود را وارد کنید" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text mt-1">
                                <div class="progress" style="height: 5px;">
                                    <div id="password-strength" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="password-strength-text" class="text-muted"></small>
                            </div>
                            <div class="invalid-feedback">
                                لطفاً کلمه عبور معتبر وارد کنید.
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="repassword" class="form-label">تکرار کلمه عبور <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control ltr" id="repassword" name="repassword" 
                                    placeholder="کلمه عبور را تکرار کنید" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleRepassword">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="password-match" class="form-text mt-1"></div>
                            <div class="invalid-feedback">
                                تکرار کلمه عبور صحیح نیست.
                            </div>
                        </div>
                        
                        <div class="mb-4">
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
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                            <label class="form-check-label" for="agreeTerms">
                                <a href="terms.php" target="_blank">قوانین و مقررات</a> سایت را می‌پذیرم <span class="text-danger">*</span>
                            </label>
                            <div class="invalid-feedback">
                                برای ثبت نام باید قوانین سایت را بپذیرید.
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-check me-2"></i> ثبت نام
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise me-2"></i> پاک کردن فرم
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p>قبلاً ثبت نام کرده‌اید؟ <a href="login.php" class="text-primary">ورود به سایت</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // بررسی اعتبار فرم
    const form = document.getElementById('registerForm');
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
    
    const toggleRepassword = document.getElementById('toggleRepassword');
    const repassword = document.getElementById('repassword');
    
    toggleRepassword.addEventListener('click', function() {
        const type = repassword.getAttribute('type') === 'password' ? 'text' : 'password';
        repassword.setAttribute('type', type);
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
    
    // بررسی تطابق رمز عبور
    const repasswordInput = document.getElementById('repassword');
    const passwordMatch = document.getElementById('password-match');
    
    repasswordInput.addEventListener('input', function() {
        if (this.value === passwordInput.value) {
            passwordMatch.textContent = '✓ رمز عبور مطابقت دارد';
            passwordMatch.className = 'form-text text-success';
        } else {
            passwordMatch.textContent = '✗ رمز عبور مطابقت ندارد';
            passwordMatch.className = 'form-text text-danger';
        }
    });
    
    // بررسی موجود بودن نام کاربری
    const usernameInput = document.getElementById('username');
    const usernameStatus = document.getElementById('username-availability-status');
    
    usernameInput.addEventListener('blur', function() {
        const username = this.value.trim();
        if (username.length > 0) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'check_username.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    usernameStatus.innerHTML = xhr.responseText;
                }
            };
            xhr.send('username=' + encodeURIComponent(username));
        } else {
            usernameStatus.innerHTML = '';
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

/* انیمیشن برای کارت */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.5s ease forwards;
}
</style>

<?php
include('./includes/footer.php');
?>