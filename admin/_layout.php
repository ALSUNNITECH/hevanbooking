<?php
/**
 * admin/_layout.php - قالب صفحة الإداري
 */
require_once __DIR__ . '/../auth.php';
require_admin();

function admin_header($page_title = 'لوحة التحكم') {
    $title = $page_title . ' | لوحة الإداري';
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= h($title) ?> | Hevan Booking</title>
      <script>
        (function () {
          try {
            var theme = localStorage.getItem('hevan-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
          } catch (e) {}
        })();
      </script>
      <link rel="stylesheet" href="<?= h(asset_href('style.css')) ?>">
    </head>
    <body>

    <div class="topbar">
      <div class="container">
        <div class="nav">
          <a href="<?= url('index.php') ?>" class="brand"><?= svg_icon('globe') ?> Hevan Booking</a>
          <nav>
            <a href="<?= url('index.php') ?>">الموقع العام</a>
            <a href="<?= url('logout.php') ?>">تسجيل خروج</a>
            <button id="themeToggle" class="theme-toggle" type="button" aria-label="تبديل الوضع">
              <?= svg_icon('moon', 'theme-icon-moon') ?>
              <?= svg_icon('sun', 'theme-icon-sun') ?>
            </button>
          </nav>
        </div>
      </div>
    </div>

    <div class="container page">
      <div class="admin-layout">
        <aside class="sidebar">
          <h3 style="margin:0 0 12px">لوحة التحكم</h3>
          <a href="<?= url('admin/index.php') ?>" class="<?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php' ? 'active' : '' ?>"><?= svg_icon('chart') ?> الرئيسية</a>
          <a href="<?= url('admin/companies.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'companies') ? 'active' : '' ?>"><?= svg_icon('building') ?> الشركات</a>
          <a href="<?= url('admin/places.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'places') ? 'active' : '' ?>"><?= svg_icon('pin') ?> المناطق</a>
          <a href="<?= url('admin/bookings.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'bookings') ? 'active' : '' ?>"><?= svg_icon('booking') ?> الحجوزات</a>
          <hr style="margin:14px 0;border:none;border-top:1px solid var(--border)">
          <a href="<?= url('index.php') ?>" style="color:var(--primary)"><?= svg_icon('back') ?> رجوع للموقع</a>
        </aside>
        <main>
    <?php
}

function admin_footer() {
    ?>
        </main>
      </div><!-- /.admin-layout -->
    </div><!-- /.container.page -->

    <div class="footer">
      <div class="container">
        <p>© <?= date('Y') ?> Hevan Booking - مشروع طلابي للمناقشة</p>
      </div>
    </div>

    <script>
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
