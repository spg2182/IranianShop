<?php
// منطق‌های اولیه
include('includes/db_link.php');
include('includes/functions.php'); // اضافه کردن فایل توابع
require_login();

// بررسی و اعتبارسنجی پارامترها
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    header('Location: index.php');
    exit;
}

// دریافت اطلاعات سفارش
$stmt = $link->prepare("SELECT o.*, u.realname, u.email 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ? AND o.user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order_result = $stmt->get_result();
if ($order_result->num_rows === 0) {
    header('Location: index.php');
    exit;
}
$order = $order_result->fetch_assoc();

// دریافت آیتم‌های سفارش
$stmt = $link->prepare("SELECT oi.*, p.name, p.image 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

// تعیین وضعیت و رنگ
$status_config = [
    'pending' => ['text' => 'در حال بررسی', 'color' => 'warning', 'icon' => 'clock-history'],
    'processing' => ['text' => 'در حال آماده‌سازی', 'color' => 'info', 'icon' => 'gear'],
    'shipped' => ['text' => 'ارسال شده', 'color' => 'primary', 'icon' => 'truck'],
    'delivered' => ['text' => 'تحویل داده شده', 'color' => 'success', 'icon' => 'check-circle'],
    'cancelled' => ['text' => 'لغو شده', 'color' => 'danger', 'icon' => 'x-circle']
];

$status = $status_config[$order['status']] ?? ['text' => 'نامشخص', 'color' => 'secondary', 'icon' => 'question-circle'];

// حالا که تمام منطق‌ها انجام شده، می‌توانیم هدر را include کنیم
$title = "تأیید سفارش";
include('includes/header.php');
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- کارت اصلی -->
            <div class="card shadow-sm overflow-hidden">
                <div class="card-header bg-gradient bg-<?= $status['color'] ?> text-white py-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-<?= $status['icon'] ?> fs-1 me-3"></i>
                        <div>
                            <h2 class="mb-1">سفارش شما با موفقیت ثبت شد</h2>
                            <?php if (!empty($order['trackcode'])): ?>
                            <p class="mb-0">کد پیگیری سفارش: <strong><?= formatTrackCode($order['trackcode']) ?></strong></p>
                        <?php else: ?>
                            <p class="mb-0 text-muted">کد پیگیری سفارش هنوز صادر نشده است.</p>
                        <?php endif; ?>
                           <!-- <p class="mb-0 opacity-75">کد پیگیری: >?= formatTrackCode($order['trackcode']) ?></p> -->
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <!-- بخش اطلاعات اصلی سفارش -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>اطلاعات سفارش</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>شماره سفارش:</span>
                                            <strong>#<?= $order_id ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>تاریخ سفارش:</span>
                                            <strong><?= date('Y/m/d H:i', strtotime($order['orderdate'])) ?></strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>وضعیت:</span>
                                            <span class="badge bg-<?= $status['color'] ?>"><?= $status['text'] ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>کد پیگیری:</span>
                                            <div>
                                                <span class="me-2"><?= formatTrackCode($order['trackcode']) ?></span>
                                                <button class="btn btn-sm btn-outline-secondary copy-track" data-track="<?= htmlspecialchars($order['trackcode']) ?>">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-truck me-2"></i>اطلاعات ارسال</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <div class="fw-bold">گیرنده:</div>
                                            <div><?= htmlspecialchars($order['realname']) ?></div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="fw-bold">شماره تماس:</div>
                                            <div><?= htmlspecialchars($order['number']) ?></div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="fw-bold">آدرس:</div>
                                            <div>
                                                <?= htmlspecialchars($order['province']) ?>، 
                                                <?= htmlspecialchars($order['city']) ?>، 
                                                <?= nl2br(htmlspecialchars($order['address'])) ?>
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <div class="fw-bold">کد پستی:</div>
                                            <div><?= htmlspecialchars($order['postal_code']) ?></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- بخش محصولات سفارش -->
                    <div class="card border-0 mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-basket me-2"></i>محصولات سفارش</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>محصول</th>
                                            <th class="text-center">تعداد</th>
                                            <th class="text-end">قیمت واحد</th>
                                            <th class="text-end">جمع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total = 0;
                                        while ($item = $items_result->fetch_assoc()):
                                            $subtotal = $item['price'] * $item['quantity'];
                                            $total += $subtotal;
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= getProductImage($item['image']) ?>" 
                                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                                             class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($item['name']) ?></div>
                                                            <div class="small text-muted">کد محصول: <?= $item['product_id'] ?></div>
                                                            <?php if (!empty($order['notes'])): ?>
                                                                <div class="small text-info mt-1">
                                                                    <i class="bi bi-info-circle me-1"></i>
                                                                    <?= nl2br(htmlspecialchars($order['notes'])) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary"><?= $item['quantity'] ?></span>
                                                </td>
                                                <td class="text-end"><?= toman($item['price'], true) ?></td>
                                                <td class="text-end fw-bold"><?= toman($subtotal, true) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">جمع کل:</td>
                                            <td class="text-end fw-bold h5"><?= toman($total, true) ?></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- دکمه‌های اقدام -->
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <a href="orders.php" class="btn btn-outline-primary">
                            <i class="bi bi-receipt me-2"></i> مشاهده سفارشات
                        </a>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i> چاپ فاکتور
                            </button>
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-shop me-2"></i> ادامه خرید
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- بخش راهنمای وضعیت سفارش -->
            <div class="card mt-4 border-0 bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-question-circle me-2"></i>راهنمای وضعیت سفارش</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2"><i class="bi bi-clock-history"></i></span>
                                <div>
                                    <div class="fw-bold">در حال بررسی</div>
                                    <div class="small text-muted">سفارش شما دریافت و در حال بررسی است</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2"><i class="bi bi-gear"></i></span>
                                <div>
                                    <div class="fw-bold">در حال آماده‌سازی</div>
                                    <div class="small text-muted">سفارش شما در حال آماده‌سازی است</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2"><i class="bi bi-truck"></i></span>
                                <div>
                                    <div class="fw-bold">ارسال شده</div>
                                    <div class="small text-muted">سفارش شما به پست تحویل داده شد</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2"><i class="bi bi-check-circle"></i></span>
                                <div>
                                    <div class="fw-bold">تحویل داده شده</div>
                                    <div class="small text-muted">سفارش شما تحویل داده شد</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
});
</script>

<style>
.card-header.bg-gradient {
    background: linear-gradient(45deg, var(--bs-<?= $status['color'] ?>), var(--bs-<?= $status['color'] ?>-rgb)) !important;
}

.table img {
    transition: transform 0.2s;
}

.table img:hover {
    transform: scale(1.05);
}

.list-group-item {
    padding: 0.5rem 0;
    border: none;
    background: transparent;
}

.list-group-item + .list-group-item {
    border-top: 1px dashed rgba(0,0,0,0.1);
}

@media print {
    .btn, .card.bg-light {
        display: none !important;
    }
    
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}
</style>

<?php include('includes/footer.php'); ?>