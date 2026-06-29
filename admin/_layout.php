<?php
/**
 * admin/_layout.php - قالب صفحة الإداري
 * ==========================================
 * هاد الملف هو الهيكل الأساسي لصفحات لوحة التحكم (لوحة الإداري).
 * فيه دالتين رئيسيتين:
 *   1. admin_header() - تطبع رأس الصفحة، الشريط العلوي، والقائمة الجانبية.
 *   2. admin_footer() - تطبع تذييل الصفحة وتقفل الوسمات.
 *
 * أي صفحة في مجلد admin/ تبدأ باستدعاء admin_header() وتنتهي بـ admin_footer().
 * مثال:
 *   admin_header('العنوان');
 *   // محتوى الصفحة هنا
 *   admin_footer();
 */
require_once __DIR__ . '/../auth.php';      // نجيب ملف التحقق من تسجيل الدخول
require_admin();                             // نتأكد أن المستخدم مسجل دخوله وإلا يتحول لصفحة الدخول

/**
 * admin_header( $page_title )
 * ============================
 * هادي الدالة تطبع:
 *   - وسم DOCTYPE و html مع لغة عربية واتجاه RTL (يمين→يسار)
 *   - رأس <head> بالـ meta والـ title
 *   - حزمة JavaScript صغيرة تجيب الثيم المحفوظ (فاتح/داكن) من المتصفح قبل طلعة الصفحة عشان لا يومض
 *   - ملف CSS من assets/style.css
 *   - شريط علوي (topbar) فيه اسم الموقع + روابط الموقع العام وتسجيل الخروج + زر الثيم
 *   - قائمة جانبية (sidebar) فيها روابط: الرئيسية، الشركات، المناطق، الحجوزات + رجوع للموقع
 *   - وسم <main> يبدأ عشان المحتوى يجي بعده
 *
 * @param string $page_title  عنوان الصفحة (يظهر في التاب وفي رأس الصفحة)
 */
function admin_header($page_title = 'لوحة التحكم') {
    $title = $page_title . ' | لوحة الإداري';
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <!-- العنوان يظهر في تبويب المتصفح -->
      <title><?= h($title) ?> | Hevan Booking</title>
      <script>
        /* 
           قبل ما الصفحة تطلع، نشوف لو في ثيم محفوظ في localStorage
           ونضيفه لوسم <html> عشان ما نحصل وميض (flash) بين الفاتح والداكن.
        */
        (function () {
          try {
            var theme = localStorage.getItem('hevan-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
          } catch (e) {}
        })();
      </script>
      <!-- جلب ملف التنسيقات (CSS) من مجلد assets -->
      <link rel="stylesheet" href="<?= h(asset_href('style.css')) ?>">
    </head>
    <body>

    <!-- ========== الشريط العلوي (Topbar) ========== -->
    <div class="topbar">
      <div class="container">
        <div class="nav">
          <!-- اسم الموقع وشعاره (glob/career) -->
          <a href="<?= url('index.php') ?>" class="brand"><?= svg_icon('globe') ?> Hevan Booking</a>
          <nav>
            <a href="<?= url('index.php') ?>">الموقع العام</a>
            <a href="<?= url('logout.php') ?>">تسجيل خروج</a>
            <!-- زر تبديل الثيم (فاتح/داكن) -->
            <button id="themeToggle" class="theme-toggle" type="button" aria-label="تبديل الوضع">
              <?= svg_icon('moon', 'theme-icon-moon') ?>
              <?= svg_icon('sun', 'theme-icon-sun') ?>
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- ========== هيكل الصفحة الرئيسي مع السايدبار ========== -->
    <div class="container page">
      <div class="admin-layout">
        <!-- ===== القائمة الجانبية (Sidebar) ===== -->
        <aside class="sidebar">
          <h3 style="margin:0 0 12px">لوحة التحكم</h3>
          <!-- basename() تجيب اسم الملف الحالي عشان نعرف أي رابط ننشطه -->
          <a href="<?= url('admin/index.php') ?>" class="<?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php' ? 'active' : '' ?>"><?= svg_icon('chart') ?> الرئيسية</a>
          <a href="<?= url('admin/companies.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'companies') ? 'active' : '' ?>"><?= svg_icon('building') ?> الشركات</a>
          <a href="<?= url('admin/places.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'places') ? 'active' : '' ?>"><?= svg_icon('pin') ?> المناطق</a>
          <a href="<?= url('admin/bookings.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'bookings') ? 'active' : '' ?>"><?= svg_icon('booking') ?> الحجوزات</a>
          <!-- فاصل بسيط -->
          <hr style="margin:14px 0;border:none;border-top:1px solid var(--border)">
          <a href="<?= url('index.php') ?>" style="color:var(--primary)"><?= svg_icon('back') ?> رجوع للموقع</a>
        </aside>
        <!-- ===== المحتوى الرئيسي (يبدأ هنا ويكمل في الفوتر) ===== -->
        <main>
    <?php
}

/**
 * admin_footer()
 * ==============
 * هادي الدالة تطبع:
 *   - إغلاق وسم <main>
 *   - إغلاق div بتاع admin-layout و container.page
 *   - تذييل الصفحة (footer) مع حقوق النشر
 *   - حزمة JavaScript تتحكم في تبديل الثيم (فاتح/داكن) باستخدام localStorage
 *   - إغلاق وسمات </body> و </html>
 *
 * تنادَى في نهاية كل صفحة من صفحات admin/ بعد المحتوى.
 */
function admin_footer() {
    ?>
        </main>
      </div><!-- /.admin-layout -->
    </div><!-- /.container.page -->

    <!-- ========== تذييل الصفحة (Footer) ========== -->
    <div class="footer">
      <div class="container">
        <p>© <?= date('Y') ?> Hevan Booking - مشروع طلابي للمناقشة</p>
      </div>
    </div>

    <script>
    /*
     * هاد السكريبت يتولى تبديل الثيم بين الفاتح (light) والداكن (dark).
     * يحفظ الاختيار في localStorage عشان يفضل حتى لو المستخدم حدث الصفحة.
     * المبدأ:
     *   - نغير data-theme على وسم <html>
     *   - localStorage يتذكر الاختيار
     *   - زر التبديل transh بين الوضعين
     */
    (function () {
      var root = document.documentElement;
      var button = document.getElementById('themeToggle');
      var key = 'hevan-theme';
      function setTheme(theme) {
        root.setAttribute('data-theme', theme);
        try { localStorage.setItem(key, theme); } catch (e) {}
        if (button) button.setAttribute('aria-label', theme === 'dark' ? 'تفعيل الوضع الفاتح' : 'تفعيل الوضع الداكن');
      }
      setTheme(root.getAttribute('data-theme') || 'light');
      if (button) button.addEventListener('click', function () {
        setTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
      });
    })();
    </script>

    </body>
    </html>
    <?php
}
