<?php
// ============================================================
// [ملف: index.php - الصفحة الرئيسية]
// ------------------------------------------------------------
// الشنو: الصفحة الرئيسية للموقع التجريبي Hevan Booking. 
// فيها عرض ترحيبي (هيرو)، إحصائيات سريعة، آخر المناطق السياحية، 
// وآخر الشركات المسجلة.
//
// كيف تشتغل: 
// 1. تبدأ بتحميل auth.php اللي بيفتح اتصال قاعدة البيانات 
//    ويسوي check للمستخدم.
// 2. تسوي استعلامات (queries) عشان تجيب عدد الشركات، المناطق، 
//    والحجوزات من قاعدة البيانات.
// 3. تجيب آخر 6 مناطق وآخر 6 شركات عشان تعرضهم في بطاقات.
// 4. تحمل includes/header.php اللي فيه فتحة <head> والقائمة.
// 5. بعد كدا تجي أقسام HTML: الهيرو، الإحصائيات، المناطق، الشركات.
// 6. أخيراً تحمل includes/footer.php عشان يقفل الصفحة.
//
// ليه موجودة: 
// عشان الزوار يشوفوا محتوى الموقع أول ما يدخلوا، 
// ويدهم فرصة يتصفحوا المناطق والشركات بدون ما يسجلوا دخول.
// ============================================================

require_once __DIR__ . '/auth.php';
$title = 'الرئيسية';

// ------------------------------------------------------------
// [إحصائيات سريعة]
// نعمل 3 استعلامات COUNT عشان نجيب عدد الشركات، المناطق، 
// والحجوزات من قاعدة البيانات. الأرقام دي نستخدمها في كذا مكان 
// في الصفحة (الهيرو كارد + صف الإحصائيات).
// ------------------------------------------------------------
$stats = [];
$stats['companies'] = (int)$conn->query("SELECT COUNT(*) AS c FROM companies")->fetch_assoc()['c'];
$stats['places']    = (int)$conn->query("SELECT COUNT(*) AS c FROM places")->fetch_assoc()['c'];
$stats['bookings']  = (int)$conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'];

// ------------------------------------------------------------
// [جلب آخر البيانات]
// نجيب آخر 6 مناطق وآخر 6 شركات (مرتبين حسب id تنازلياً)
// عشان نعرضهم في بطاقات تحت في الصفحة.
// ------------------------------------------------------------
$places    = $conn->query("SELECT * FROM places ORDER BY id DESC LIMIT 6");
$companies = $conn->query("SELECT * FROM companies ORDER BY id DESC LIMIT 6");

// ------------------------------------------------------------
// [تحميل رأس الصفحة]
// header.php فيه <!DOCTYPE html>، <head>، وبداية <body> 
// مع القائمة العلوية (navbar).
// ------------------------------------------------------------
include __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     [قسم الهيرو - Hero Section]
     الشنو: أول قسم يشوفه الزائر. فيه:
     - شعار ونص ترحيبي (eyebrow + عنوان + وصف).
     - 3 أزرار: احجز الآن، تصفح الوجهات، دخول الإداري.
     - فورم بحث سريع: الزائر يدخل كلمة أو يختار شركة ويضغط بحث.
     - صورة توضيحية مع كارد صغير فيه الإحصائيات (float).
     
     كيف تشتغل: 
     - svg_icon() ترجع SVG inline بدل الإيموجي.
     - url() تسوي رابط نسبي صحيح.
     - فورم البحث يودّي المستخدم لـ book.php مع query params.
     
     ليه موجود: 
     عشان يعطي انطباع أول حلو ويحفز الزوار يتصفحوا 
     أو يحجزوا مباشرة.
     ============================================================ -->
<section class="hero">
  <div class="hero-content">
    <span class="eyebrow">اكتشف السودان مع Hevan Booking</span>
    <h1>نظام إدارة الحجوزات السياحية</h1>
    <p class="hero-desc">
      منصة تجريبية أنيقة لإدارة الشركات السياحية، عرض الوجهات بالصور،
      وتسجيل الحجوزات بسرعة من الجوال أو اللابتوب.
    </p>
    <div class="actions">
      <a class="btn" href="<?= url('book.php') ?>"><?= svg_icon('booking') ?> احجز الآن</a>
      <a class="btn glass" href="#places"><?= svg_icon('landscape') ?> تصفح الوجهات</a>
      <a class="btn secondary" href="<?= url('login.php') ?>"><?= svg_icon('lock') ?> دخول الإداري</a>
    </div>

    <!-- --------------------------------------------------------
         [فورم البحث السريع]
         الزائر يكتب كلمة بحث أو يختار شركة من القائمة المنسدلة،
         ويضغط بحث. البيانات ترسل عبر GET لـ book.php.
         القائمة المنسدلة تتعبى من قاعدة البيانات (كل الشركات).
         -------------------------------------------------------- -->
    <form class="hero-search" method="get" action="<?= url('book.php') ?>">
      <input type="text" name="q" placeholder="ابحث عن وجهة أو شركة..." aria-label="بحث">
      <select name="company_id" aria-label="اختر شركة">
        <option value="">كل الشركات</option>
        <?php
        $companies_for_search = $conn->query("SELECT id, name FROM companies ORDER BY name");
        while ($c = $companies_for_search->fetch_assoc()): ?>
          <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
        <?php endwhile; ?>
      </select>
      <button class="btn accent" type="submit">بحث</button>
    </form>
  </div>

  <!-- --------------------------------------------------------
       [الجانب البصري - Hero Visual]
       صورة رئيسية (hram.png) + كارد صغير عائم فيه الأرقام:
       عدد الحجوزات، الشركات، المناطق.
       -------------------------------------------------------- -->
  <div class="hero-visual">
    <img src="<?= asset_url('hram.png') ?>" alt="Hevan Booking">
    <div class="hero-card floating">
      <div class="hero-stat">
        <strong><?= $stats['bookings'] ?></strong>
        <span>حجز مسجل</span>
      </div>
      <div class="hero-stat">
        <strong><?= $stats['companies'] ?></strong>
        <span>شركة سياحية</span>
      </div>
      <div class="hero-stat wide">
        <strong><?= $stats['places'] ?></strong>
        <span>منطقة سياحية جاهزة للعرض</span>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     [صف الإحصائيات - Stats Row]
     4 مربعات (stat-box) كل واحد يعرض:
     - أيقونة SVG
     - رقم الإحصائية
     - تسمية (الشركات، المناطق، الحجوزات)
     المربع الرابع ثابت (متجاوب - جوال ولابتوب).
     ليه موجود: عشان الزائر يشوف الأرقام بارزة ويعرف 
     حجم المحتوى في الموقع.
     ============================================================ -->
