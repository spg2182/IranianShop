<?php
ob_start();
// آماده‌سازی هدر
$title = "سفارشات شما";
include('includes/header.php');
include('includes/functions.php');
include('includes/db_link.php');
require_login();

// بررسی دسترسی کاربر
if (!(isset($_SESSION["state_login"]) && $_SESSION["state_login"] === true)) {
    header('Location: not_logged_in.php');
    exit;
}

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == "admin") {
    header('Location: admin_orders.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// دریافت سفارشات کاربر با جزئیات کامل
$stmt = $link->prepare("
    SELECT o.*, 
           (SELECT SUM(oi.price * oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) as total_amount
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.orderdate DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">سفارشات شما</h1>
            <p class="text-muted mb-0">کاربر گرامی <?= htmlspecialchars($_SESSION['realname']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <!-- فیلتر وضعیت -->
            <select class="form-select form-select-sm" id="statusFilter">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending">در حال بررسی</option>
                <option value="processing">در حال آماده‌سازی</option>
                <option value="shipped">ارسال شده</option>
                <option value="delivered">تحویل داده شده</option>
                <option value="cancelled">لغو شده</option>
            </select>
            <!-- جستجو -->
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="orderSearch" placeholder="جستجوی سفارش...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>
    <?php if ($orders_result->num_rows > 0): ?>
        <div class="row" id="ordersContainer">
            <?php while ($order = $orders_result->fetch_assoc()): ?>
                <?php
                // دریافت آیتم‌های سفارش
                $order_id = $order['id'];
                $items_stmt = $link->prepare("
                    SELECT oi.*, p.name, p.image 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ");
                $items_stmt->bind_param("i", $order_id);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                
                // تعیین وضعیت و رنگ
                $status_config = [
                    'pending' => ['text' => 'در حال بررسی', 'color' => 'warning', 'progress' => 25],
                    'processing' => ['text' => 'در حال آماده‌سازی', 'color' => 'info', 'progress' => 50],
                    'shipped' => ['text' => 'ارسال شده', 'color' => 'primary', 'progress' => 75],
                    'delivered' => ['text' => 'تحویل داده شده', 'color' => 'success', 'progress' => 100],
                    'cancelled' => ['text' => 'لغو شده', 'color' => 'danger', 'progress' => 0]
                ];
                
                $status = $status_config[$order['status']] ?? ['text' => 'نامشخص', 'color' => 'secondary', 'progress' => 0];
                ?>
                
                <div class="col-12 mb-4 order-card" data-status="<?= $order['status'] ?>" data-id="<?= $order['id'] ?>">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">سفارش #<?= $order['id'] ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?= date('Y/m/d H:i', strtotime($order['orderdate'])) ?>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge bg-<?= $status['color'] ?> fs-6"><?= $status['text'] ?></span>
                                    <button class="btn btn-sm btn-outline-secondary toggle-details" data-bs-toggle="collapse" data-bs-target="#orderDetails<?= $order['id'] ?>">
                                        <i class="bi bi-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">مبلغ کل</small>
                                        <span class="fw-bold h5 mb-0"><?= toman($order['total_amount'], true) ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
    <small class="text-muted">کد پیگیری</small>
    <div class="d-flex align-items-center">
        <?php if (!empty($order['trackcode'])): ?>
            <span class="me-2"><?= formatTrackCode($order['trackcode']) ?></span>
            <button class="btn btn-sm btn-outline-secondary copy-track" data-track="<?= htmlspecialchars($order['trackcode']) ?>">
                <i class="bi bi-clipboard"></i>
            </button>
        <?php else: ?>
            <span class="me-2 text-muted">صادر نشده</span>
        <?php endif; ?>
    </div>
</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">وضعیت سفارش</small>
                                        <div class="progress mt-1" style="height: 8px;">
                                            <div class="progress-bar bg-<?= $status['color'] ?>" role="progressbar" style="width: <?= $status['progress'] ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- جزئیات سفارش (پنهان به صورت پیش‌فرض) -->
                            <div class="collapse mt-4" id="orderDetails<?= $order['id'] ?>">
                                <hr>
                                <h6 class="mb-3">محصولات این سفارش:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>محصول</th>
                                                <th>تعداد</th>
                                                <th>قیمت واحد</th>
                                                <th>جمع</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($item = $items_result->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?= getProductImage($item['image']) ?>" 
                                                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                                                 class="img-thumbnail me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                            <span><?= htmlspecialchars($item['name']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?= $item['quantity'] ?></td>
                                                    <td><?= toman($item['price'], true) ?></td>
                                                    <td><?= toman($item['price'] * $item['quantity'], true) ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted small">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        ارسال به: <?= htmlspecialchars($order['city']) ?>
                                    </div>
                                    <a href="order_confirmation.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-receipt me-1"></i> مشاهده فاکتور
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3">هنوز سفارشی ثبت نکرده‌اید</h4>
            <p class="text-muted">می‌توانید از فروشگاه ما محصولات را مشاهده و خریداری کنید</p>
            <a href="index.php" class="btn btn-primary mt-3">
                <i class="bi bi-shop me-2"></i> رفتن به فروشگاه
            </a>
        </div>
    <?php endif; ?>
</div>
<!-- Toast برای کپی کد پیگیری -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="copyToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                کد پیگیری با موفقیت کپی شد!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // فیلتر وضعیت سفارش
    const statusFilter = document.getElementById('statusFilter');
    const orderCards = document.querySelectorAll('.order-card');
    
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value.toLowerCase();
        
        orderCards.forEach(card => {
            const cardStatus = card.getAttribute('data-status').toLowerCase();
            
            if (selectedStatus === '' || cardStatus === selectedStatus) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // جستجوی سفارش
    const orderSearch = document.getElementById('orderSearch');
    orderSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        orderCards.forEach(card => {
            const orderId = card.getAttribute('data-id');
            const cardText = card.textContent.toLowerCase();
            
            if (cardText.includes(searchTerm) || orderId.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // کپی کد پیگیری
    const copyButtons = document.querySelectorAll('.copy-track');
    const copyToast = new bootstrap.Toast(document.getElementById('copyToast'));
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const trackCode = this.getAttribute('data-track');
            
            navigator.clipboard.writeText(trackCode).then(() => {
                // تغییر آیکون موقت
                const originalIcon = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check-lg"></i>';
                
                // نمایش toast
                copyToast.show();
                
                // بازگشت به حالت اولیه
                setTimeout(() => {
                    this.innerHTML = originalIcon;
                }, 2000);
            });
        });
    });
    
    // تغییر آیکون دکمه جزئیات
    const toggleButtons = document.querySelectorAll('.toggle-details');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            
            if (icon.classList.contains('bi-chevron-down')) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            } else {
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        });
    });
});
</script>
<style>
.order-card {
    transition: all 0.3s ease;
}
.order-card:hover {
    transform: translateY(-3px);
}
.copy-track:hover {
    color: var(--bs-success);
}
.progress {
    background-color: rgba(0,0,0,0.05);
}
.table img {
    transition: transform 0.2s;
}
.table img:hover {
    transform: scale(1.1);
}
#orderSearch:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
@media (max-width: 768px) {
    .d-flex.justify-content-between.align-items-center.mb-4 {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .d-flex.gap-2 {
        width: 100%;
        margin-top: 1rem;
    }
    
    .input-group {
        flex: 1;
    }
}
</style>
<?php include('./includes/footer.php'); ?>