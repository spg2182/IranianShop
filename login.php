<?php
$title = "ورود به سایت";
include('./includes/header.php');
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// اگر کاربر قبلاً وارد شده باشد، به صفحه اصلی هدایت شود
if (isset($_SESSION["state_login"]) && $_SESSION["state_login"] === true) {
    header("Location: index.php");
    exit;
}

// نمایش پیام‌های فلش از سشن
$flashMessage = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    $flashMessage = "<div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert'>
        <i class='bi bi-" . ($flash['type'] === 'success' ? 'check-circle-fill' : ($flash['type'] === 'danger' ? 'exclamation-triangle-fill' : 'info-circle-fill')) . " me-2'></i>
        {$flash['text']}
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    unset($_SESSION['flash']);
}

// تنظیم پیام‌های وضعیت
$message = '';
$messageType = 'info';
$messageIcon = 'info-circle-fill';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'logged_out':
            $message = 'شما با موفقیت از حساب کاربری خود خارج شدید.';
            $messageType = 'success';
            $messageIcon = 'check-circle-fill';
            break;
        case 'session_expired':
            $message = 'نشست شما منقضی شده است. لطفاً دوباره وارد شوید.';
            $messageType = 'warning';
            $messageIcon = 'exclamation-triangle-fill';
            break;
        case 'registered':
            $message = 'ثبت نام شما با موفقیت انجام شد. لطفاً وارد شوید.';
            $messageType = 'success';
            $messageIcon = 'check-circle-fill';
            break;
    }
}

$statusMessage = '';
if (!empty($message)) {
    $statusMessage = "<div class='alert alert-{$messageType} alert-dismissible fade show' role='alert'>
        <i class='bi bi-{$messageIcon} me-2'></i>
        {$message}
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
}
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="bi bi-person-circle" style="font-size: 3rem; color: #6c63ff;"></i>
                        </div>
                        <h2 class="card-title fw-bold">ورود به حساب کاربری</h2>
                        <p class="text-muted">برای ادامه، لطفاً وارد حساب خود شوید</p>
                    </div>
                    
                    <?php echo $flashMessage; ?>
                    <?php echo $statusMessage; ?>
                    
                    <div id="alert-container"></div>
                    
                    <form id="loginForm" method="post" action="action_login.php" name="login" autocomplete="off">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">نام کاربری</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" id="username" name="username" class="form-control ltr" 
                                       placeholder="نام کاربری خود را وارد کنید" required>
                            </div>
                            <div class="form-text text-muted">فقط حروف لاتین و اعداد</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">کلمه عبور</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password" class="form-control ltr" 
                                       placeholder="کلمه عبور خود را وارد کنید" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted">حداقل ۸ کاراکتر شامل اعداد، علائم و حروف لاتین</div>
                        </div>
                        
                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                            <label class="form-check-label" for="rememberMe">مرا به خاطر بسپار</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2" id="loginButton">
                                <i class="bi bi-box-arrow-in-right me-2"></i> ورود
                            </button>
                            <button type="reset" class="btn btn-outline-secondary py-2">
                                <i class="bi bi-arrow-clockwise me-2"></i> پاک کردن فرم
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <a href="forgot_password.php" class="text-decoration-none d-block mb-2">
                            <i class="bi bi-question-circle"></i> فراموشی رمز عبور
                        </a>
                        <div>
                            <span class="text-muted">حساب کاربری ندارید؟</span>
                            <a href="register.php" class="text-decoration-none fw-semibold">
                                <i class="bi bi-person-plus"></i> ثبت نام
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script>
$(document).ready(function() {
    // نمایش/مخفی کردن رمز عبور
    $('#togglePassword').on('click', function() {
        const passwordField = $('#password');
        const passwordType = passwordField.attr('type');
        const toggleIcon = $(this).find('i');
        
        if (passwordType === 'password') {
            passwordField.attr('type', 'text');
            toggleIcon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            toggleIcon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });
    
    // ارسال فرم با AJAX
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        // غیرفعال کردن دکمه ارسال برای جلوگیری از ارسال‌های متعدد
        const submitButton = $('#loginButton');
        const originalText = submitButton.html();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> در حال ورود...');
        
        $.ajax({
            type: 'POST',
            url: 'action_login.php',
            data: $(this).serialize(),
            dataType: 'json',
            cache: false,
            success: function(response) {
                console.log('Success Response:', response);
                
                if (response.success) {
                    // نمایش پیام موفقیت
                    showAlert('ورود با موفقیت انجام شد. در حال انتقال به صفحه اصلی...', 'success');
                    
                    // هدایت به صفحه اصلی بعد از 1 ثانیه
                    setTimeout(function() {
                        window.location.href = response.redirect || 'index.php';
                    }, 1000);
                } else {
                    showAlert(response.message || 'خطا در ورود', 'danger');
                    submitButton.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.log('XHR Status:', xhr.status);
                console.log('Response Text:', xhr.responseText);
                
                let errorMessage = 'خطایی در ارسال درخواست پیش آمد. لطفاً دوباره تلاش کنید.';
                
                // اگر پاسخ JSON باشد، آن را تجزیه کن
                if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        // اگر پاسخ JSON نباشد، بررسی کن که آیا کاربر وارد شده است
                        if (xhr.responseText.includes('index.php') || xhr.status === 200) {
                            // ممکن است ورود موفقیت‌آمیز بوده باشد
                            showAlert('ورود با موفقیت انجام شد. در حال انتقال به صفحه اصلی...', 'success');
                            setTimeout(function() {
                                window.location.href = 'index.php';
                            }, 1000);
                            return;
                        }
                    }
                }
                
                showAlert(errorMessage, 'danger');
                submitButton.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // تابع برای نمایش پیام‌ها
    function showAlert(message, type) {
        const iconClass = type === 'danger' ? 'exclamation-triangle-fill' : 
                         type === 'success' ? 'check-circle-fill' : 
                         type === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill';
                         
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="bi bi-${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
        `;
        $('#alert-container').html(alertHtml);
        
        // حذف خودکار پیام بعد از 5 ثانیه (برای پیام‌های خطا)
        if (type === 'danger' || type === 'warning') {
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    }
});
</script>
<?php include('./includes/footer.php'); ?>