// توابع عمومی برای فروشگاه
const Shop = {
    // تابع نمایش پیام
    showToast: function(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    },
    
    // تابع فرمت‌سازی قیمت
    toman: function(price, showCurrency = false) {
        if (typeof window.toman === 'function') {
            return window.toman(price, showCurrency);
        }
        return price.toLocaleString('fa-IR') + (showCurrency ? ' تومان' : '');
    },
    
    // تابع به‌روزرسانی شمارنده سبد خرید
    updateCartBadge: function(count) {
        const cartBadge = document.querySelector('.navbar .badge');
        if (cartBadge) {
            cartBadge.textContent = count;
            if (count == 0) {
                cartBadge.style.display = 'none';
            } else {
                cartBadge.style.display = 'block';
            }
        }
    },
    
    // تابع افزودن به سبد خرید
    addToCart: function(productId, productName = 'محصول', stock = 0, quantity = 1) {
        if (stock <= 0) {
            Shop.showToast('این محصول در حال حاضر موجود نیست', 'warning');
            return;
        }
        
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', 'add');
        formData.append('quantity', quantity);
        
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
                Shop.updateCartBadge(data.cart_count);
                Shop.showToast(`${productName} به سبد خرید اضافه شد`, 'success');
            } else {
                Shop.showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Shop.showToast('خطا در ارتباط با سرور', 'danger');
        });
    },
    
    // تابع به‌روزرسانی آیتم سبد خرید
    updateCartItem: function(productId, quantity, rowElement, remove = false) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('action', remove ? 'remove' : 'update');
        if (!remove) formData.append('quantity', quantity);
        
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
                Shop.updateCartBadge(data.cart_count);
                
                if (remove) {
                    rowElement.remove();
                    
                    // بررسی خالی بودن سبد خرید
                    const rows = document.querySelectorAll('tbody tr');
                    if (rows.length === 0) {
                        location.reload();
                    }
                } else {
                    // به‌روزرسانی تعداد و قیمت
                    const input = rowElement.querySelector('.qty-input');
                    const price = parseFloat(rowElement.dataset.price);
                    const subtotal = price * quantity;
                    
                    input.value = quantity;
                    rowElement.querySelector('.item-subtotal').textContent = Shop.toman(subtotal, true);
                    
                    // به‌روزرسانی مجموع کل
                    Shop.updateCartTotal();
                }
                
                Shop.showToast(data.message, 'success');
            } else {
                Shop.showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Shop.showToast('خطا در ارتباط با سرور', 'danger');
        });
    },
    
    // تابع به‌روزرسانی مجموع کل سبد خرید
    updateCartTotal: function() {
        let total = 0;
        document.querySelectorAll('tbody tr').forEach(row => {
            const price = parseFloat(row.dataset.price);
            const quantity = parseInt(row.querySelector('.qty-input').value);
            total += price * quantity;
        });
        
        const cartTotal = document.querySelector('.cart-total');
        if (cartTotal) {
            cartTotal.innerHTML = `<strong>${Shop.toman(total, true)}</strong>`;
        }
    },
    
    // تابع افزودن به علاقه‌مندی‌ها
    addToWishlist: function(productId, buttonElement) {
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
                if (buttonElement) {
                    buttonElement.innerHTML = '<i class="bi bi-heart-fill"></i>';
                    buttonElement.classList.add('text-danger');
                }
                
                Shop.showToast(data.message, 'success');
            } else {
                Shop.showToast(data.message, 'info');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Shop.showToast('خطا در ارتباط با سرور', 'danger');
        });
    },
    
    // تابع حذف از علاقه‌مندی‌ها
    removeFromWishlist: function(productId, cardElement) {
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
                if (cardElement) {
                    cardElement.remove();
                    
                    // بررسی خالی بودن لیست
                    const cards = document.querySelectorAll('.product-card');
                    if (cards.length === 0) {
                        location.reload();
                    }
                }
                
                Shop.showToast(data.message, 'success');
            } else {
                Shop.showToast(data.message, 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Shop.showToast('خطا در ارتباط با سرور', 'danger');
        });
    },
    
    // تابع مقداردهی اولیه رویدادها
    initEventListeners: function() {
        document.addEventListener('click', function(e) {
            // دکمه‌های افزودن به سبد خرید
            if (e.target.closest('.add-to-cart')) {
                e.preventDefault();
                const button = e.target.closest('.add-to-cart');
                const productId = button.dataset.productId;
                const productName = button.dataset.productName || 'محصول';
                const stock = parseInt(button.dataset.stock) || 0;
                
                Shop.addToCart(productId, productName, stock, 1);
            }
            
            // دکمه‌های افزایش تعداد در سبد خرید
            if (e.target.closest('.increase-qty')) {
                e.preventDefault();
                const button = e.target.closest('.increase-qty');
                const row = button.closest('tr');
                const input = row.querySelector('.qty-input');
                const productId = row.dataset.productId;
                const maxQty = parseInt(input.max);
                let currentQty = parseInt(input.value);
                
                if (currentQty < maxQty) {
                    currentQty++;
                    Shop.updateCartItem(productId, currentQty, row);
                } else {
                    Shop.showToast('تعداد درخواستی از موجودی انبار بیشتر است', 'warning');
                }
            }
            
            // دکمه‌های کاهش تعداد در سبد خرید
            if (e.target.closest('.decrease-qty')) {
                e.preventDefault();
                const button = e.target.closest('.decrease-qty');
                const row = button.closest('tr');
                const input = row.querySelector('.qty-input');
                const productId = row.dataset.productId;
                let currentQty = parseInt(input.value);
                
                if (currentQty > 1) {
                    currentQty--;
                    Shop.updateCartItem(productId, currentQty, row);
                }
            }
            
            // دکمه‌های حذف از سبد خرید
            if (e.target.closest('.remove-from-cart')) {
                e.preventDefault();
                const button = e.target.closest('.remove-from-cart');
                const row = button.closest('tr');
                const productId = row.dataset.productId;
                
                if (confirm('آیا از حذف این محصول از سبد خرید مطمئن هستید؟')) {
                    Shop.updateCartItem(productId, 0, row, true);
                }
            }
            
            // دکمه‌های افزودن به علاقه‌مندی‌ها
            if (e.target.closest('.add-to-wishlist')) {
                e.preventDefault();
                const button = e.target.closest('.add-to-wishlist');
                const productId = button.dataset.productId;
                
                Shop.addToWishlist(productId, button);
            }
            
            // دکمه‌های حذف از علاقه‌مندی‌ها
            if (e.target.closest('.remove-from-wishlist')) {
                e.preventDefault();
                const button = e.target.closest('.remove-from-wishlist');
                const productId = button.dataset.productId;
                const card = button.closest('.card');
                
                if (confirm('آیا از حذف این محصول از لیست علاقه‌مندی‌ها مطمئن هستید؟')) {
                    Shop.removeFromWishlist(productId, card);
                }
            }
        });
    }
};

// مقداردهی اولیه پس از بارگذاری DOM
document.addEventListener('DOMContentLoaded', function() {
    Shop.initEventListeners();
});