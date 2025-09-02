<?php
$title = "اطلاعات کالا";
ob_start(); // شروع بافر کردن خروجی
include('includes/header.php');
include('includes/db_link.php');

// اعتبارسنجی و امن‌سازی ورودی
$id = 0;
$product_found = false;
$alert_message = '';
$alert_type = '';
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    setcookie("last-viewed-item", $id, time() + 3600 * 24 * 30, "/"); // کوکی ۳۰ روز با مسیر کامل
    $query = "SELECT * FROM products WHERE id = ?";
} elseif (isset($_COOKIE["last-viewed-item"]) && is_numeric($_COOKIE["last-viewed-item"])) {
    $id = (int)$_COOKIE["last-viewed-item"];
    $query = "SELECT * FROM products WHERE id = ?";
    $alert_message = 'شما در حال مشاهده آخرین کالایی که بازدید کردید هستید.';
    $alert_type = 'info';
} else {
    $alert_message = 'خطایی در دریافت اطلاعات کالا رخ داده است.';
    $alert_type = 'danger';
}
// اجرای کوئری با استفاده از Prepared Statements
if (!empty($query)) {
    $stmt = mysqli_prepare($link, $query);
    if ($stmt === false) {
        $alert_message = 'خطا در آماده‌سازی پرس‌وجو: ' . mysqli_error($link);
        $alert_type = 'danger';
    } else {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $product_found = true;
            
            // دریافت محصولات مرتبط (بر اساس دسته‌بندی یا تصادفی)
            $related_query = "SELECT id, name, image, price FROM products WHERE id != ? ORDER BY RAND() LIMIT 4";
            $related_stmt = mysqli_prepare($link, $related_query);
            mysqli_stmt_bind_param($related_stmt, "i", $id);
            mysqli_stmt_execute($related_stmt);
            $related_result = mysqli_stmt_get_result($related_stmt);
        } else {
            $alert_message = 'کالایی با این مشخصات یافت نشد.';
            $alert_type = 'warning';
        }
        mysqli_stmt_close($stmt);
    }
}
// اگر نیاز به هدایت دارید، قبل از هر خروجی دیگری انجام دهید
if (!empty($alert_message) && ($alert_type == 'danger' || $alert_type == 'warning')) {
    ob_end_clean(); // پاک کردن بافر
    header("Location: index.php");
    exit;
}
// نمایش هشدار در صورت وجود
if (!empty($alert_message)) {
    echo "<div class='container mt-4'><div class='alert alert-{$alert_type} text-center'>{$alert_message}</div></div>";
}
// نمایش اطلاعات محصول در صورت وجود
if ($product_found):
    // تعیین وضعیت موجودی
    $stock_status = $row['qty'] > 0 ? 'موجود' : 'ناموجود';
    $stock_class = $row['qty'] > 0 ? 'text-success' : 'text-danger';
    $btn_class = $row['qty'] > 0 ? 'btn-primary' : 'btn-secondary disabled';
    $btn_text = $row['qty'] > 0 ? 'سفارش این کالا' : 'ناموجود';
    
    // استفاده از تابع کمکی برای دریافت مسیر تصویر
    $image_path = getProductImage($row['image']);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="product-image-container border rounded overflow-hidden shadow-sm bg-light d-flex justify-content-center align-items-center" style="height: 400px;">
                <img src="<?= $image_path ?>"
                     alt="<?= htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8') ?>"
                     class="img-fluid"
                     style="max-height: 100%; max-width: 100%; object-fit: contain;"
                     onerror="this.src='/assets/images/no-image.png'">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="product-details">
                <h2 class="mb-3"><?= htmlspecialchars($row["name"], ENT_QUOTES, 'UTF-8') ?></h2>
                
                <div class="product-info mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="product-price me-3 fs-4 fw-bold"><?= toman($row["price"], true) ?></span>
                        <span class="badge bg-<?= $row['qty'] > 0 ? 'success' : 'danger' ?> fs-6"><?= $stock_status ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <span class="fw-bold">موجودی انبار:</span>
                        <span class="<?= $stock_class ?> me-2"><?= (int)$row["qty"] ?> عدد</span>
                    </div>
                    
                    <?php if (!empty($row['product_code'])): ?>
                    <div class="mb-4">
                        <span class="fw-bold">کد کالا:</span>
                        <span class="me-2"><?= htmlspecialchars($row["product_code"], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-description mb-4">
                    <h5 class="fw-bold mb-2">توضیحات محصول:</h5>
                    <div class="border rounded p-3 bg-light">
                        <?= nl2br(htmlspecialchars($row["details"], ENT_QUOTES, 'UTF-8')) ?>
                    </div>
                </div>
                
                <div class="product-actions">
                    <!-- دکمه افزودن به سبد خرید -->
                    <?php if (isset($_SESSION['state_login']) && $_SESSION['state_login'] === true): ?>
                        <?php if ($row['qty'] > 0): ?>
                            <button class="btn btn-success btn-lg add-to-cart" 
                                    data-product-id="<?= $row['id'] ?>" 
                                    data-product-name="<?= htmlspecialchars($row['name']) ?>">
                                <i class="bi bi-cart-plus"></i> افزودن به سبد خرید
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="bi bi-cart-plus"></i> ناموجود
                            </button>
                        <?php endif; ?>
                        
                        <!-- دکمه افزودن به علاقه‌مندی‌ها -->
                        <button class="btn btn-outline-danger btn-lg add-to-wishlist" 
                                data-product-id="<?= $row['id'] ?>">
                            <i class="bi bi-heart"></i> علاقه‌مندی‌ها
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> برای خرید وارد شوید
                        </a>
                    <?php endif; ?>
                    
                    <button class="btn btn-outline-secondary btn-lg ms-2" onclick="window.history.back()">
                        <i class="bi bi-arrow-right"></i> بازگشت
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- محصولات مرتبط -->
    <?php if ($related_result && mysqli_num_rows($related_result) > 0): ?>
    <div class="related-products mt-5">
        <h3 class="text-center mb-4">محصولات مرتبط</h3>
        <div class="row">
            <?php while ($related_row = mysqli_fetch_assoc($related_result)): ?>
                <?php 
                // استفاده از تابع کمکی برای محصولات مرتبط
                $related_image_path = getProductImage($related_row['image']);
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100 product-card shadow-sm">
                        <div class="product-image-container" style="height: 200px;">
                            <a href="product.php?id=<?= (int)$related_row['id'] ?>">
                                <img src="<?= $related_image_path ?>" 
                                     class="card-img-top product-image w-100 h-100" 
                                     alt="<?= htmlspecialchars($related_row['name']) ?>"
                                     style="object-fit: cover;"
                                     onerror="this.src='/assets/images/no-image.png'">
                            </a>
                        </div>
                        <div class="card-body">
                            <h5 class="product-title"><?= htmlspecialchars($related_row['name']) ?></h5>
                            <p class="product-price"><?= toman($related_row['price'], true) ?></p>
                            <a href="product.php?id=<?= (int)$related_row['id'] ?>" class="btn btn-sm btn-outline-primary w-100">مشاهده محصول</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (isset($related_stmt)) mysqli_stmt_close($related_stmt); ?>
</div>
<?php 
endif;
include('includes/footer.php');
?>

<!-- کد جاوا اسکریپت برای مدیریت دکمه‌ها -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // مدیریت دکمه‌های افزودن به سبد خرید
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName || 'محصول';
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', 'add');
            formData.append('quantity', 1);
            
            fetch('action_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('خطا در ارتباط با سرور');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // به‌روزرسانی شمارنده سبد خرید در هدر
                    const cartBadge = document.querySelector('.navbar .badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                        if (data.cart_count == 0) {
                            cartBadge.style.display = 'none';
                        } else {
                            cartBadge.style.display = 'block';
                        }
                    }
                    
                    // استفاده از تابع showToast از main.js
                    if (typeof window.showToast === 'function') {
                        window.showToast(`${productName} به سبد خرید اضافه شد`, 'success');
                    } else {
                        alert(`${productName} به سبد خرید اضافه شد`);
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message, 'danger');
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('خطا در ارتباط با سرور', 'danger');
                } else {
                    alert('خطا در ارتباط با سرور');
                }
            });
        });
    });
    
    // مدیریت دکمه‌های افزودن به علاقه‌مندی‌ها
    document.querySelectorAll('.add-to-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', 'add');
            
            fetch('action_wishlist.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('خطا در ارتباط با سرور');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // تغییر آیکون دکمه
                    this.innerHTML = '<i class="bi bi-heart-fill"></i>';
                    this.classList.add('text-danger');
                    
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message, 'success');
                    } else {
                        alert(data.message);
                    }
                } else {
                    if (typeof window.showToast === 'function') {
                        window.showToast(data.message, 'info');
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('خطا در ارتباط با سرور', 'danger');
                } else {
                    alert('خطا در ارتباط با سرور');
                }
            });
        });
    });
});
</script>

<?php ob_end_flush(); // پایان بافر کردن و ارسال خروجی ?>