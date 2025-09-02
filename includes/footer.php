<?php
// جلوگیری از بارگذاری تکراری فوتر
if (!defined('FOOTER_LOADED')) {
    define('FOOTER_LOADED', true);
?>
            </main>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="bi bi-shop me-2"></i><?php echo $siteTitle; ?></h5>
                    <p class="mb-0">بهترین محصولات با بهترین کیفیت و قیمت مناسب</p>
                    <p class="mb-0">تجربه‌ای متفاوت از خرید آنلاین</p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-2" data-bs-toggle="tooltip" title="فیس‌بوک">
                            <i class="bi bi-facebook fs-5"></i>
                        </a>
                        <a href="#" class="text-white me-2" data-bs-toggle="tooltip" title="توییتر">
                            <i class="bi bi-twitter fs-5"></i>
                        </a>
                        <a href="#" class="text-white me-2" data-bs-toggle="tooltip" title="اینستاگرام">
                            <i class="bi bi-instagram fs-5"></i>
                        </a>
                        <a href="#" class="text-white me-2" data-bs-toggle="tooltip" title="تلگرام">
                            <i class="bi bi-telegram fs-5"></i>
                        </a>
                        <a href="#" class="text-white" data-bs-toggle="tooltip" title="واتس‌اپ">
                            <i class="bi bi-whatsapp fs-5"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <h5><i class="bi bi-link-45deg me-2"></i>لینک‌های مفید</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="about.php" class="text-white text-decoration-none d-flex align-items-center">
                                <i class="bi bi-chevron-left me-1"></i> درباره ما
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="contact.php" class="text-white text-decoration-none d-flex align-items-center">
                                <i class="bi bi-chevron-left me-1"></i> تماس با ما
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="terms.php" class="text-white text-decoration-none d-flex align-items-center">
                                <i class="bi bi-chevron-left me-1"></i> شرایط استفاده
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="privacy.php" class="text-white text-decoration-none d-flex align-items-center">
                                <i class="bi bi-chevron-left me-1"></i> حریم خصوصی
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="faq.php" class="text-white text-decoration-none d-flex align-items-center">
                                <i class="bi bi-chevron-left me-1"></i> سوالات متداول
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="delivery.php" class="text-white text-decoration-none d-flex align-items-center">
                                <i class="bi bi-chevron-left me-1"></i> اطلاعات ارسال
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5><i class="bi bi-info-circle me-2"></i>اطلاعات تماس</h5>
                    <?php
                    // استفاده از تابع mds_date برای نمایش تاریخ شمسی
                    if (function_exists('mds_date')) {
                        echo "<p class='mb-2'><i class='bi bi-calendar3 me-2'></i> امروز: " . mds_date("l، d F Y ساعت H:i", "now", 1) . "</p>";
                    } 
                    // اگر تابع mds_date وجود نداشت، بررسی وجود افزونه intl
                    elseif (extension_loaded('intl')) {
                        // تنظیم منطقه به فارسی ایران
                        $locale = 'fa_IR';
                        $formatter = new IntlDateFormatter(
                            $locale,
                            IntlDateFormatter::FULL,
                            IntlDateFormatter::SHORT,
                            'Asia/Tehran'
                        );
                        $formatter->setPattern('EEEE، d MMMM yyyy ساعت H:mm');
                        echo "<p class='mb-2'><i class='bi bi-calendar3 me-2'></i> " . $formatter->format(time()) . "</p>";
                    } 
                    // اگر هیچ‌کدام از موارد بالا وجود نداشت، از تاریخ میلادی استفاده کن
                    else {
                        echo "<p class='mb-2'><i class='bi bi-calendar3 me-2'></i> امروز: " . date('l، d F Y ساعت H:i') . "</p>";
                    }
                    ?>
                    <p class="mb-2"><i class="bi bi-geo-alt me-2"></i> تهران، خیابان آزادی، پلاک 123</p>
                    <p class="mb-2"><i class="bi bi-telephone me-2"></i> 021-12345678</p>
                    <p class="mb-2"><i class="bi bi-envelope me-2"></i> info@example.com</p>
                    <p class="mb-0"><i class="bi bi-clock me-2"></i> شنبه تا چهارشنبه: ۹ صبح تا ۸ شب</p>
                    <p class="mb-0"><i class="bi bi-clock me-2"></i> پنج‌شنبه و جمعه: ۱۰ صبح تا ۶ عصر</p>
                </div>
            </div>
            <hr class="my-3">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo $siteTitle; ?>. تمامی حقوق محفوظ است.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">طراحی و توسعه توسط 
                        <a href="#" class="text-white text-decoration-none" data-bs-toggle="tooltip" title="گروه برنامه نویسی رهنما">
                            <i class="bi bi-code-slash"></i> گروه برنامه نویسی رهنما
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo $basePath; ?>/assets/js/shop.js"></script>
    <script src="<?php echo $basePath; ?>/assets/js/main.js"></script>    
    <!-- Custom Script for Dropdowns and Hash Navigation -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // مقداردهی اولیه تمام dropdownها
        var dropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        dropdownTriggerList.map(function (dropdownTriggerEl) {
            return new bootstrap.Dropdown(dropdownTriggerEl);
        });
        
        // مقداردهی initial تمام popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // مقداردهی initial تمام tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // مدیریت تغییرات هش (URL)
        function handleHashChange() {
            // اگر هش تغییر کرد، تمام dropdownها را دوباره مقداردهی اولیه کن
            var dropdowns = document.querySelectorAll('.dropdown-menu.show');
            dropdowns.forEach(function(dropdown) {
                var dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.parentElement);
                if (dropdownInstance) {
                    dropdownInstance.hide();
                }
            });
            
            // پس از مخفی کردن dropdownها، آنها را دوباره مقداردهی اولیه کن
            setTimeout(function() {
                var newDropdownTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
                newDropdownTriggerList.map(function (dropdownTriggerEl) {
                    return new bootstrap.Dropdown(dropdownTriggerEl);
                });
            }, 100);
        }
        
        // شنونایی تغییرات هش
        window.addEventListener('hashchange', handleHashChange);
        
        // اجرای اولیه برای هش فعلی
        if (window.location.hash) {
            handleHashChange();
        }
    });
    
    $(function(){
        // Loading spinner
        $(document).ajaxStart(function() {
            $('#loading').removeClass('d-none');
        }).ajaxStop(function() {
            $('#loading').addClass('d-none');
        });
        
        // در بخش ارسال فرم افزودن محصول
        $('#addProductForm').on('submit', function(e){
            e.preventDefault();
            
            // اعتبارسنجی کد کالا (فقط اعداد و حروف)
            var productCode = $('input[name="product_code"]').val();
            if (!/^[a-zA-Z0-9]+$/.test(productCode)) {
                alert('کد کالا فقط می‌تواند شامل اعداد و حروف انگلیسی باشد.');
                return false;
            }
            
            // اعتبارسنجی قیمت (فقط اعداد)
            var price = $('input[name="price"]').val();
            if (!/^\d+$/.test(price)) {
                alert('قیمت فقط می‌تواند شامل اعداد باشد.');
                return false;
            }
            
            // اعتبارسنجی موجودی (فقط اعداد)
            var qty = $('input[name="qty"]').val();
            if (!/^\d+$/.test(qty)) {
                alert('موجودی فقط می‌تواند شامل اعداد باشد.');
                return false;
            }
            
            // اگر اعتبارسنجی با موفقیت انجام شد، ارسال درخواست
            var fd = new FormData(this);
            $.ajax({
                url: 'action_admin_products.php',
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                dataType: 'json'
            }).done(function(res){
                if (res && res.success) {
                    alert(res.message || 'محصول اضافه شد');
                    location.reload();
                } else {
                    alert('خطا: '+(res && res.message ? res.message : 'خطایی رخ داد'));
                }
            }).fail(function(jqXHR, textStatus, errorThrown){
                console.error('AJAX failed:', textStatus, errorThrown);
                alert('خطایی در ارسال درخواست پیش آمد.');
            });
        });
        
        // Open edit modal
        $(document).on('click', '.btn-edit', function(){
            var btn = $(this);
            $('#edit_id').val(btn.data('id'));
            $('#edit_product_code').val(btn.data('product_code'));
            $('#edit_name').val(btn.data('name'));
            $('#edit_qty').val(btn.data('qty'));
            $('#edit_price').val(btn.data('price'));
            $('#edit_details').val(btn.data('details'));
            $('#current_image').val(btn.data('image') || '');
            var m = new bootstrap.Modal(document.getElementById('editProductModal'));
            m.show();
        });
        
        // EDIT Product
        $('#editProductForm').on('submit', function(e){
            e.preventDefault();
            var fd = new FormData(this);
            $.ajax({
                url: 'action_admin_products.php?action=EDIT',
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                dataType: 'json'
            }).done(function(res){
                if (res && res.success) {
                    alert(res.message || 'ویرایش انجام شد');
                    location.reload();
                } else {
                    alert('خطا: '+(res && res.message ? res.message : 'خطایی رخ داد'));
                }
            }).fail(function(jqXHR, textStatus, errorThrown){
                console.error('AJAX failed:', textStatus, errorThrown);
                alert('خطایی در ارسال درخواست رخ داد.');
            });
        });
        
        // Image Modal
        $(document).on('click', '[data-bs-target="#imageModal"]', function() {
            const imageSrc = $(this).data('image');
            $('#modalImage').attr('src', imageSrc);
        });
    });

    </script>
</body>
</html>
<?php
} // پایان بررسی FOOTER_LOADED
?>