<?php
ob_start();
$title = "تسویه حساب";
include('includes/header.php');
include('includes/functions.php');
include('includes/db_link.php');
require_login(); // اطمینان از ورود کاربر
$user_id = $_SESSION['user_id'];

// دریافت آیتم‌های سبد خرید
$stmt = $link->prepare("SELECT c.*, p.name, p.price, p.image, p.qty as stock 
                        FROM cart c 
                        JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// اگر سبد خرید خالی است، هدایت به صفحه سبد خرید
if ($cart_items->num_rows === 0) {
    header('Location: cart.php');
    exit;
}

// محاسبه مجموع قیمت
$total = 0;
$cart_data = [];
while ($item = $cart_items->fetch_assoc()) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $cart_data[] = $item;
}

// دریافت اطلاعات کاربر
$stmt = $link->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// دریافت استان‌ها
$provinces_result = $link->query("SELECT id, name FROM provinces ORDER BY name");
$provinces = [];
while ($row = $provinces_result->fetch_assoc()) {
    $provinces[] = $row;
}

// پردازش فرم تسویه حساب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = isset($_POST['number']) ? trim($_POST['number']) : '';
    $province = filter_input(INPUT_POST, 'province', FILTER_VALIDATE_INT);
    $city = filter_input(INPUT_POST, 'city', FILTER_VALIDATE_INT);
    $postal_code = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    $crypto_type = isset($_POST['crypto_type']) ? $_POST['crypto_type'] : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    
    if (empty($number) || empty($province) || empty($city) || empty($postal_code) || empty($address) || empty($payment_method)) {
        $error = 'لطفاً تمام فیلدهای الزامی را پر کنید.';
    } elseif (!preg_match('/^\d{10}$/', $postal_code)) {
        $error = 'کد پستی باید 10 رقم باشد.';
    } else {
        // شروع تراکنش
        $link->begin_transaction();
        
        try {
            // دریافت نام استان و شهر
            $stmt = $link->prepare("SELECT name FROM provinces WHERE id = ?");
            $stmt->bind_param("i", $province);
            $stmt->execute();
            $province_result = $stmt->get_result();
            $province_name = $province_result->fetch_assoc()['name'];
            
            $stmt = $link->prepare("SELECT name FROM cities WHERE id = ?");
            $stmt->bind_param("i", $city);
            $stmt->execute();
            $city_result = $stmt->get_result();
            $city_name = $city_result->fetch_assoc()['name'];
            
            // ایجاد سفارش جدید
            //$track_code = 'TRK' . time() . rand(1000, 9999);  تولید تصادفی کد رهگیری
            // ایجاد سفارش جدید (بدون تولید کد رهگیری)
            $stmt = $link->prepare("INSERT INTO orders (user_id, username, orderdate, number, address, province, city, postal_code, trackcode, status, payment_method, crypto_type, notes) 
                       VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, NULL, 'pending', ?, ?, ?)");
            $username = $_SESSION['username'];
            $stmt->bind_param("isssssssss", $user_id, $username, $number, $address, $province_name, $city_name, $postal_code, $payment_method, $crypto_type, $notes);
            $stmt->execute();
            $order_id = $link->insert_id;

            // افزودن آیتم‌های سفارش
            foreach ($cart_data as $item) {
                $stmt = $link->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $stmt->execute();
                
                // به‌روزرسانی موجودی محصول
                $stmt = $link->prepare("UPDATE products SET qty = qty - ? WHERE id = ?");
                $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmt->execute();
            }
            
            // خالی کردن سبد خرید
            $stmt = $link->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            $link->commit();
            
            // هدایت به صفحه تأیید سفارش
            header("Location: order_confirmation.php?order_id=$order_id");
            exit;
        } catch (Exception $e) {
            $link->rollback();
            $error = 'خطا در ثبت سفارش: ' . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4">تسویه حساب</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8">
            <form method="post" id="checkoutForm" class="needs-validation" novalidate>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">اطلاعات ارسال</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="province" class="form-label">استان <span class="text-danger">*</span></label>
                                <select class="form-select" id="province" name="province" required>
                                    <option value="">انتخاب استان</option>
                                    <?php foreach ($provinces as $province): ?>
                                        <option value="<?= $province['id'] ?>" 
                                                <?= (isset($_POST['province']) && $_POST['province'] == $province['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($province['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    لطفاً استان را انتخاب کنید
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">شهر <span class="text-danger">*</span></label>
                                <select class="form-select" id="city" name="city" required>
                                    <option value="">ابتدا استان را انتخاب کنید</option>
                                </select>
                                <div class="invalid-feedback">
                                    لطفاً شهر را انتخاب کنید
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">کد پستی <span class="text-danger">*</span></label>
                                <input type="text" class="form-control ltr" id="postal_code" name="postal_code" 
                                       value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>" 
                                       maxlength="10" pattern="\d{10}" required>
                                <div class="invalid-feedback">
                                    کد پستی باید 10 رقم باشد
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="number" class="form-label">شماره تماس <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control ltr" id="number" name="number" 
                                       value="<?= htmlspecialchars($user['phone'] ?? $_POST['number'] ?? '') ?>" 
                                       pattern="09[0-9]{9}" required>
                                <div class="invalid-feedback">
                                    شماره تماس نامعتبر است
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">آدرس دقیق <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($_POST['address'] ?? $user['address'] ?? '') ?></textarea>
                            <div class="invalid-feedback">
                                لطفاً آدرس را وارد کنید
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">توضیحات额外</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="هر توضیح اضافی برای سفارش خود اینجا بنویسید"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">روش پرداخت</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked required>
                            <label class="form-check-label" for="payment_cod">
                                پرداخت در محل (COD)
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_online" value="online">
                            <label class="form-check-label" for="payment_online">
                                پرداخت آنلاین (درگاه بانکی)
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment_crypto" value="crypto">
                            <label class="form-check-label" for="payment_crypto">
                                پرداخت با ارز دیجیتال
                            </label>
                        </div>
                        
                        <div id="crypto_options" style="display: none;">
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">نوع ارز دیجیتال</label>
                                <select class="form-select" id="crypto_type" name="crypto_type">
                                    <option value="USDT">تتر (USDT)</option>
                                    <option value="ETH">اتریوم (ETH)</option>
                                    <option value="BTC">بیتکوین (BTC)</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6>دستورال پرداخت با ارز دیجیتال:</h6>
                                <ol>
                                    <li>نوع ارز دیجیتال مورد نظر خود را انتخاب کنید</li>
                                    <li>مبلغ <?= toman($total, true) ?> را به آدرس زیر ارسال کنید</li>
                                    <li>کد پیگیری تراکنش را در قسمت توضیحات وارد کنید</li>
                                    <li>پس از تایید پرداخت، سفارش شما ثبت خواهد شد</li>
                                </ol>
                                
                                <div class="mt-3">
                                    <h6>آدرس کیف پول:</h6>
                                    <div class="input-group">
                                        <input type="text" class="form-control ltr" id="wallet_address" readonly 
                                               value="0x1234567890abcdef1234567890abcdef12345678">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyWalletAddress()">
                                            <i class="bi bi-clipboard"></i> کپی
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="cart.php" class="btn btn-outline-secondary me-md-2">بازگشت به سبد خرید</a>
                    <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                        <i class="bi bi-credit-card"></i> ثبت سفارش
                    </button>
                </div>
            </form>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">خلاصه سفارش</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>محصول</th>
                                    <th>تعداد</th>
                                    <th>قیمت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_data as $item): 
                                    $subtotal = $item['price'] * $item['quantity'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= toman($subtotal, true) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2"><strong>جمع کل:</strong></td>
                                    <td><strong><?= toman($total, true) ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <h6>هزینه ارسال</h6>
                        <p class="mb-0">هزینه ارسال بر اساس آدرس شما محاسبه خواهد شد.</p>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <h5>مبلغ قابل پرداخت:</h5>
                        <h5 class="text-success"><?= toman($total, true) ?></h5>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">اطلاعات امنیتی</h6>
                    <p class="card-text small text-muted">
                        تمام اطلاعات شما به صورت امن رمزنگری می‌شوند و ما از اطلاعات شما محافظت می‌کنیم.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    const submitBtn = document.getElementById('submitBtn');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const cryptoOptions = document.getElementById('crypto_options');
    const paymentCrypto = document.getElementById('payment_crypto');
    
    // بارگذاری شهرها بر اساس استان انتخاب شده
    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;
        citySelect.innerHTML = '<option value="">در حال بارگذاری...</option>';
        citySelect.disabled = true;
        
        if (provinceId) {
            fetch('get_cities.php?province_id=' + provinceId)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    citySelect.innerHTML = '<option value="">انتخاب شهر</option>';
                    if (Array.isArray(data)) {
                        data.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.id;
                            option.textContent = city.name;
                            // Check if this city was previously selected
                            <?php if (isset($_POST['city'])): ?>
                                if (city.id == <?= $_POST['city'] ?>) {
                                    option.selected = true;
                                }
                            <?php endif; ?>
                            citySelect.appendChild(option);
                        });
                    }
                    citySelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    citySelect.innerHTML = '<option value="">خطا در بارگذاری شهرها</option>';
                });
        } else {
            citySelect.innerHTML = '<option value="">ابتدا استان را انتخاب کنید</option>';
            citySelect.disabled = true;
        }
    });
    
    // اگر از قبل استان انتخاب شده باشد، شهرها را بارگذاری کن
    if (provinceSelect.value) {
        provinceSelect.dispatchEvent(new Event('change'));
    }
    
    // مدیریت نمایش گزینه‌های ارز دیجیتال
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'crypto') {
                cryptoOptions.style.display = 'block';
                document.getElementById('crypto_type').required = true;
            } else {
                cryptoOptions.style.display = 'none';
                document.getElementById('crypto_type').required = false;
            }
        });
    });
    
    // اگر از قبل ارز دیجیتال انتخاب شده باشد
    if (paymentCrypto && paymentCrypto.checked) {
        cryptoOptions.style.display = 'block';
        document.getElementById('crypto_type').required = true;
    }
    
    // کپی آدرس کیف پول
    window.copyWalletAddress = function() {
        const walletAddress = document.getElementById('wallet_address');
        walletAddress.select();
        document.execCommand('copy');
        
        // نمایش پیام موفقیت
        const originalText = event.target.innerHTML;
        event.target.innerHTML = '<i class="bi bi-check"></i> کپی شد';
        setTimeout(() => {
            event.target.innerHTML = originalText;
        }, 2000);
    };
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // غیرفعال کردن دکمه ارسال
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>در حال ثبت سفارش...';
        
        // اعتبارسنجی فرم
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            
            // فعال کردن دکمه ارسال
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-credit-card"></i> ثبت سفارش';
            return;
        }
        
        // ارسال فرم
        form.submit();
    });
    
    // اعتبارسنجی شماره تماس
    const phoneInput = document.getElementById('number');
    phoneInput.addEventListener('input', function() {
        const phonePattern = /^09[0-9]{9}$/;
        if (phonePattern.test(this.value)) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity('شماره تماس نامعتبر است');
        }
    });
    
    phoneInput.addEventListener('invalid', function() {
        if (this.value === '') {
            this.setCustomValidity('لطفاً شماره تماس را وارد کنید');
        } else {
            this.setCustomValidity('شماره تماس نامعتبر است');
        }
    });
    
    // اعتبارسنجی کد پستی
    const postalCodeInput = document.getElementById('postal_code');
    postalCodeInput.addEventListener('input', function() {
        const postalCodePattern = /^\d{10}$/;
        if (postalCodePattern.test(this.value)) {
            this.setCustomValidity('');
        } else {
            this.setCustomValidity('کد پستی نامعتبر است');
        }
    });
    
    postalCodeInput.addEventListener('invalid', function() {
        if (this.value === '') {
            this.setCustomValidity('لطفاً کد پستی را وارد کنید');
        } else {
            this.setCustomValidity('کد پستی نامعتبر است');
        }
    });
});
</script>

<?php include('includes/footer.php'); ?>