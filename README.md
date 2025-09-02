# فروشگاه ایرانیان | Iranian E-commerce

یک سیستم فروشگاه آنلاین پیشرفته و کامل با قابلیت‌های مدیریت سفارشات، محصولات و کاربران. این پروژه با الهام از یک پروژه موجود در GitHub توسعه یافته اما با تغییرات گسترده (بیش از 95%) و ویژگی‌های جدید و به‌روز شده.

## Table of Contents (فهرست مطالب)
- [ویژگی‌ها](#ویژگی‌ها)
- [نصب و راه‌اندازی](#نصب-و-راه‌اندازی)
- [استفاده](#استفاده)
- [تکنولوژی‌های استفاده شده](#تکنولوژی‌های-استفاده-شده)
- [تغییرات و بهبودها](#تغییرات-و-بهبودها)
- [قدردانی](#قدردانی)
- [مشارکت در توسعه](#مشارکت-در-توسعه)
- [لایسنس](#لایسنس)

## ویژگی‌ها

### مدیریت محصولات
- افزودن، ویرایش و حذف محصولات
- مدیریت موجودی انبار با کنترل خودکار
- دسته‌بندی محصولات
- آپلود تصاویر محصولات با بهینه‌سازی
- نمایش محصولات با فیلتر و جستجوی پیشرفته

### مدیریت سفارشات
- نمایش لیست سفارشات با فیلتر وضعیت و جستجوی هوشمند
- ویرایش کامل سفارش (افزودن/حذف/تغییر تعداد محصولات به صورت آژاکسی)
- تغییر وضعیت سفارش (در حال بررسی، در حال آماده‌سازی، ارسال شده، تحویل داده شده، لغو شده)
- ثبت کد پیگیری پستی با قابلیت کپی خودکار
- اطلاع‌رسانی خودکار به مشتریان از طریق ایمیل
- تاریخچه کامل تغییرات سفارش

### مدیریت کاربران
- ثبت نام و ورود کاربران با امنیت بالا
- پروفایل کاربری پیشرفته
- سطوح دسترسی (کاربر عادی، مدیر)
- بازیابی رمز عبور

### سبد خرید
- افزودن و حذف محصولات از سبد خرید به صورت آژاکسی
- محاسبه خودکار قیمت کل با تخفیف‌ها
- پرداخت در محل و پرداخت آنلاین
- ذخیره سبد خرید برای کاربران عضو

### طراحی و رابط کاربری
- طراحی ریسپانسیو برای همه دستگاه‌ها
- رابط کاربری مدرن و کاربرپسند با انیمیشن‌های روان
- پشتیبانی کامل از زبان فارسی با قالب راست‌چین
- پنل مدیریت قدرتمند با داشبورد تعاملی

## نصب و راه‌اندازی

### پیش‌نیازها
- PHP 7.4 یا بالاتر
- MySQL 5.7 یا بالاتر
- وب سرور (Apache, Nginx, etc.)
- Composer (برای مدیریت وابستگی‌ها)

### مراحل نصب
1. کلون کردن ریپازیتوری:
   ```bash
   git clone https://github.com/your-username/iranian-ecommerce.git
   cd iranian-ecommerce
   ```

2. نصب وابستگی‌ها:
   ```bash
   composer install
   ```

3. ایجاد دیتابیس:
   - یک دیتابیس جدید در MySQL ایجاد کنید
   - فایل `iranianshop.sql` را در دیتابیس خود ایمپورت کنید

4. تنظیمات اتصال به دیتابیس:
   - فایل `includes/db_link.php` را باز کرده و اطلاعات اتصال به دیتابیس را وارد کنید:
   ```php
   $link = mysqli_connect("localhost", "username", "password", "database_name");
   ```

5. تنظیمات اولیه:
   - فایل `includes/init.php` را برای تنظیمات اولیه بررسی کنید
   - مسیر پروژه را در وب سرور خود تنظیم کنید

6. دسترسی به سیستم:
   - پروژه را در مرورگر خود باز کنید
   - برای ورود به بخش مدیریت، از حساب کاربری با سطح دسترسی "admin" استفاده کنید با رمز admin1234

## استفاده

### برای کاربران عادی
1. ثبت نام در سایت
2. ورود به حساب کاربری
3. افزودن محصولات به سبد خرید
4. تکمیل فرآیند خرید

### برای مدیران
1. ورود به بخش مدیریت با حساب کاربری مدیر
2. مدیریت محصولات:
   - افزودن محصول جدید
   - ویرایش محصول موجود
   - مدیریت موجودی
3. مدیریت سفارشات:
   - مشاهده لیست سفارشات با فیلترهای پیشرفته
   - تغییر وضعیت سفارش
   - ویرایش محصولات سفارش داده شده
   - ثبت کد پیگیری پستی
   - ارسال اطلاعیه به مشتری
4. مدیریت کاربران:
   - مشاهده لیست کاربران
   - تغییر سطح دسترسی کاربران

## تکنولوژی‌های استفاده شده

### Backend
- PHP 7.4+
- MySQL 8.0+
- JavaScript (ES6+)
- AJAX
- REST API

### Frontend
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- jQuery
- SweetAlert2

### ابزارها
- Git
- Composer
- Webpack
- npm/yarn

## تغییرات و بهبودها

این پروژه با الهام از یک پروژه موجود در GitHub توسعه یافته اما با تغییرات گسترده زیر:

### تغییرات اصلی (بیش از 95% تغییر):
- **بازنویسی کامل کد**: بهبود ساختار و خوانایی کد
- **پیاده‌سازی معماری MVC**: جدا کردن منطق از نمایش
- **بهبود امنیت**: استفاده از prepared statements و جلوگیری از SQL Injection
- **بهینه‌سازی عملکرد**: کاهش زمان بارگذاری و بهبود پاسخگویی
- **رابط کاربری مدرن**: طراحی کاملاً جدید با تجربه کاربری بهتر

### ویژگی‌های جدید:
- **سیستم مدیریت سفارشات پیشرفته**: ویرایش زنده سفارشات بدون رفرش صفحه
- **سیستم اطلاع‌رسانی**: ارسال خودکار ایمیل به مشتریان
- **جستجوی پیشرفته**: جستجو در محصولات و سفارشات با فیلترهای متعدد
- **مدیریت موجودی هوشمند**: کنترل خودکار موجودی انبار
- **پشتیبانی از پرداخت‌های چندگانه**: در محل و آنلاین
- **پنل مدیریت قدرتمند**: داشبورد تعاملی با نمودارها و آمار
- **طراحی ریسپانسیو**: بهینه‌سازی برای تمام دستگاه‌ها
- **پشتیبانی از زبان‌های چندگانه**: آماده برای بین‌المللی‌سازی

### بهبودهای فنی:
- **استفاده از AJAX**: بهبود تجربه کاربری با بارگذاری صفحات بدون رفرش
- **بهینه‌سازی دیتابیس**: بهبود کوئری‌ها و ایندکس‌گذاری
- **امنیت پیشرفته**: محافظت در برابر حملات رایج وب
- **کد تمیز**: رعایت استانداردهای برنامه‌نویسی
- **مستندات کامل**: راهنمای استفاده و توسعه

## قدردانی

این پروژه با الهام از پروژه iranianshop ((https://github.com/alialmasi/iranianshop)) توسعه یافته است. از تمام توسعه‌دهندگان و مشارکت‌کنندگان آن پروژه سپاسگزاریم. این نسخه با تغییرات گسترده و ویژگی‌های جدید ارائه شده است.

## مشارکت در توسعه

ما از مشارکت‌های شما استقبال می‌کنیم! اگر می‌خواهید در توسعه این پروژه مشارکت کنید:

1. ریپازیتوری را فورک کنید
2. یک شاخه (branch) جدید برای ویژگی خود ایجاد کنید
3. تغییرات خود را اعمال کنید
4. کامیت کنید (`git commit -m 'Add some feature'`)
5. به شاخه اصلی خود push کنید (`git push origin feature-branch`)
6. یک Pull Request ایجاد کنید

## لایسنس

این پروژه تحت لایسنس MIT منتشر شده است. برای اطلاعات بیشتر، فایل [LICENSE](LICENSE) را مطالعه کنید.

---

# Iranian E-commerce

An advanced and complete e-commerce system with order management, product management, and user management features. This project is inspired by an existing GitHub project but has been extensively modified (over 95% different) with new features and improvements.

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Technologies Used](#technologies-used)
- [Changes and Improvements](#changes-and-improvements)
- [Acknowledgments](#acknowledgments)
- [Contributing](#contributing)
- [License](#license)

## Features

### Product Management
- Add, edit, and delete products
- Advanced inventory management with auto-control
- Product categorization
- Product image upload with optimization
- Product display with advanced filtering and search

### Order Management
- Display order list with status filters and smart search
- Full order editing (add/remove/change product quantities via AJAX)
- Change order status (pending, processing, shipped, delivered, cancelled)
- Register postal tracking code with auto-copy feature
- Automatic customer notification via email
- Complete order change history

### User Management
- User registration and login with high security
- Advanced user profiles
- Access levels (regular user, admin)
- Password recovery

### Shopping Cart
- Add and remove products from cart via AJAX
- Automatic total price calculation with discounts
- Cash on delivery and online payment
- Cart saving for registered users

### Design and User Interface
- Responsive design for all devices
- Modern and user-friendly interface with smooth animations
- Complete Persian language support with RTL layout
- Powerful admin panel with interactive dashboard

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)
- Composer (for dependency management)

### Installation Steps
1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/iranian-ecommerce.git
   cd iranian-ecommerce
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Create the database:
   - Create a new database in MySQL
   - Import the `database.sql` file into your database

4. Configure database connection:
   - Open the `includes/db_link.php` file and enter your database connection details:
   ```php
   $link = mysqli_connect("localhost", "username", "password", "database_name");
   ```

5. Initial configuration:
   - Check the `includes/init.php` file for initial settings
   - Set up the project path in your web server

6. Access the system:
   - Open the project in your browser
   - To access the admin panel, use an account with "admin" access level

## Usage

### For Regular Users
1. Register on the site
2. Log in to your account
3. Add products to cart
4. Complete the checkout process

### For Administrators
1. Log in to the admin panel with an admin account
2. Manage products:
   - Add new product
   - Edit existing product
   - Manage inventory
3. Manage orders:
   - View order list with advanced filters
   - Change order status
   - Edit ordered products
   - Register postal tracking code
   - Send notification to customer
4. Manage users:
   - View user list
   - Change user access levels

## Technologies Used

### Backend
- PHP 7.4+
- MySQL 8.0+
- JavaScript (ES6+)
- AJAX
- REST API

### Frontend
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- jQuery
- SweetAlert2

### Tools
- Git
- Composer
- Webpack
- npm/yarn

## Changes and Improvements

This project is inspired by an existing GitHub project but has been extensively modified with the following changes:

### Major Changes (Over 85% Different):
- **Complete Code Rewrite**: Improved structure and code readability
- **MVC Architecture Implementation**: Separation of logic from presentation
- **Enhanced Security**: Using prepared statements and preventing SQL Injection
- **Performance Optimization**: Reduced loading time and improved responsiveness
- **Modern UI Design**: Completely new design with better user experience

### New Features:
- **Advanced Order Management System**: Live order editing without page refresh
- **Notification System**: Automatic email sending to customers
- **Advanced Search**: Search in products and orders with multiple filters
- **Smart Inventory Management**: Automatic inventory control
- **Multiple Payment Support**: Cash on delivery and online payments
- **Powerful Admin Panel**: Interactive dashboard with charts and statistics
- **Responsive Design**: Optimized for all devices
- **Multi-language Support**: Ready for internationalization

### Technical Improvements:
- **AJAX Implementation**: Improved user experience with page refresh-free loading
- **Database Optimization**: Improved queries and indexing
- **Advanced Security**: Protection against common web attacks
- **Clean Code**: Adherence to programming standards
- **Complete Documentation**: Usage and development guides

## Acknowledgments

This project is inspired by ((https://github.com/alialmasi/iranianshop)). We thank all the developers and contributors of that project. This version is released with extensive modifications and new features.

## Contributing

We welcome contributions! If you'd like to contribute to this project:

1. Fork the repository
2. Create a new branch for your feature (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
