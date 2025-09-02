<?php
$title = "فراموشی رمز عبور";
include('./includes/header.php');

if (isset($_SESSION["state_login"]) && $_SESSION["state_login"] === true) {
    header("Location: index.php");
    exit;
}
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">فراموشی رمز عبور</h2>
                    
                    <div id="alert-container"></div>
                    
                    <form id="forgotPasswordForm" method="post" action="action_forgot_password.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">ایمیل</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email" class="form-control" 
                                       placeholder="ایمیل خود را وارد کنید" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> ارسال لینک بازنشانی
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">
                            <i class="bi bi-arrow-right"></i> بازگشت به صفحه ورود
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#forgotPasswordForm').on('submit', function(e) {
        e.preventDefault();
        
        const submitButton = $(this).find('button[type="submit"]');
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> در حال ارسال...');
        
        $.ajax({
            type: 'POST',
            url: 'action_forgot_password.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                showAlert(response.message, response.success ? 'success' : 'danger');
                submitButton.prop('disabled', false).html('<i class="bi bi-send"></i> ارسال لینک بازنشانی');
            },
            error: function() {
                showAlert('خطایی در ارسال درخواست پیش آمد. لطفاً دوباره تلاش کنید.', 'danger');
                submitButton.prop('disabled', false).html('<i class="bi bi-send"></i> ارسال لینک بازنشانی');
            }
        });
    });
    
    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alert-container').html(alertHtml);
    }
});
</script>
<?php include('./includes/footer.php'); ?>