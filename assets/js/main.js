document.addEventListener('DOMContentLoaded', function () {
    // منوی همبرگر
    const hamburger = document.querySelector('.hamburger');
    const nav = document.querySelector('.nav');
    
    if (hamburger && nav) {
        hamburger.addEventListener('click', function () {
            nav.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }
    
    // بستن منو هنگام کلیک روی لینک‌ها (در موبایل)
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (nav.classList.contains('active')) {
                nav.classList.remove('active');
                hamburger.classList.remove('active');
            }
        });
    });
    
    // مدیریت پیام‌های فلش (Flash Messages)
    const flashMessages = document.querySelectorAll('.flash, .alert');
    
    // تابع برای حذف پیام فلش
    const removeFlashMessage = (flash) => {
        flash.classList.add('fade-out');
        setTimeout(() => {
            if (flash.parentNode) {
                flash.remove();
            }
        }, 300);
    };


}    
    // مدیریت کلیک روی دکمه بستن پیام
    document.addEventListener('click', function (e) {
        if (e.target.matches('.flash-close, .btn-close')) {
            const flash = e.target.closest('.flash, .alert');
            if (flash) {
                removeFlashMessage(flash);
            }
        }
    });
    
    // مدیریت خودکار پیام‌ها
    flashMessages.forEach(function (flash) {
        // اگر کلاس no-auto-dismiss وجود داشته باشد، نادیده بگیر
        if (flash.classList.contains('no-auto-dismiss') || flash.classList.contains('alert-permanent')) {
            return;
        }
        
        let timeoutId;
        
        // تابع برای حذف پیام
        const dismiss = () => {
            if (!flash || !flash.parentNode) return;
            removeFlashMessage(flash);
        };
        
        // تابع برای شروع تایمر
        const startTimer = () => { 
            timeoutId = setTimeout(dismiss, 6000); 
        };
        
        // تابع برای متوقف کردن تایمر
        const stopTimer = () => { 
            clearTimeout(timeoutId); 
        };
        
        // شروع تایمر
        startTimer();
        
        // متوقف کردن تایمر در صورت هاور کردن روی پیام
        flash.addEventListener('mouseenter', stopTimer);
        flash.addEventListener('mouseleave', startTimer);
    });
    
    // مدیریت فرم‌ها - جلوگیری از ارسال چندباره
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitButton = form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> در حال ارسال...';
                
                // در صورت خطا، دکمه را فعال کن
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = submitButton.getAttribute('data-original-text') || 'ارسال';
                }, 5000);
            }
        });
    });
    
    // ذخیره متن اصلی دکمه‌های فرم
    const submitButtons = document.querySelectorAll('[type="submit"]');
    submitButtons.forEach(button => {
        if (!button.hasAttribute('data-original-text')) {
            button.setAttribute('data-original-text', button.innerHTML);
        }
    });
    
    // مدیریت نمایش/مخفی کردن رمز عبور
    const togglePasswordButtons = document.querySelectorAll('[data-toggle="password"]');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordField = document.querySelector(targetId);
            
            if (passwordField) {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // تغییر آیکون
                const icon = this.querySelector('i');
                if (icon) {
                    if (type === 'password') {
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    } else {
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    }
                }
            }
        });
    });
    
    // افزودن کلاس‌های انیمیشن به عناصر هنگام اسکرول
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    animatedElements.forEach(element => {
        observer.observe(element);
    });
    
    // مدیریت تولتیپ‌ها
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // مدیریت پاپ‌اورها
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // اسکرول نرم به لینک‌های داخلی
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // دکمه بازگشت به بالا
    const backToTopButton = document.createElement('button');
    backToTopButton.innerHTML = '<i class="bi bi-arrow-up"></i>';
    backToTopButton.className = 'btn btn-primary position-fixed';
    backToTopButton.style.cssText = 'bottom: 20px; left: 20px; z-index: 1000; display: none; width: 50px; height: 50px; border-radius: 50%;';
    document.body.appendChild(backToTopButton);
    
    backToTopButton.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // نمایش/مخفی کردن دکمه بازگشت به بالا بر اساس اسکرول
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
    
    // اعتبارسنجی ساده فرم‌ها
    const requiredFields = document.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('invalid', function() {
            this.classList.add('is-invalid');
        });
        
        field.addEventListener('input', function() {
            if (this.validity.valid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    // بررسی وجود کتابخانه Bootstrap
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap.js بارگذاری نشده است.');
        return;
    }
    
    // مقداردهی اولیه تمام dropdownها
    var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    dropdownTriggerList.map(function (dropdownTriggerEl) {
        return new bootstrap.Dropdown(dropdownTriggerEl);
    });
    
    // مقداردهی اولیه تمام popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // مقداردهی initial تمام tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
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
            .then(response => response.json())
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
                    
                    showToast(`${productName} به سبد خرید اضافه شد`, 'success');
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // تغییر آیکون دکمه
                    this.innerHTML = '<i class="bi bi-heart-fill"></i>';
                    this.classList.add('text-danger');
                    
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'info');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('خطا در ارتباط با سرور', 'danger');
            });
        });
    });
});

