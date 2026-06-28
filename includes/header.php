<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($title ?? 'نظام الحجوزات السياحية') ?> | Hevan Booking</title>
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
      <a href="<?= url('index.php') ?>" class="brand">
      <img class="brand-logo" src="<?= asset_url('logo/logoicon1.png') ?>" alt="Hevan Booking" onerror="this.style.display='none'" width="40" height="40">
      <?= svg_icon('globe') ?> Hevan Booking
    </a>
      <nav>
        <a href="<?= url('index.php') ?>">الرئيسية</a>
        <a href="<?= url('book.php') ?>">حجز جديد</a>
        <?php if (is_admin()): ?>
          <a href="<?= url('admin/index.php') ?>">لوحة التحكم</a>
          <a href="<?= url('logout.php') ?>">تسجيل خروج</a>
        <?php else: ?>
          <a href="<?= url('login.php') ?>">دخول الإداري</a>
        <?php endif; ?>
        <button id="themeToggle" class="theme-toggle" type="button" aria-label="تبديل الوضع">
          <?= svg_icon('moon', 'theme-icon-moon') ?>
          <?= svg_icon('sun', 'theme-icon-sun') ?>
        </button>
      </nav>
    </div>
  </div>
</div>

<div class="container page">
