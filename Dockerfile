# ============================================================
# Dockerfile بتاع Hevan Booking — يشرحلك النظام كامل
# ============================================================

# بنستخدم الصورة الأساسية: PHP 8.2 مع Apache جاهزين
FROM php:8.2-apache

# نركب إضافات MySQL عشان نقدر نركّب قاعدة البيانات
# و نشغل مود rewrite بتاع Apache عشان الـ URLs الحلوة
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# نحدد مجلد الشغل اللي Apache بيشتغل منه
WORKDIR /var/www/html

# ننسخ مجلد dev كله إلى جوا الـ container مكان الشغل
COPY dev/ /var/www/html/

# نغير المالك (owner) لكل الملفات يكون www-data
# عشان Apache يقدر يقرا ويكتب فيها
RUN chown -R www-data:www-data /var/www/html

# متغيرات البيئة — دي معلومات الداتابيز اللي بنستخدمها
# DB_HOST = اسم سيرفر قاعدة البيانات (db هو اسم service الـ MySQL)
# DB_USER = اسم المستخدم بتاع الداتابيز
# DB_PASS = كلمة السر بتاعت المستخدم
# DB_NAME = اسم قاعدة البيانات
ENV DB_HOST=db \
    DB_USER=hevan \
    DB_PASS=hevanpass \
    DB_NAME=hevan_booking

# نفتح بورت 80 عشان نقدر ندخل التطبيق من المتصفح
EXPOSE 80
