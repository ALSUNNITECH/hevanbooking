-- ============================================================
-- Hevan Booking Demo — قاعدة بيانات MySQL / MariaDB
-- مخصصة لـ XAMPP على Windows
-- ============================================================

-- ------------------------------------------------------------
-- 1. قاعدة البيانات
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS hevan_booking
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE hevan_booking;

-- ------------------------------------------------------------
-- 2. جدول المستخدمين (الإداريون فقط)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `full_name`   VARCHAR(150) NOT NULL,
  `username`    VARCHAR(50)  NOT NULL UNIQUE,
  `password`    VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed',
  `email`       VARCHAR(150) NOT NULL,
  `role`        ENUM('admin','manager') DEFAULT 'admin',
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. جدول الشركات السياحية
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `companies`;

CREATE TABLE `companies` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(150) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `email`        VARCHAR(150) DEFAULT NULL,
  `phone`        VARCHAR(30)  DEFAULT NULL,
  `address`      VARCHAR(255) DEFAULT NULL,
  `logo`         VARCHAR(255) DEFAULT NULL COMMENT 'مسار صورة الشعار',
  `is_active`    TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_companies_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. جدول الأماكن / الوجهات السياحية
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `places`;

CREATE TABLE `places` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `company_id`     INT UNSIGNED NOT NULL,
  `name`           VARCHAR(150) NOT NULL,
  `description`    TEXT DEFAULT NULL,
  `location`       VARCHAR(255) NOT NULL COMMENT 'موقع تقريبي / عنوان',
  `city`           VARCHAR(100) NOT NULL,
  `image`          VARCHAR(255) DEFAULT NULL COMMENT 'مسار صورة المكان',
  `price_per_night` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `max_guests`     INT UNSIGNED NOT NULL DEFAULT 2,
  `category`       VARCHAR(100) NOT NULL DEFAULT 'فندق',
  `rating`         DECIMAL(3,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `is_featured`    TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `is_active`      TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_places_company`
    FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_places_company` (`company_id`),
  INDEX `idx_places_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. جدول الحجوزات
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;

CREATE TABLE `bookings` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `place_id`       INT UNSIGNED NOT NULL,
  `customer_name`  VARCHAR(150) NOT NULL,
  `customer_email` VARCHAR(150) DEFAULT NULL,
  `customer_phone` VARCHAR(30)  NOT NULL,
  `company_name`   VARCHAR(150) NOT NULL COMMENT 'نسخة مطبوعة لسهولة العرض',
  `place_name`     VARCHAR(150) NOT NULL COMMENT 'نسخة مطبوعة لسهولة العرض',
  `check_in`       DATE NOT NULL,
  `check_out`      DATE NOT NULL,
  `guests`         INT UNSIGNED NOT NULL DEFAULT 1,
  `total_price`    DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `status`         ENUM('pending','accepted','rejected','cancelled')
                   NOT NULL DEFAULT 'pending',
  `notes`          TEXT DEFAULT NULL,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `chk_dates` CHECK (`check_out` > `check_in`),
  CONSTRAINT `fk_bookings_place`
    FOREIGN KEY (`place_id`) REFERENCES `places`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_bookings_status` (`status`),
  INDEX `idx_bookings_dates` (`check_in`,`check_out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. بيانات أولية — Seed Data
-- ------------------------------------------------------------

-- 6.1 المستخدم الإداري
INSERT INTO `users` (`full_name`, `username`, `password`, `email`, `role`)
VALUES (
  'مدير النظام',
  'admin',
  -- كلمة المرور: admin123
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin@hevan.com',
  'admin'
);

-- ------------------------------------------------------------
-- 6.2 الشركات
-- ------------------------------------------------------------
INSERT INTO `companies` (`name`, `description`, `email`, `phone`, `address`, `is_active`) VALUES
(
  'سياحة القدس',
  'شركة متخصصة في تنظيم الرحلات والزيارة للأماكن المقدسة في فلسطين والقدس الشريف، مع ضيافة عائلية عريقة.',
  'info@jerusalem-tours.ps',
  '+970-2-1234567',
  'شارع القدس - رام الله - فلسطين',
  1
),
(
  'شواطئ غزة للسياحة',
  'تقدم باقات سياحية شاطئية على ساحل بحر غزة، مع إقامة في شقق فندقية مطلة على البحر.',
  'contact@gaza-beach.ps',
  '+970-8-7654321',
  'الطابق 2 - عمارة السياح - غزة',
  1
),
(
  'جبال نابلس',
  'رحلات مشي وتسلق في جبال نابلس وزيارة قرى رومانية وأثرية في محافظة نابلس.',
  'hike@nablus-mountains.ps',
  '+970-9-5555555',
  'وسط المدينة - نابلس',
  1
),
(
  'أريحا للسياحة العلاجية',
  'باقات علاجية في عيون أريحا الحارة مع إقامة في منتجعات صغيرة وعائلات مضيفة.',
  'hello@jericho-spa.ps',
  '+970-2-2223333',
  'شارع 19 - أريحا',
  1
),
(
  'وادي الأردن',
  'رحلات نهرية وتخييم على ضفاف نهر الأردن مع تجربة سباحة في مياه البحر الميت.',
  'info@jordan-valley.ps',
  '+970-5-1234567',
  'وادي القلعة - أريحا',
  1
);

-- ------------------------------------------------------------
-- 6.3 الأماكن / الوجهات
-- ------------------------------------------------------------
INSERT INTO `places` (
  `company_id`, `name`, `description`, `location`, `city`, `price_per_night`,
  `max_guests`, `category`, `rating`, `is_featured`, `is_active`
) VALUES
-- القدس
(1, 'فندق القدس الوطني', 'فندق 4 نجوم قريب من البلدة القديمة، إفطار فلسطيني تقليدي.', 'قرب باب العامود', 'القدس', 180.00, 3, 'فندق', 4.70, 1, 1),
(1, 'شقة بيت المقدس', 'شقة عائلية مطلة على أسوار المدينة القديمة، غرفتين وصالة.', 'حي الشيخ جون', 'القدس', 120.00, 4, 'شقة', 4.50, 1, 1),
(1, 'دار الضيافة الصلاحية', 'بيت ضيافة تراثي من الحجر القديم، أجواء دافئة وضيافة فلسطينية أصيلة.', 'حي Potter', 'القدس', 150.00, 2, 'بيت ضيافة', 4.80, 1, 1),

-- غزة
(2, 'منتجع شاطئ غزة', 'منتجع 3 نجوم مباشر على شاطئ بحر غزة، مسبح وحديقة أطفال.', 'شاطئ المدينة', 'غزة', 200.00, 4, 'منتجع', 4.30, 1, 1),
(2, 'شاليه بحر غزة', 'شاليه قريب من الشاطئ، مناسب للعائلات الصغيرة، إطلالة جزئية على البحر.', 'شارع بحر', 'غزة', 160.00, 3, 'شاليه', 4.40, 1, 1),

-- نابلس
(3, 'مزرعة جبلية نابلس', 'إقامة في مزرعة بين الجبال مع إفطار زراعي طازج وجولات تسلق.', 'قرية عسيلين', 'نابلس', 90.00, 5, 'مزرعة', 4.60, 1, 1),
(3, 'بيت ضيافة جبل القومندي', 'بيت حجري قديم يعيد بناء تجربة الحياة الجبلية التقليدية في نابلس.', 'جبل712', 'نابلس', 100.00, 2, 'بيت ضيافة', 4.20, 0, 1),

-- أريحا
(4, 'فندق واحة أريحا', 'فندق صغير قرب عيون أريحا الحارة، مسبح مياه حارة وحمام عائلي.', 'طريق دير هزان', 'أريحا', 220.00, 2, 'فندق', 4.55, 1, 1),
(4, 'شاليه عيون الحارة', 'شاليه مكيف مع فناء داخلي، تجربة استرخاء قرب الينابيع الحارة.', 'حي النويعمة', 'أريحا', 170.00, 3, 'شاليه', 4.35, 0, 1),

-- البحر الميت
(5, 'منتجع وادي الأردن', 'منتجع 4 نجوم على شواطئ البحر الميت مع منتجع صحي وسبا.', 'شاطئ السياح', 'البحر الميت', 350.00, 3, 'منتجع صحي', 4.90, 1, 1),
(5, 'كابينة وادي الأردن', 'كابينة خشبية بسيطة بين الحدائق، مناسبة للشباب والمغامرين.', 'مركز التخييم', 'البحر الميت', 80.00, 2, 'تخييم', 4.10, 0, 1);

-- ------------------------------------------------------------
-- 6.4 الحجوزات (سيناريو عرض)
-- ------------------------------------------------------------
INSERT INTO `bookings` (
  `place_id`, `customer_name`, `customer_email`, `customer_phone`,
  `company_name`, `place_name`, `check_in`, `check_out`, `guests`, `total_price`, `status`, `notes`
) VALUES
-- حجوزات مقبولة
(1, 'محمد خالد', 'mohammad@example.com', '0599123456', 'سياحة القدس', 'فندق القدس الوطني', '2026-07-01', '2026-07-04', 2, 540.00, 'accepted', 'غرفة منطلين على المدينة القديمة'),
(1, 'سارة أحمد', 'sara@example.com', '0599234567', 'سياحة القدس', 'فندق القدس الوطني', '2026-08-05', '2026-08-08', 1, 540.00, 'accepted', 'طلب إفطار'),
(2, 'عمر حسن', 'omar@example.com', '0599345678', 'سياحة القدس', 'شقة بيت المقدس', '2026-07-10', '2026-07-12', 4, 240.00, 'accepted', NULL),

(4, 'ليلى محمود', 'layla@example.com', '0599456789', 'شواطئ غزة للسياحة', 'منتجع شاطئ غزة', '2026-07-15', '2026-07-18', 3, 600.00, 'accepted', 'غرفة بجوار المسبح'),

(3, 'يوسف عادل', 'yousef@example.com', '0599567890', 'جبال نابلس', 'مزرعة جبلية نابلس', '2026-08-01', '2026-08-03', 5, 270.00, 'accepted', 'باقة مع فطور'),

(9, 'ريم سعيد', 'reem@example.com', '0599678901', 'أريحا للسياحة العلاجية', 'فندق واحة أريحا', '2026-07-20', '2026-07-22', 2, 440.00, 'accepted', 'طلب حمام خاص'),

(10, 'فادي جابر', 'fadi@example.com', '0599789012', 'أريحا للسياحة العلاجية', 'شاليه عيون الحارة', '2026-08-10', '2026-08-12', 3, 340.00, 'accepted', NULL),

-- حجوزات بانتظار القبول
(5, 'نور الدين', 'nour@example.com', '0599890123', 'شواطئ غزة للسياحة', 'شاليه بحر غزة', '2026-07-25', '2026-07-27', 2, 320.00, 'pending', NULL),
(6, 'دانا خليل', 'dana@example.com', '0599901234', 'جبال نابلس', 'بيت ضيافة جبل القومندي', '2026-08-05', '2026-08-07', 2, 200.00, 'pending', 'باقة مشي'),
(7, 'تامر ربيع', 'tamer@example.com', '0599012345', 'أريحا للسياحة العلاجية', 'فندق واحة أريحا', '2026-08-12', '2026-08-14', 2, 440.00, 'pending', NULL),
(8, 'هدى مصطفى', 'huda@example.com', '0599123467', 'أريحا للسياحة العلاجية', 'شاليه عيون الحارة', '2026-08-15', '2026-08-17', 3, 340.00, 'pending', 'زيارة لعيون الحارة'),

-- حالات مختبرة
(11, 'أحمد مازن', 'ahmad@example.com', '0599234569', 'وادي الأردن', 'منتجع وادي الأردن', '2026-07-05', '2026-07-06', 2, 350.00, 'rejected', 'الطلب تأخر بعد اكتمال الحجز'),
(2, 'كريم سامي', 'karim@example.com', '0599345679', 'سياحة القدس', 'شقة بيت المقدس', '2026-06-20', '2026-06-22', 3, 240.00, 'cancelled', 'ألغى العميل الحجز');