// توابع کمکی
const utils = {
    // نمایش پیام به کاربر
    showAlert: function(message, type = 'info', duration = 5000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // اضافه کردن به بالای صفحه
        const container = document.querySelector('.container') || document.body;
        container.insertBefore(alertDiv, container.firstChild);
        
        // حذف خودکار پس از مدت زمان مشخص
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 300);
        }, duration);
    },
    
    // کپی کردن متن به کلیپ‌بورد
    copyToClipboard: function(text) {
        navigator.clipboard.writeText(text).then(() => {
            utils.showAlert('متن با موفقیت کپی شد!', 'success');
        }).catch(err => {
            console.error('خطا در کپی کردن متن: ', err);
            utils.showAlert('خطا در کپی کردن متن', 'danger');
        });
    },
    
    // فرمت‌سازی قیمت
    formatPrice: function(price) {
        return new Intl.NumberFormat('fa-IR').format(price);
    },
    
    // فرمت‌سازی تاریخ
    formatDate: function(date) {
        return new Intl.DateTimeFormat('fa-IR', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        }).format(new Date(date));
    }
};

// در دسترس قرار دادن توابع به صورت عمومی
if (typeof window !== 'undefined') {
    window.utils = utils;
}

// تابع نمایش Toast
function showToast(message, type = 'info') {
    // ایجاد container برای toast اگر وجود نداشته باشد
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // ایجاد toast
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // اضافه کردن toast به container
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // نمایش toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 5000
    });
    toast.show();
    
    // حذف toast از DOM پس از مخفی شدن
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}

// تابع فرمت‌سازی قیمت
function toman(price, showCurrency = false) {
    return price.toLocaleString('fa-IR') + (showCurrency ? ' تومان' : '');
}

// تابع حذف کاربر
function deleteUser(userId, username, userType = 'normal') {
    // بررسی اینکه آیا کاربر مدیر است یا نه
    if (userType === 'admin' || userType === 1) {
        showToast('مدیر سیستم قابل حذف نیست.', 'warning');
        return;
    }
    
    // استفاده از modal بوت‌استرپ به جای confirm
    const modalHtml = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تأیید حذف کاربر</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>آیا از حذف کاربر <strong>${username}</strong> مطمئن هستید؟</p>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            این عملیات غیرقابل بازگشت است.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">حذف کاربر</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // اضافه کردن modal به body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // نمایش modal
    const modalElement = document.getElementById('deleteConfirmModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // رویداد دکمه تأیید حذف
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        // غیرفعال کردن دکمه برای جلوگیری از کلیک‌های مکرر
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> در حال حذف...';
        
        // ارسال درخواست حذف
        fetch(`admin_delete_user.php?id=${userId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('خطا در ارتباط با سرور');
                }
                return response.json();
            })
            .then(data => {
                // بستن modal
                modal.hide();
                
                // نمایش پیام نتیجه
                if (data.success) {
                    showToast(data.message, 'success');
                    // هدایت به صفحه مدیریت کاربران پس از 1.5 ثانیه
                    setTimeout(() => {
                        window.location.href = data.redirect || 'admin_users.php';
                    }, 1500);
                } else {
                    showToast(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.', 'danger');
            })
            .finally(() => {
                // حذف modal از DOM پس از بسته شدن
                modalElement.addEventListener('hidden.bs.modal', function () {
                    modalElement.remove();
                });
            });
    });
}

// در دسترس قرار دادن توابع به صورت عمومی
if (typeof window !== 'undefined') {
    window.showToast = showToast;
    window.toman = toman;
    window.deleteUser = deleteUser;
}
