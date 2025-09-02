<?php
$title = "صفحه اصلی";
include('./includes/header.php');
include('./includes/db_link.php');
// پیکربندی صفحه‌بندی
$items_per_page = 6; // تغییر از 12 به 6 برای نمایش بهتر
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
// استفاده از Prepared Statements برای جلوگیری از SQL Injection
$query = "SELECT id, name, image, price, qty, details FROM products ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $link->prepare($query);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$products = $stmt->get_result();
// دریافت تعداد کل محصولات برای صفحه‌بندی
$count_query = "SELECT COUNT(*) as total FROM products";
$count_result = $link->query($count_query);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $items_per_page);
?>
<div class="container mt-4">
    <!-- بخش معرفی سایت -->
    <div class="row mb-5 animate-on-scroll">
        <div class="col-12">
            <div class="jumbotron bg-light p-5 rounded-3 shadow-sm">
                <div class="text-center">
                    <h1 class="display-4 fw-bold mb-3">به <?php echo htmlspecialchars($siteTitle); ?> خوش آمدید</h1>
                    <p class="lead mb-4">بهترین محصولات با کیفیت عالی و قیمت مناسب</p>
                    <p class="mb-4">ما با ارائه محصولات باکیفیت و خدمات پس از فروش عالی، تلاش می‌کنیم تجربه‌ای متفاوت از خرید آنلاین را برای شما فراهم کنیم.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="product.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-bag me-2"></i> مشاهده همه محصولات
                        </a>
                        <a href="about.php" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-info-circle me-2"></i> درباره ما
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- بخش ویژگی‌ها -->
    <div class="row mb-5">
        <div class="col-md-4 mb-4 animate-on-scroll">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-3">
                        <i class="bi bi-truck fs-1"></i>
                    </div>
                    <h5 class="card-title">ارسال سریع</h5>
                    <p class="card-text">ارسال محصولات به سراسر کشور در کمترین زمان ممکن</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4 animate-on-scroll">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-3">
                        <i class="bi bi-shield-check fs-1"></i>
                    </div>
                    <h5 class="card-title">پرداخت امن</h5>
                    <p class="card-text">امکان پرداخت آنلاین با امنیت بالا و درگاه‌های معتبر</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4 animate-on-scroll">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-primary mb-3">
                        <i class="bi bi-arrow-repeat fs-1"></i>
                    </div>
                    <h5 class="card-title">بازگشت کالا</h5>
                    <p class="card-text">امکان بازگشت کالا تا 7 روز پس از خرید در صورت وجود مشکل</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- بخش محصولات -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">محصولات پرطرفدار</h2>
                <a href="product.php" class="btn btn-outline-primary">
                    <i class="bi bi-grid-3x3-gap me-1"></i> مشاهده همه
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <?php while ($row = $products->fetch_assoc()): ?>
            <?php 
            // تعیین وضعیت موجودی
            $stock_status = $row['qty'] > 0 ? 'موجود' : 'ناموجود';
            $stock_class = $row['qty'] > 0 ? 'text-success' : 'text-danger';
            
            // استفاده از تابع کمکی برای دریافت مسیر تصویر
            $image_path = getProductImage($row['image']);
            ?>
            
            <div class="col-lg-4 col-md-6 mb-4 animate-on-scroll"> <!-- تغییر از col-lg-3 به col-lg-4 -->
                <div class="card h-100 product-card shadow-sm">
                    <div class="product-image-container">
                        <a href="product.php?id=<?php echo (int)$row['id']; ?>">
                            <img src="<?php echo $image_path; ?>" 
                                 class="card-img-top product-image" 
                                 alt="<?php echo htmlspecialchars($row['name']); ?>"
                                 onerror="this.src='<?php echo $basePath; ?>/assets/images/no-image.png'">
                        </a>
                        <?php if ($row['qty'] <= 0): ?>
                            <div class="out-of-stock-badge">ناموجود</div>
                        <?php endif; ?>
                        
                        <!-- دکمه افزودن به علاقه‌مندی‌ها -->
                        <?php if (isset($_SESSION['state_login']) && $_SESSION['state_login'] === true): ?>
                            <button class="btn btn-sm btn-light position-absolute top-0 start-0 m-2 add-to-wishlist" 
                                    data-product-id="<?php echo $row['id']; ?>" 
                                    title="افزودن به علاقه‌مندی‌ها">
                                <i class="bi bi-heart"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                        <p class="product-excerpt flex-grow-1"><?php echo htmlspecialchars(details($row['details'], 100)); ?></p>
                        <div class="product-price-container mb-2">
                            <span class="product-price"><?php echo toman($row['price'], true); ?></span>
                        </div>
                        <div class="product-stock mb-3">
                            <span class="<?php echo $stock_class; ?>">
                                <i class="bi bi-box-seam"></i> <?php echo $stock_status; ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mt-auto">
                            <a href="product.php?id=<?php echo (int)$row["id"] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-info-circle"></i> جزئیات
                            </a>
                            <?php if (isset($_SESSION['state_login']) && $_SESSION['state_login'] === true): ?>
                                <?php if ($row['qty'] > 0): ?>
                                    <button class="btn btn-primary btn-sm add-to-cart" 
                                            data-product-id="<?php echo $row['id']; ?>" 
                                            data-product-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                            data-product-price="<?php echo $row['price']; ?>">
                                        <i class="bi bi-cart-plus"></i> افزودن به سبد
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="bi bi-cart-plus"></i> ناموجود
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-box-arrow-in-right"></i> ورود
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        
        <?php if ($products->num_rows == 0): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i>
                در حال حاضر محصولی برای نمایش وجود ندارد.
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- صفحه‌بندی -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php 
            // لینک صفحه قبلی
            if ($page > 1): 
                $prev_page = $page - 1;
            ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $prev_page ?>" aria-label="Previous">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Previous">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
            
            <?php 
            // شماره صفحات
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            if ($start_page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                <?php if ($start_page > 2): ?>
                    <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?php echo $i ?>"><?php echo $i ?></a>
                </li>
            <?php endfor; ?>
            
            <?php 
            if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                <?php endif; ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages ?>"><?php echo $total_pages ?></a></li>
            <?php endif; ?>
            
            <?php 
            // لینک صفحه بعدی
            if ($page < $total_pages): 
                $next_page = $page + 1;
            ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $next_page ?>" aria-label="Next">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled">
                    <a class="page-link" href="#" aria-label="Next">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
    
    <!-- بخش خبرنامه -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="card-title">عضویت در خبرنامه</h4>
                            <p class="card-text">با عضویت در خبرنامه ما، از جدیدترین محصولات و تخفیف‌ها مطلع شوید.</p>
                        </div>
                        <div class="col-md-6">
                            <form class="d-flex">
                                <input type="email" class="form-control me-2" placeholder="ایمیل خود را وارد کنید" required>
                                <button type="submit" class="btn btn-light">عضویت</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('./includes/footer.php'); ?>

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