<div class="stats-row">
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('building') ?></span>
    <strong><?= $stats['companies'] ?></strong>
    <span class="muted">الشركات</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('pin') ?></span>
    <strong><?= $stats['places'] ?></strong>
    <span class="muted">المناطق</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('booking') ?></span>
    <strong><?= $stats['bookings'] ?></strong>
    <span class="muted">الحجوزات</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('mobile') ?></span>
    <strong>متجاوب</strong>
    <span class="muted">جوال ولابتوب</span>
  </div>
</div>

<!-- ============================================================
     [قسم المناطق السياحية]
     عنوان القسم + badge أحمر "وجهات مميزة".
     تحت: grid فيه بطاقات كل بطاقة تمثل منطقة سياحية.
     ليه موجود: عشان يعرض أحدث 6 مناطق مسجلة في قاعدة البيانات،
     كل واحدة مع صورتها ووصفها وزر الحجز.
     ============================================================ -->
<div id="places" class="section-title">
  <div>
    <h2><?= svg_icon('landscape') ?> المناطق السياحية</h2>
    <p class="muted">صور الوجهات تظهر تلقائياً من مجلد assets.</p>
  </div>
  <span class="badge">وجهات مميزة</span>
</div>

<div class="grid destination-grid">
  <?php while ($place = $places->fetch_assoc()): ?>
    <?php $images = place_image_urls($place); ?>
    <!-- --------------------------------------------------------
         [بطاقة منطقة سياحية]
         - صورة الغلاف (أول صورة من array images).
         - badge فيها تصنيف المنطقة (category).
         - إذا في صور إضافية (>1) نعرض صف مصغرات (thumb-row).
         - اسم المنطقة، الموقع، وصف مختصر.
         - رابط "احجز لهذه المنطقة" يودي لـ book.php مع place_id.
         -------------------------------------------------------- -->
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
        <p class="muted"><?= svg_icon('pin') ?> <?= h($place['location']) ?></p>
        <p><?= h(text_excerpt($place['description'] ?? '', 95)) ?></p>
        <a class="btn small full-mobile" href="<?= url('book.php?place_id=' . (int)$place['id']) ?>">احجز لهذه المنطقة</a>
      </div>
    </article>
  <?php endwhile; ?>
</div>

<!-- ============================================================
     [قسم الشركات السياحية]
     عنوان القسم + badge "شركات معتمدة".
     تحت: grid فيه بطاقات كل بطاقة تمثل شركة سياحية.
     كل شركة: صورتها (أو أيقونة SVG لو ما في صورة)، اسمها،
     رقم التليفون، العنوان، وصف مختصر، وزر حجز.
     ليه موجود: عشان يعرض الشركات المسجلة ويدخّل الزوار 
     يحجزوا مع الشركة المفضلة.
     ============================================================ -->
<div class="section-title">
  <div>
    <h2><?= svg_icon('building') ?> الشركات السياحية</h2>
    <p class="muted">اختر شركة مناسبة وأرسل طلب حجزك مباشرة.</p>
  </div>
  <span class="badge">شركات معتمدة</span>
</div>

<div class="grid company-grid">
  <?php while ($company = $companies->fetch_assoc()): ?>
    <!-- --------------------------------------------------------
         [بطاقة شركة سياحية]
         - إذا في صورة (image_url) نعرضها، وإلا نعرض أيقونة SVG.
         - اسم الشركة، التليفون، العنوان.
         - وصف مختصر (100 حرف).
         - رابط "احجز مع الشركة" يودي لـ book.php مع company_id.
         -------------------------------------------------------- -->
    <article class="card company-card">
      <?php if (!empty($company['image_url'])): ?>
        <div class="card-img"><img src="<?= h(asset_url($company['image_url'])) ?>" alt="<?= h($company['name']) ?>"></div>
      <?php else: ?>
        <div class="company-icon"><?= svg_icon('building') ?></div>
      <?php endif; ?>
      <div class="card-body">
        <h3><?= h($company['name']) ?></h3>
        <p class="muted"><?= svg_icon('phone') ?> <?= h($company['phone']) ?></p>
        <p class="muted"><?= svg_icon('pin') ?> <?= h($company['address']) ?></p>
        <p><?= h(text_excerpt($company['description'] ?? '', 100)) ?></p>
        <a class="btn small full-mobile" href="<?= url('book.php?company_id=' . (int)$company['id']) ?>">احجز مع الشركة</a>
      </div>
    </article>
  <?php endwhile; ?>
</div>

<?php
// ------------------------------------------------------------
// [تحميل ذيل الصفحة]
// footer.php يقفل الصفحة: يطبع </body> و </html>، 
// وفيه شريط الحقوق (copyright) والروابط.
// ------------------------------------------------------------
include __DIR__ . '/includes/footer.php';
?>
