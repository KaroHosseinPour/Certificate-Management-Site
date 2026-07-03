# Certificate-Management-Site
یک سیستم حرفه‌ای مدیریت گواهی‌ها با PHP و MySQL شامل پنل ادمین، تولید کد رهگیری، جستجوی پیشرفته و ثبت لاگ بازدید برای هر گواهی

# 📜 سیستم مدیریت گواهی‌ها

یک سیستم ساده و حرفه‌ای برای مدیریت گواهی‌ها، ثبت اطلاعات، جستجو و بررسی صحت گواهی‌ها.

---

## 🚀 امکانات پروژه

- 🔐 سیستم ورود و مدیریت ادمین
- 📄 افزودن، ویرایش و حذف گواهی‌ها
- 🔎 جستجوی گواهی‌های صادره بر اساس نام
- 🧾 تولید خودکار کد رهگیری برای هر گواهی
- 📊 مدیریت وضعیت گواهی (معتبر / غیرفعال)
- 🖼 آپلود تصویر گواهی
- 📌 ثبت بازدید هر گواهی (لاگ بازدید)
- 📱 طراحی ریسپانسیو

---

## 🛠 تکنولوژی‌ها

- PHP
- MySQL
- HTML / CSS
- Bootstrap 5
- JavaScript

---

## 📂 ساختار دیتابیس

این سیستم شامل 3 جدول اصلی است:

- users → مدیریت ادمین‌ها
- certificates → اطلاعات گواهی‌ها
- certificate_views → ثبت بازدیدها

---

## ⚙️ نصب و راه‌اندازی

1. ابتدا تمامی فایل‌ها را در هاست خود بارگذاری کنید. <br>
اگر در پوشه اصلی وب سایت فایل ها را اپلود کنید، مسیرها به شرح ذیل است. <br>
Main Page : YourWebsite.YourDomin <br>
Admin Page : YourWebsite.YourDomin/admin/login.php <br>
در صورتی که فایل ها را داخل پوشه مشخصی بارگذاری کنید مسیر ها به شرح ذیل است <br>
Main Page : YourWebsite.YourDomin/YourFolder <br>
Admin Page : YourWebsite.YourDomin/YourFolder/admin/login.php <br>


2. داخل پنل مدیریتی هاست خود، دیتابیس اختصاصی را بسازید
 

3. اطلاعات پوشه Config.php را با توجه به اطلاعات دیتابیسی که تازگی ساختید تکمیل کنید. فیلدهای نام دیتابیس، یوزر نیم دیتابیس و پسورد دیتابیس اجباری است


4. از طریق پی‌اچ‌پی ادمین، وارد دیتابیس هاست خود شده و دیتابیس ساخته شده را انتخاب کنید. داخل بخش SQL کدهای زیر را وارد کنید تا جدول های خواسته شده ساخته شود
```SQL
CREATE DATABASE IF NOT EXISTS certificates_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE certificates_system;

-- =========================
-- USERS TABLE
-- =========================
CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) DEFAULT NULL,
    username VARCHAR(100) DEFAULT NULL UNIQUE,
    password VARCHAR(255) DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- CERTIFICATES TABLE
-- =========================
CREATE TABLE certificates (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    tracking_code VARCHAR(80) DEFAULT NULL,
    recipient_name VARCHAR(255) NOT NULL,
    certificate_type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    issuer_name VARCHAR(255) NOT NULL,
    issue_date DATE NOT NULL,
    status ENUM('valid','revoked') DEFAULT 'valid',
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- CERTIFICATE VIEWS TABLE
-- =========================
CREATE TABLE certificate_views (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    certificate_id INT(11) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
پس از وارد کردن کدها، دکمه Go را بزنید


5. وارد صفحه ورود مدیر شوید و نام کاربری و رمز عبور خود را تعیین کنید تا کاربری مورد نظر ساخته شود

---

## 🔑 ورود اولیه

پس از نصب، در ابتدا برای اولین بار با باز کردن صفحه لاگین در پوشه ادمین، فرم ایجاد یوزر ادمین را مشاهده می‌کنید. پس از ساخت یوزر ادمین، برای دفعات بعد از همان نام کاربری و رمز عبور از قبل ساخته شده برای ورود به داشبورد مدیریتی استفاده کنید.

---

## 📸 تصاویر محیط

> ![Main Page](Certificate%20WebSite/Pic/Certificate.jpg)
> ![Login](Certificate%20WebSite/Pic/Login.jpg)
> ![Dashboard](Certificate%20WebSite/Pic/Dashboard.jpg)
> ![Dashboard](Certificate%20WebSite/Pic/Dashboard2.jpg)
> ![views Report](Certificate%20WebSite/Pic/viewe%20Report.jpg)
> ![Edit Certificate](Certificate%20WebSite/Pic/Edit.jpg)
> ![Check Certificate](Certificate%20WebSite/Pic/Certificate%20Check.jpg)

---

## 👨‍💻 توسعه‌دهنده

- نام : کارو حسین ‌پور  

---

## ⚠️ نکات مهم

- پوشه آپلود باید دسترسی نوشتن داشته باشد
- حتماً از utf8mb4 استفاده شود
- نسخه پیشنهادی PHP: 8+

---

## ⭐ برنامه‌های آینده

- تولید QR Code
- خروجی PDF
- چندسطحی کردن دسترسی‌ها
- رابط کاربری پیشرفته‌تر

---

## 📄 لایسنس

این پروژه برای استفاده آموزشی و شخصی رایگان است.  
برای استفاده تجاری، هماهنگی لازم است.
