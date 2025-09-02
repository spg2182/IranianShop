<?php
$title = "لیست علاقه‌مندی‌ها";
include('includes/header.php');
include('includes/db_link.php');
require_login();

$user_id = $_SESSION['user_id'];

// دریافت محصولات لیست علاقه‌مندی‌ها
$stmt = $link->prepare("SELECT w.*, p.name, p.price, p.image, p.qty 
                        FROM wishlist w 
                        JOIN products p ON w.product_id = p.id 
                        WHERE w.user_id = ? 
                        ORDER BY w.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist_items = $stmt->get_result();
?>

<div class="container mt-4">
    <h1 class="mb-4">لیست علاقه‌مندی‌ها</h1>
    
    <?php if ($wishlist_items->num_rows === 0): ?>
        <div class="alert alert-info text-center">
            لیست علاقه‌مندی‌های شما خالی است. <a href="index.php" class="alert-link">مشاهده محصولات</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php while ($item = $wishlist_items->fetch_assoc()): ?>
                <?php 
                // استفاده از تابع کمکی برای محصولات مرتبط
                $related_image_path = getProductImage($item['image']);
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100 product-card shadow-sm">
                        <div class="product-image-container" style="height: 200px;">
                            <a href="product.php?id=<?= $item['product_id'] ?>">
                                <img src="<?= $related_image_path ?>" 
                                     class="card-img-top product-image" 
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     style="object-fit: cover;"
                                     onerror="this.src='/assets/images/no-image.png'">
                            </a>
                            <button class="btn btn-sm btn-danger position-absolute top-0 start-0 m-2 remove-from-wishlist" 
                                    data-product-id="<?= $item['product_id'] ?>" 
                                    title="حذف از علاقه‌مندی‌ها">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="product-title"><?= htmlspecialchars($item['name']) ?></h5>
                            <p class="product-price mb-3"><?= toman($item['price'], true) ?></p>
                            <div class="mt-auto">
                                <?php if ($item['qty'] > 0): ?>
                                    <button class="btn btn-sm btn-primary add-to-cart w-100" 
                                            data-product-id="<?= $item['product_id'] ?>" 
                                            data-product-name="<?= htmlspecialchars($item['name']) ?>"
                                            data-stock="<?= $item['qty'] ?>">
                                        <i class="bi bi-cart-plus"></i> افزودن به سبد خرید
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary w-100" disabled>
                                        <i class="bi bi-cart-plus"></i> ناموجود
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // توابع کمکی در صورت عدم وجود
    if (typeof window.showToast === 'undefined') {
        window.showToast = function(message, type = 'info') {
            alert(message);
        };
    }
    
    // Event Delegation برای دکمه‌های حذف از علاقه‌مندی‌ها
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-from-wishlist')) {
            const button = e.target.closest('.remove-from-wishlist');
            const productId = button.dataset.productId;
            const card = button.closest('.card');
            
            if (confirm('آیا از حذف این محصول از لیست علاقه‌مندی‌ها مطمئن هستید؟')) {
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('action', 'remove');
                
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
                        card.remove();
                        
                        // بررسی خالی بودن لیست
                        const cards = document.querySelectorAll('.product-card');
                        if (cards.length === 0) {
                            location.reload();
                        }
                        
                        window.showToast(data.message, 'success');
                    } else {
                        window.showToast(data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.showToast('خطا در ارتباط با سرور', 'danger');
                });
            }
        }
    });
    
    // Event Delegation برای دکمه‌های افزودن به سبد خرید
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart')) {
            const button = e.target.closest('.add-to-cart');
            const productId = button.dataset.productId;
            const productName = button.dataset.productName || 'محصول';
            const stock = parseInt(button.dataset.stock) || 0;
            
            // اگر محصول موجودی نداشته باشد
            if (stock <= 0) {
                window.showToast('این محصول در حال حاضر موجود نیست', 'warning');
                return;
            }
            
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
                    
                    window.showToast(`${productName} به سبد خرید اضافه شد`, 'success');
                } else {
                    window.showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showToast('خطا در ارتباط با سرور', 'danger');
            });
        }
    });
});
</script>

<?php include('includes/footer.php'); ?>