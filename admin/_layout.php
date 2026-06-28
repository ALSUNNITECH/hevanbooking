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
      <link rel="stylesheet" href="<?= h(asset_href('style.css')) ?>">
    </head>
    <body>

    <div class="topbar">
      <div class="container">
        <div class="nav">
          <a href="<?= url('index.php') ?>" class="brand">🌍 Hevan Booking</a>
          <nav>
            <a href="<?= url('index.php') ?>">الموقع العام</a>
            <a href="<?= url('logout.php') ?>">تسجيل خروج</a>
          </nav>
        </div>
      </div>
    </div>

    <div class="container page">
      <div class="admin-layout">
        <aside class="sidebar">
          <h3 style="margin:0 0 12px">لوحة التحكم</h3>
          <a href="<?= url('admin/index.php') ?>" class="<?= basename($_SERVER['SCRIPT_NAME'] ?? '') === 'index.php' ? 'active' : '' ?>">📊 الرئيسية</a>
          <a href="<?= url('admin/companies.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'companies') ? 'active' : '' ?>">🏢 الشركات</a>
          <a href="<?= url('admin/places.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'places') ? 'active' : '' ?>">📍 المناطق</a>
          <a href="<?= url('admin/bookings.php') ?>" class="<?= str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'bookings') ? 'active' : '' ?>">📋 الحجوزات</a>
          <hr style="margin:14px 0;border:none;border-top:1px solid var(--border)">
          <a href="<?= url('index.php') ?>" style="color:var(--primary)">⬅ رجوع للموقع</a>
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

    </body>
    </html>
    <?php
}
