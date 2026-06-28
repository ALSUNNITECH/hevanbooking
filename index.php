<?php
require_once __DIR__ . '/auth.php';
$title = 'الرئيسية';

// إحصائيات سريعة
$stats = [];
$stats['companies'] = (int)$conn->query("SELECT COUNT(*) AS c FROM companies")->fetch_assoc()['c'];
$stats['places']    = (int)$conn->query("SELECT COUNT(*) AS c FROM places")->fetch_assoc()['c'];
$stats['bookings']  = (int)$conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'];

// آخر المناطق والشركات
$places    = $conn->query("SELECT * FROM places ORDER BY id DESC LIMIT 6");
$companies = $conn->query("SELECT * FROM companies ORDER BY id DESC LIMIT 6");

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
  <div class="hero-content">
    <span class="eyebrow">✈️ اكتشف أجمل الوجهات السياحية</span>
    <h1>Hevan Booking</h1>
    <p class="hero-desc">
      احجز رحلتك القادمة بسهولة، اختر وجهتك المفضلة من بين أفضل الشركات السياحية.
    </p>

    <div class="hero-visual">
      <form class="hero-card" method="get" action="<?= url('book.php') ?>">
        <input type="text" name="q" placeholder="📝 عنوان الرحلة" aria-label="عنوان الرحلة">
        <input type="text" name="desc" placeholder="📝 وصف الرحلة" aria-label="وصف الرحلة">
        <select name="city" aria-label="اختر المدينة">
          <option value="">🏙️ اختر المدينة</option>
          <?php
          $cities = $conn->query("SELECT DISTINCT city FROM places WHERE is_active=1 ORDER BY city");
          while ($c = $cities->fetch_assoc()): ?>
            <option value="<?= h($c['city']) ?>"><?= h($c['city']) ?></option>
          <?php endwhile; ?>
        </select>
        <select name="company_id" aria-label="اختر الشركة">
          <option value="">🏢 اختر الشركة السياحية</option>
          <?php
          $companies_for_search = $conn->query("SELECT id, name FROM companies WHERE is_active=1 ORDER BY name");
          while ($c = $companies_for_search->fetch_assoc()): ?>
            <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
          <?php endwhile; ?>
        </select>
        <input type="date" name="check_in" aria-label="تاريخ الحجز">
        <button class="btn" type="submit">حدد واحجز ✨</button>
      </form>
    </div>
  </div>
</section>

<!-- إحصائيات -->
<div class="stats-row">
  <div class="stat-box">
    <span class="stat-icon">🏢</span>
    <strong><?= $stats['companies'] ?></strong>
    <span class="muted">الشركات</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon">📍</span>
    <strong><?= $stats['places'] ?></strong>
    <span class="muted">المناطق</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon">📋</span>
    <strong><?= $stats['bookings'] ?></strong>
    <span class="muted">الحجوزات</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon">📱</span>
    <strong>متجاوب</strong>
    <span class="muted">جوال ولابتوب</span>
  </div>
</div>

<!-- المناطق السياحية -->
<div id="places" class="section-title">
  <div>
    <h2>🏞️ المناطق السياحية</h2>
    <p class="muted">صور الوجهات تظهر تلقائياً من مجلد assets.</p>
  </div>
  <span class="badge">وجهات مميزة</span>
</div>

<div class="grid destination-grid">
  <?php while ($place = $places->fetch_assoc()): ?>
    <?php $images = place_image_urls($place); ?>
    <article class="card destination-card">
      <a class="card-img destination-cover" href="<?= url('book.php?place_id=' . (int)$place['id']) ?>">
        <img src="<?= h($images[0] ?? asset_url('hram.png')) ?>" alt="<?= h($place['name']) ?>">
        <span class="image-badge"><?= h($place['category']) ?></span>
      </a>
      <?php if (count($images) > 1): ?>
        <div class="thumb-row" aria-label="صور <?= h($place['name']) ?>">
          <?php foreach (array_slice($images, 1, 4) as $image): ?>
            <img src="<?= h($image) ?>" alt="<?= h($place['name']) ?>">
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="card-body">
        <h3><?= h($place['name']) ?></h3>
        <p class="muted">📍 <?= h($place['location']) ?></p>
        <p><?= h(text_excerpt($place['description'] ?? '', 95)) ?></p>
        <a class="btn small full-mobile" href="<?= url('book.php?place_id=' . (int)$place['id']) ?>">احجز لهذه المنطقة</a>
      </div>
    </article>
  <?php endwhile; ?>
</div>

<!-- الشركات السياحية -->
<div class="section-title">
  <div>
    <h2>🏢 الشركات السياحية</h2>
    <p class="muted">اختر شركة مناسبة وأرسل طلب حجزك مباشرة.</p>
  </div>
  <span class="badge">شركات معتمدة</span>
</div>

<div class="grid company-grid">
  <?php while ($company = $companies->fetch_assoc()): ?>
    <article class="card company-card">
      <?php if (!empty($company['image_url'])): ?>
        <div class="card-img"><img src="<?= h(asset_url($company['image_url'])) ?>" alt="<?= h($company['name']) ?>"></div>
      <?php else: ?>
        <div class="company-icon">🏢</div>
      <?php endif; ?>
      <div class="card-body">
        <h3><?= h($company['name']) ?></h3>
        <p class="muted">📞 <?= h($company['phone']) ?></p>
        <p class="muted">📍 <?= h($company['address']) ?></p>
        <p><?= h(text_excerpt($company['description'] ?? '', 100)) ?></p>
        <a class="btn small full-mobile" href="<?= url('book.php?company_id=' . (int)$company['id']) ?>">احجز مع الشركة</a>
      </div>
    </article>
  <?php endwhile; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
