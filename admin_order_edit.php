<?php
if (!defined('BASE_PATH')) {
    include('./includes/init.php');
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once('./includes/functions.php');
require_once('includes/PersianCalendar.php');
require_admin();

// بررسی وجود سفارش
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$order_id) {
    header('Location: admin_orders.php');
    exit;
}

include('includes/db_link.php');

// دریافت اطلاعات سفارش
$stmt = $link->prepare("
    SELECT o.*, u.realname, u.email, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows === 0) {
    header('Location: admin_orders.php');
    exit;
}
$order = $order_result->fetch_assoc();

// دریافت آیتم‌های سفارش
$stmt = $link->prepare("
    SELECT oi.*, p.name, p.image, p.qty as stock 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

// دریافت محصولات برای افزودن به سفارش
$products_stmt = $link->prepare("SELECT id, name, price, qty FROM products ORDER BY name");
$products_stmt->execute();
$products_result = $products_stmt->get_result();

// تعیین وضعیت و رنگ
$status_config = [
    '0' => ['text' => 'در حال بررسی', 'color' => 'warning', 'icon' => 'clock-history'],
    '1' => ['text' => 'در حال آماده‌سازی', 'color' => 'info', 'icon' => 'gear'],
    '2' => ['text' => 'ارسال شده', 'color' => 'primary', 'icon' => 'truck'],
    '3' => ['text' => 'لغو شده', 'color' => 'danger', 'icon' => 'x-circle'],
    '4' => ['text' => 'تحویل داده شده', 'color' => 'success', 'icon' => 'check-circle']

];

$status = $status_config[$order['status']] ?? ['text' => 'نامشخص', 'color' => 'secondary', 'icon' => 'question-circle'];

$title = "ویرایش سفارش #" . $order_id;
include('./includes/header.php');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">ویرایش سفارش #<?= $order_id ?></h1>
            <p class="text-muted mb-0">
                <span class="badge bg-<?= $status['color'] ?> me-2"><?= $status['text'] ?></span>
                <?= date('Y/m/d H:i', strtotime($order['orderdate'])) ?>
            </p>
        </div>
        <a href="admin_orders.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i> بازگشت به لیست سفارشات
        </a>
    </div>

    <div class="row">
        <!-- بخش اطلاعات مشتری و ارسال -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>اطلاعات مشتری</h5>
                </div>
                <div class="card-body">
                    <p><strong>نام:</strong> <?= htmlspecialchars($order['realname']) ?></p>
                    <p><strong>نام کاربری:</strong> <?= htmlspecialchars($order['username']) ?></p>
                    <p><strong>شماره تماس:</strong> <?= htmlspecialchars($order['number']) ?></p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt me-2"></i>اطلاعات ارسال</h5>
                </div>
                <div class="card-body">
                    <p><strong>استان:</strong> <?= htmlspecialchars($order['province']) ?></p>
                    <p><strong>شهر:</strong> <?= htmlspecialchars($order['city']) ?></p>
                    <p><strong>کد پستی:</strong> <?= htmlspecialchars($order['postal_code']) ?></p>
                    <p><strong>آدرس:</strong> <?= nl2br(htmlspecialchars($order['address'])) ?></p>
                </div>
            </div>
        </div>

        <!-- بخش محصولات سفارش -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-basket me-2"></i>محصولات سفارش</h5>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-circle me-1"></i> افزودن محصول
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>محصول</th>
                                    <th>قیمت واحد</th>
                                    <th>تعداد</th>
                                    <th>موجودی</th>
                                    <th>جمع</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
<tbody>
    <?php 
    $total = 0;
    while ($item = $items_result->fetch_assoc()):
        // استفاده از قیمت فعلی محصول (ممکن است قیمت تغییر کرده باشد)
        $item_price = $item['price']; // قیمت در زمان ثبت سفارش
        $subtotal = $item_price * $item['quantity'];
        $total += $subtotal;
    ?>
        <tr data-item-id="<?= $item['id'] ?>" data-price="<?= $item_price ?>">
            <td>
                <div class="d-flex align-items-center">
                    <img src="<?= getProductImage($item['image']) ?>" 
                         alt="<?= htmlspecialchars($item['name']) ?>" 
                         class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="small text-muted">کد محصول: <?= $item['product_id'] ?></div>
                    </div>
                </div>
            </td>
            <td><?= toman($item_price, true) ?></td>
            <td>
                <div class="input-group input-group-sm" style="width: 120px;">
                    <input type="number" class="form-control item-quantity" 
                           value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>"
                           data-item-id="<?= $item['id'] ?>">
                    <button class="btn btn-outline-secondary update-quantity" 
                            data-item-id="<?= $item['id'] ?>">
                        <i class="bi bi-check"></i>
                    </button>
                </div>
            </td>
            <td>
                <span class="badge bg-<?= $item['stock'] > 0 ? 'success' : 'danger' ?>">
                    <?= $item['stock'] ?>
                </span>
            </td>
            <td class="fw-bold item-subtotal"><?= toman($subtotal, true) ?></td>
            <td>
                <button class="btn btn-sm btn-outline-danger remove-item" 
                        data-item-id="<?= $item['id'] ?>">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    <?php endwhile; ?>
</tbody>                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">جمع کل:</td>
                                    <td class="fw-bold h5"><?= toman($total, true) ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- بخش مدیریت سفارش -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>مدیریت سفارش</h5>
                </div>
                <div class="card-body">
                    <form id="orderManagementForm">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="orderStatus" class="form-label">وضعیت سفارش</label>
                                <select class="form-select" id="orderStatus" name="status">
                                    <?php foreach ($status_config as $key => $config): ?>
                                        <option value="<?= $key ?>" <?= $order['status'] == $key ? 'selected' : '' ?>>
                                            <?= $config['text'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="trackCode" class="form-label">کد پیگیری پست</label>
                                <input type="text" class="form-control ltr" id="trackCode" name="trackcode" 
                                       value="<?= htmlspecialchars($order['trackcode'] ?? '') ?>"
                                       placeholder="کد پیگیری را وارد کنید">
                                <div class="form-text">این کد فقط برای سفارش‌های ارسال شده نیاز است</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adminNotes" class="form-label">یادداشت‌های مدیریتی</label>
                            <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3"
                                      placeholder="یادداشت‌های داخلی برای این سفارش"><?= htmlspecialchars($order['admin_notes'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="customerNotes" class="form-label">پیام به مشتری</label>
                            <textarea class="form-control" id="customerNotes" name="customer_notes" rows="3"
                                      placeholder="پیامی که به مشتری نمایش داده می‌شود"><?= htmlspecialchars($order['customer_notes'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="alert alert-info d-none" id="updateAlert">
                            <i class="bi bi-info-circle me-2"></i>
                            <span id="updateMessage"></span>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-danger" id="cancelOrderBtn">
                                <i class="bi bi-x-circle me-2"></i> لغو سفارش
                            </button>
                            <div>
                                <button type="button" class="btn btn-secondary" id="saveChangesBtn">
                                    <i class="bi bi-save me-2"></i> ذخیره تغییرات
                                </button>
                                <button type="button" class="btn btn-primary" id="notifyCustomerBtn">
                                    <i class="bi bi-envelope me-2"></i> اطلاع‌رسانی به مشتری
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال افزودن محصول -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">افزودن محصول به سفارش</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="productSearch" placeholder="جستجوی محصول...">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>محصول</th>
                                <th>قیمت</th>
                                <th>موجودی</th>
                                <th>تعداد</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <tr data-product-id="<?= $product['id'] ?>" data-product-name="<?= htmlspecialchars($product['name']) ?>" 
                                    data-product-price="<?= $product['price'] ?>" data-product-stock="<?= $product['qty'] ?>">
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= toman($product['price'], true) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $product['qty'] > 0 ? 'success' : 'danger' ?>">
                                            <?= $product['qty'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm product-quantity" 
                                               value="1" min="1" max="<?= $product['qty'] ?>" style="width: 80px;">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary add-product-to-order" 
                                                data-product-id="<?= $product['id'] ?>"
                                                data-order-id="<?= $order_id ?>">
                                            افزودن
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast برای اطلاع‌رسانی -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="notificationToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">
                پیام اطلاع‌رسانی
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // جستجوی محصول
    const productSearch = document.getElementById('productSearch');
    const productsTableBody = document.getElementById('productsTableBody');
    
    productSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = productsTableBody.querySelectorAll('tr');
        
        rows.forEach(row => {
            const productName = row.getAttribute('data-product-name').toLowerCase();
            if (productName.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // افزودن محصول به سفارش
    const addProductButtons = document.querySelectorAll('.add-product-to-order');
    const addProductModal = new bootstrap.Modal(document.getElementById('addProductModal'));
    
    addProductButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantity = this.closest('tr').querySelector('.product-quantity').value;
            
            // ارسال درخواست آژاکسی
            fetch('admin_add_order_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=<?= $order_id ?>&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // نمایش پیام موفقیت
                    showToast('محصول با موفقیت به سفارش اضافه شد', 'success');
                    
                    // رفرش صفحه برای نمایش محصولات جدید
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('خطا در ارتباط با سرور', 'danger');
            });
        });
    });
    
    // به‌روزرسانی تعداد محصول
    const updateQuantityButtons = document.querySelectorAll('.update-quantity');
    
    updateQuantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.getAttribute('data-item-id');
            const quantityInput = this.closest('tr').querySelector('.item-quantity');
            const quantity = quantityInput.value;
            
            // ارسال درخواست آژاکسی
            fetch('admin_update_order_item.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `item_id=${itemId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // نمایش پیام موفقیت
                    showToast('تعداد محصول با موفقیت به‌روزرسانی شد', 'success');
                    
                    // به‌روزرسانی مجموع
                    location.reload();
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('خطا در ارتباط با سرور', 'danger');
            });
        });
    });
    
    // حذف محصول از سفارش
    const removeItemButtons = document.querySelectorAll('.remove-item');
    
    removeItemButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('آیا از حذف این محصول از سفارش مطمئن هستید؟')) {
                const itemId = this.getAttribute('data-item-id');
                
                // ارسال درخواست آژاکسی
                fetch('admin_remove_order_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // نمایش پیام موفقیت
                        showToast('محصول با موفقیت از سفارش حذف شد', 'success');
                        
                        // حذف ردیف از جدول
                        this.closest('tr').remove();
                        
                        // به‌روزرسانی مجموع
                        location.reload();
                    } else {
                        showToast(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('خطا در ارتباط با سرور', 'danger');
                });
            }
        });
    });
    
    // ذخیره تغییرات سفارش
    const saveChangesBtn = document.getElementById('saveChangesBtn');
    const orderManagementForm = document.getElementById('orderManagementForm');
    
    saveChangesBtn.addEventListener('click', function() {
        const formData = new FormData(orderManagementForm);
        
        // ارسال درخواست آژاکسی
        fetch('admin_update_order.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('تغییرات با موفقیت ذخیره شد', 'success');
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('خطا در ارتباط با سرور', 'danger');
        });
    });
    
    // لغو سفارش
    const cancelOrderBtn = document.getElementById('cancelOrderBtn');
    
    cancelOrderBtn.addEventListener('click', function() {
        if (confirm('آیا از لغو این سفارش مطمئن هستید؟')) {
            // تغییر وضعیت به لغو شده
            document.getElementById('orderStatus').value = 'cancelled';
            
            // ارسال درخواست آژاکسی
            const formData = new FormData(orderManagementForm);
            
            fetch('admin_update_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('سفارش با موفقیت لغو شد', 'warning');
                    
                    // بازگشت به لیست سفارشات
                    setTimeout(() => {
                        window.location.href = 'admin_orders.php';
                    }, 2000);
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('خطا در ارتباط با سرور', 'danger');
            });
        }
    });
    
    // اطلاع‌رسانی به مشتری
    const notifyCustomerBtn = document.getElementById('notifyCustomerBtn');
    
    notifyCustomerBtn.addEventListener('click', function() {
        const customerNotes = document.getElementById('customerNotes').value;
        
        if (!customerNotes.trim()) {
            showToast('لطفاً پیامی برای مشتری وارد کنید', 'warning');
            return;
        }
        
        // ارسال درخواست آژاکسی
        fetch('admin_notify_customer.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `order_id=<?= $order_id ?>&message=${encodeURIComponent(customerNotes)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('پیام با موفقیت برای مشتری ارسال شد', 'success');
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('خطا در ارتباط با سرور', 'danger');
        });
    });
    
    // تابع نمایش Toast
    function showToast(message, type = 'info') {
        const toast = document.getElementById('notificationToast');
        const toastMessage = document.getElementById('toastMessage');
        
        // تنظیم پیام و رنگ
        toastMessage.textContent = message;
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        
        // نمایش Toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }
});


</script>

<?php include('./includes/footer.php'); ?>