<?php
if (!defined('BASE_PATH')) {
    include('./includes/init.php');
}
// شروع سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// include کردن فایل توابع که شامل تابع require_admin است
include_once('./includes/functions.php');
require_once('includes/PersianCalendar.php');
// بررسی دسترسی مدیر (هم ورود کاربر و هم سطح دسترسی را بررسی می‌کند)
require_admin();

// اگر کاربر مدیر نیست، ریدایرکت کن
if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] !== "admin") {
    header('Location: restricted.php');
    exit;
}

$title = "مدیریت سفارشات";
include('./includes/header.php');
include('includes/db_link.php');

// دریافت سفارشات با جزئیات کامل
$stmt = $link->prepare("
    SELECT o.*, u.realname, u.email, 
           (SELECT SUM(oi.price * oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) as total_amount
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.orderdate DESC
");
$stmt->execute();
$orders_result = $stmt->get_result();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">مدیریت سفارشات</h1>
            <p class="text-muted mb-0">مدیریت و پیگیری سفارشات کاربران</p>
        </div>
        <div class="d-flex gap-2">
            <!-- فیلتر وضعیت -->
            <select class="form-select form-select-sm" id="statusFilter">
                <option value="">همه وضعیت‌ها</option>
                <option value="0">تحت بررسی</option>
                <option value="1">آماده ارسال</option>
                <option value="2">ارسال شده</option>
                <option value="3">لغو شده</option>
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

    <!-- راهنمای وضعیت سفارش -->
    <div class="card mb-4 border-0 bg-light">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-info-circle me-2"></i>راهنمای وضعیت سفارش</h5>
            <div class="row">
                <div class="col-md-2 mb-2">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-warning me-2">0</span>
                        <div>تحت بررسی</div>
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-info me-2">1</span>
                        <div>درحال آماده سازی</div>
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary me-2">2</span>
                        <div>ارسال شده</div>
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-danger me-2">3</span>
                        <div>لغو شده</div>
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2">4</span>
                        <div>تحویل شده</div>
                    </div>
                </div>            </div>
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
                    '0' => ['text' => 'تحت بررسی', 'color' => 'warning', 'icon' => 'clock-history'],
                    '1' => ['text' => 'آماده ارسال', 'color' => 'info', 'icon' => 'box-seam'],
                    '2' => ['text' => 'ارسال شده', 'color' => 'primary', 'icon' => 'truck'],
                    '3' => ['text' => 'لغو شده', 'color' => 'danger', 'icon' => 'x-circle'],
					'4' => ['text' => 'تحویل داده شده', 'color' => 'success', 'icon' => 'check-circle']
                ];

                $status = $status_config[$order['status']] ?? ['text' => 'نامشخص', 'color' => 'secondary', 'icon' => 'question-circle'];
                ?>
                
                <div class="col-lg-6 mb-4 order-card" data-status="<?= $order['status'] ?>" data-id="<?= $order['id'] ?>">
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
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">مشتری</small>
                                        <span class="fw-bold"><?= htmlspecialchars($order['realname']) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($order['username']) ?></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">مبلغ کل</small>
                                        <span class="fw-bold h5 mb-0"><?= toman($order['total_amount'], true) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
                                        <small class="text-muted">شماره تماس</small>
                                        <span><?= htmlspecialchars($order['number']) ?></span>
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
                <span class="text-muted">ثبت نشده</span>
            <?php endif; ?>

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
                                    <div>
										<button class="btn btn-sm btn-outline-primary edit-order" 
										data-id="<?= $order['id'] ?>" 
										data-status="<?= $order['status'] ?>" 
										data-trackcode="<?= htmlspecialchars($order['trackcode'] ?? '') ?>"
										data-bs-toggle="modal"
										data-bs-target="#editOrderModal">
										<i class="bi bi-pencil-square me-1"></i> ثبت رهگیری

										<button class="btn btn-sm btn-primary" >
										<a href="admin_order_edit.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
											<i class="bi bi-penci1-square me-1"></i> ویرایش سفارش
										</a>
										</button>
                                    </div>
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
            <h4 class="mt-3">هیچ سفارشی یافت نشد</h4>
            <p class="text-muted">در حال حاضر هیچ سفارشی در سیستم ثبت نشده است</p>
        </div>
    <?php endif; ?>
</div>

<!-- مودال ویرایش سفارش -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOrderModalLabel">ویرایش سفارش</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm">
                    <input type="hidden" id="orderId" name="order_id">
                    
                    <div class="mb-3">
                        <label for="orderStatus" class="form-label">وضعیت سفارش</label>
                        <select class="form-select" id="orderStatus" name="status" required>
                            <option value="0">تحت بررسی</option>
                            <option value="1">آماده ارسال</option>
                            <option value="2">ارسال شده</option>
                            <option value="3">لغو شده</option>
							<option value="4">تحویل شده</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="trackCode" class="form-label">کد پیگیری پست</label>
                        <input type="text" class="form-control ltr" id="trackCode" name="trackcode" placeholder="کد پیگیری را وارد کنید">
                        <div class="form-text">این کد فقط برای سفارش‌های ارسال شده نیاز است</div>
                    </div>
                    
                    <div class="alert alert-info d-none" id="updateAlert">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="updateMessage"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-primary" id="saveOrderBtn">ذخیره تغییرات</button>
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
    // فیلتر وضعیت سفارش
    const statusFilter = document.getElementById('statusFilter');
    const orderCards = document.querySelectorAll('.order-card');
    
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value;
        
        orderCards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            
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
    
    // ویرایش سفارش
    const editOrderModal = document.getElementById('editOrderModal');
    const editOrderForm = document.getElementById('editOrderForm');
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    const updateAlert = document.getElementById('updateAlert');
    const updateMessage = document.getElementById('updateMessage');
    
    // تنظیم مقادیر اولیه مودال ویرایش
    editOrderModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-id');
        const orderStatus = button.getAttribute('data-status');
        const trackCode = button.getAttribute('data-trackcode');
        
        document.getElementById('orderId').value = orderId;
        document.getElementById('orderStatus').value = orderStatus;
        document.getElementById('trackCode').value = trackCode;
        
        // مخفی کردن پیام‌های قبلی
        updateAlert.classList.add('d-none');
    });
    
			// ذخیره تغییرات سفارش
			saveOrderBtn.addEventListener('click', function() {
				const orderId = document.getElementById('orderId').value;
				const orderStatus = document.getElementById('orderStatus').value;
				const trackCode = document.getElementById('trackCode').value;
				
				// غیرفعال کردن دکمه ذخیره
				saveOrderBtn.disabled = true;
				saveOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>در حال ذخیره...';
				
				// ارسال درخواست آژاکسی
				fetch('admin_update_order.php', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: `order_id=${orderId}&status=${orderStatus}&trackcode=${encodeURIComponent(trackCode)}`
				})
				.then(response => response.json())
				.then(data => {
					if (data.success) {
						// نمایش پیام موفقیت
						updateAlert.classList.remove('d-none', 'alert-danger');
						updateAlert.classList.add('alert-success');
						updateMessage.textContent = data.message;
						
						// به‌روزرسانی وضعیت در کارت
						const orderCard = document.querySelector(`.order-card[data-id="${orderId}"]`);
						if (orderCard) {
							// تعیین وضعیت و رنگ (برای وضعیت‌های عددی)
							const statusBadge = orderCard.querySelector('.badge');
							const statusConfig = {
								'0': { text: 'در حال بررسی', color: 'warning' },
								'1': { text: 'در حال آماده‌سازی', color: 'info' },
								'2': { text: 'ارسال شده', color: 'primary' },
								'3': { text: 'لغو شده', color: 'danger' },
								'4': { text: 'تحویل شده', color: 'success'}
							};

							const status = statusConfig[orderStatus];
							statusBadge.textContent = status.text;
							statusBadge.className = `badge bg-${status.color} fs-6`;
							
							// به‌روزرسانی کد پیگیری
							const trackCodeContainer = orderCard.querySelector('.copy-track').parentElement;
							if (trackCode) {
								trackCodeContainer.querySelector('.me-2').textContent = trackCode ? formatTrackCode(trackCode) : 'ثبت نشده';
							}
							
							// به‌روزرسانی دیتا اتریبیوت
							orderCard.setAttribute('data-status', orderStatus);
						}
						
						// بستن مودال بعد از 2 ثانیه
						setTimeout(() => {
							const modal = bootstrap.Modal.getInstance(editOrderModal);
							modal.hide();
						}, 2000);
					} else {
						// نمایش پیام خطا
						updateAlert.classList.remove('d-none', 'alert-success');
						updateAlert.classList.add('alert-danger');
						updateMessage.textContent = data.message;
					}
				})
				.catch(error => {
					console.error('Error:', error);
					updateAlert.classList.remove('d-none', 'alert-success');
					updateAlert.classList.add('alert-danger');
					updateMessage.textContent = 'خطا در ارتباط با سرور';
				})
				.finally(() => {
					// فعال کردن دکمه ذخیره
					saveOrderBtn.disabled = false;
					saveOrderBtn.innerHTML = 'ذخیره تغییرات';
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