# فروشگاه ایرانیان | Iranian E-commerce

یک سیستم فروشگاه آنلاین کامل با قابلیت‌های مدیریت سفارشات، محصولات و کاربران.

## Table of Contents (فهرست مطالب)
- [ویژگی‌ها](#ویژگی‌ها)
- [نصب و راه‌اندازی](#نصب-و-راه‌اندازی)
- [استفاده](#استفاده)
- [تکنولوژی‌های استفاده شده](#تکنولوژی‌های-استفاده-شده)
- [مشارکت در توسعه](#مشارکت-در-توسعه)
- [لایسنس](#لایسنس)

## ویژگی‌ها

### مدیریت محصولات
- افزودن، ویرایش و حذف محصولات
- مدیریت موجودی انبار
- دسته‌بندی محصولات
- آپلود تصاویر محصولات

### مدیریت سفارشات
- نمایش لیست سفارشات با فیلتر و جستجو
- ویرایش کامل سفارش (افزودن/حذف/تغییر تعداد محصولات)
- تغییر وضعیت سفارش (در حال بررسی، در حال آماده‌سازی، ارسال شده، تحویل داده شده، لغو شده)
- ثبت کد پیگیری پستی
- اطلاع‌رسانی به مشتریان

### مدیریت کاربران
- ثبت نام و ورود کاربران
- پروفایل کاربری
- سطوح دسترسی (کاربر عادی، مدیر)

### سبد خرید
- افزودن و حذف محصولات از سبد خرید
- محاسبه خودکار قیمت کل
- پرداخت در محل و پرداخت آنلاین

### طراحی و رابط کاربری
- طراحی ریسپانسیو برای همه دستگاه‌ها
- رابط کاربری مدرن و کاربرپسند
- پشتیبانی از زبان فارسی

## نصب و راه‌اندازی

### پیش‌نیازها
- PHP 7.4 یا بالاتر
- MySQL 5.7 یا بالاتر
- وب سرور (Apache, Nginx, etc.)

### مراحل نصب
1. کلون کردن ریپازیتوری:
   ```bash
   git clone https://github.com/your-username/iranian-ecommerce.git
   cd iranian-ecommerce
   ```

2. ایجاد دیتابیس:
   - یک دیتابیس جدید در MySQL ایجاد کنید
   - فایل `iransianshop.sql` را در دیتابیس خود ایمپورت کنید

3. تنظیمات اتصال به دیتابیس:
   - فایل `includes/db_link.php` را باز کرده و اطلاعات اتصال به دیتابیس را وارد کنید:
   ```php
   $link = mysqli_connect("localhost", "username", "password", "database_name");
   ```

4. تنظیمات اولیه:
   - فایل `includes/init.php` را برای تنظیمات اولیه بررسی کنید
   - مسیر پروژه را در وب سرور خود تنظیم کنید

5. دسترسی به سیستم:
   - پروژه را در مرورگر خود باز کنید
   - برای ورود به بخش مدیریت، از حساب کاربری با سطح دسترسی "admin" استفاده کنید
   - رمز عبور نیز admin1234 می باشد

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
   - مشاهده لیست سفارشات
   - تغییر وضعیت سفارش
   - ویرایش محصولات سفارش داده شده
   - ثبت کد پیگیری پستی
4. مدیریت کاربران:
   - مشاهده لیست کاربران
   - تغییر سطح دسترسی کاربران

## تکنولوژی‌های استفاده شده

در این پروژه از [فونت وزیرمتن](https://rastikerdar.github.io/vazirmatn/) اثر زنده‌یاد صابر راستی‌کردار استفاده شده


### Backend
- PHP 7.4+
- MySQL
- JavaScript (ES6+)
- AJAX

### Frontend
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- jQuery

### ابزارها
- Git
- Composer (برای مدیریت وابستگی‌ها)

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

A complete e-commerce system with order management, product management, and user management features.

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Technologies Used](#technologies-used)
- [Contributing](#contributing)
- [License](#license)

## Features

### Product Management
- Add, edit, and delete products
- Inventory management
- Product categorization
- Product image upload

### Order Management
- Display order list with filters and search
- Full order editing (add/remove/change product quantities)
- Change order status (pending, processing, shipped, delivered, cancelled)
- Register postal tracking code
- Customer notification

### User Management
- User registration and login
- User profiles
- Access levels (regular user, admin)

### Shopping Cart
- Add and remove products from cart
- Automatic total price calculation
- Cash on delivery and online payment

### Design and User Interface
- Responsive design for all devices
- Modern and user-friendly interface
- Persian language support

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

### Installation Steps
1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/iranian-ecommerce.git
   cd iranian-ecommerce
   ```

2. Create the database:
   - Create a new database in MySQL
   - Import the `iranianshop.sql` file into your database

3. Configure database connection:
   - Open the `includes/db_link.php` file and enter your database connection details:
   ```php
   $link = mysqli_connect("localhost", "username", "password", "database_name");
   ```

4. Initial configuration:
   - Check the `includes/init.php` file for initial settings
   - Set up the project path in your web server

5. Access the system:
   - Open the project in your browser
   - To access the admin panel, use an account with "admin" access level
   - Admin password is admin1234

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
   - View order list
   - Change order status
   - Edit ordered products
   - Register postal tracking code
4. Manage users:
   - View user list
   - Change user access levels

## Technologies Used

### Backend
- PHP 7.4+
- MySQL
- JavaScript (ES6+)
- AJAX

### Frontend
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- jQuery

### Tools
- Git
- Composer (for dependency management)

## Contributing

We welcome contributions! If you'd like to contribute to this project:

1. Fork the repository
2. Create a new branch for your feature (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
