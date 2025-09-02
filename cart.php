<?php
$title = "سبد خرید";
include('includes/header.php');
include('includes/db_link.php');

// اگر کاربر لاگین کرده از دیتابیس استفاده کن، در غیر این صورت از سشن
if (isset($_SESSION['state_login']) && $_SESSION['state_login'] === true) {
    $user_id = $_SESSION['user_id'];
    
    // دریافت آیتم‌های سبد خرید از دیتابیس
    $stmt = $link->prepare("SELECT c.*, p.name, p.price, p.image, p.qty as stock 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();
    
    // محاسبه مجموع قیمت
    $total = 0;
    $cart_data = [];
    while ($item = $cart_items->fetch_assoc()) {
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
        $cart_data[] = $item;
    }
} else {
    // کاربر مهمان - استفاده از سشن
    $cart_data = [];
    $total = 0;
    
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // دریافت اطلاعات محصولات از دیتابیس
        $product_ids = array_keys($_SESSION['cart']);
        $ids = implode(',', $product_ids);
        $query = "SELECT id, name, price, image, qty as stock FROM products WHERE id IN ($ids)";
        $result = $link->query($query);
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[$row['id']] = $row;
        }
        
        // ساخت آرایه سبد خرید با اطلاعات کامل محصولات
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            if (isset($products[$product_id])) {
                $product = $products[$product_id];
                $subtotal = $product['price'] * $quantity;
                $total += $subtotal;
                $cart_data[] = [
                    'id' => $product_id,
                    'product_id' => $product_id,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'quantity' => $quantity,
                    'stock' => $product['stock']
                ];
            }
        }
    }
}
?>

<div class="container mt-4">
    <h1 class="mb-4">سبد خرید</h1>
    
    <?php if (empty($cart_data)): ?>
        <div class="alert alert-info text-center">
            سبد خرید شما خالی است. <a href="index.php" class="alert-link">بازگشت به فروشگاه</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>محصول</th>
                        <th>قیمت</th>
                        <th>تعداد</th>
                        <th>جمع</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_data as $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                        <tr data-product-id="<?= $item['product_id'] ?>" data-price="<?= $item['price'] ?>">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= getProductImage($item['image']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                         class="img-thumbnail me-3" style="width: 80px;">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                        <small class="text-muted">کد: <?= $item['product_id'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="item-price"><?= toman($item['price'], true) ?></td>
                            <td>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button class="btn btn-outline-secondary decrease-qty" type="button">-</button>
                                    <input type="text" class="form-control text-center qty-input" 
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" max="<?= $item['stock'] ?>" readonly>
                                    <button class="btn btn-outline-secondary increase-qty" type="button">+</button>
                                </div>
                            </td>
                            <td class="item-subtotal"><?= toman($subtotal, true) ?></td>
                            <td>
                                <button class="btn btn-sm btn-danger remove-from-cart">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>جمع کل:</strong></td>
                        <td class="cart-total"><strong><?= toman($total, true) ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right"></i> ادامه خرید
            </a>
            <?php if (isset($_SESSION['state_login']) && $_SESSION['state_login'] === true): ?>
                <a href="checkout.php" class="btn btn-success btn-lg">
                    <i class="bi bi-credit-card"></i> تسویه حساب
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right"></i> برای تسویه حساب وارد شوید
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include('includes/footer.php'); ?>