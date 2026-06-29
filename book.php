<?php
// ============================================================
// صفحة تسجيل حجز جديد - Hevan Booking
// ============================================================
// الفكرة العامة: الصفحة دي بتسمح للزوار يسجلوا حجز سياحي جديد
// عن طريق تعبئة نموذج فيه بيانات العميل والشركة والتاريخ.
// ============================================================

// ---------- جلب المكتبات الأساسية ----------
// نشمل ملف auth.php عشان نتأكد من صلاحيات الدخول (إن لزم)
// ونستخدم المتغيرات والوظائف اللي فيه.
require_once __DIR__ . '/auth.php';

// ---------- متغيرات الصفحة الأساسية ----------
// $title: عنوان الصفحة اللي بيظهر في تبويب المتصفح
// $success / $error: رسايل النجاح أو الخطأ اللي نعرضها للمستخدم
// $today: تاريخ اليوم (صيغة YYYY-MM-DD) عشان نقارن بيه تواريخ الحجز
$title = 'تسجيل حجز جديد';
$success = '';
$error   = '';
$today   = date('Y-m-d');

// ---------- قراءة معاملات URL (GET) ----------
// لو المستخدم جاي من صفحة رئيسية وضغط على شركة معينة أو منطقة،
// بنجيب الـ IDs من الرابط عشان نعبيهم تلقائياً في النموذج.
$selected_company = (int)($_GET['company_id'] ?? 0);
$selected_place   = (int)($_GET['place_id'] ?? 0);
$selected_company_info = null;
$selected_place_info   = null;

// ============================================================
// 1. معالجة إرسال النموذج (POST)
// ============================================================
// لما المستخدم يضغط على "حفظ الحجز" بنستقبل البيانات هنا،
// نتحقق منها، وبعدين نحفظها في قاعدة البيانات.
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ---------- قراءة المدخلات من $_POST ----------
    // نستخدم trim() عشان نشيل الفراغات الزايدة من الحواشي.
    // النقطة (??) تعني: لو القيمة مش موجودة استخدم القيمة الافتراضية.
    $user_name    = trim($_POST['user_name'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $company_id   = (int)($_POST['company_id'] ?? 0);
    $place_id     = !empty($_POST['place_id']) ? (int)$_POST['place_id'] : null;
    $booking_date = $_POST['booking_date'] ?? '';
    $notes        = trim($_POST['notes'] ?? '');

    // ---------- التحقق من صحة البيانات (Validation) ----------
    // 1. الحقول الإجبارية: اسم العميل، رقم الهاتف، الشركة، التاريخ.
    // 2. التاريخ ما يكونش قبل النهاردة.
    // 3. لو في إيميل، نتأكد إن صيغته صحيحة.
    // ==================================================
    if ($user_name === '' || $phone === '' || $company_id <= 0 || $booking_date === '') {
        $error = 'أكمل الحقول المطلوبة: اسم العميل، رقم الهاتف، الشركة، تاريخ الحجز.';
    } elseif ($booking_date < $today) {
        $error = 'تاريخ الحجز لا يمكن أن يكون قبل تاريخ اليوم.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'البريد الإلكتروني غير صحيح.';
    } else {
        // ---------- حفظ البيانات في قاعدة البيانات ----------
        // نستخدم prepared statement عشان نمنع هجمات SQL Injection.
        // الحجز ينحفظ بحالة "pending" يعني قيد الانتظار لحين الموافقة.
        // ==================================================
        $stmt = $conn->prepare("INSERT INTO bookings (user_name, phone, email, company_id, place_id, booking_date, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param('sssiiss', $user_name, $phone, $email, $company_id, $place_id, $booking_date, $notes);
        $stmt->execute();
        $success = 'تم تسجيل الحجز بنجاح! الحالة الآن: قيد الانتظار.';

        // ---------- تحديث المتغيرات المختارة ----------
        // عشان النموذج يفضل عارض نفس الخيارات اللي اختارها المستخدم.
        $selected_company = $company_id;
        $selected_place   = $place_id ?? 0;
    }
}

// ============================================================
// 2. جلب معلومات الشركة المختارة (إن وجدت)
// ============================================================
// لو المستخدم اختار شركة من الرابط أو من النموذج، نجيب بياناتها
// (الاسم، الهاتف، العنوان) عشان نعرضها في الملخص جنب النموذج.
// ============================================================
if ($selected_company > 0) {
    $stmt = $conn->prepare("SELECT id, name, phone, address FROM companies WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $selected_company);
    $stmt->execute();
    $selected_company_info = $stmt->get_result()->fetch_assoc();
}

// ============================================================
// 3. جلب معلومات المنطقة السياحية المختارة (إن وجدت)
// ============================================================
// نفس الفكرة: لو اختار منطقة، نجيب كل بياناتها (الاسم، الموقع،
// الوصف، التصنيف، الصورة) عشان نعرضها جنب النموذج.
// ============================================================
if ($selected_place > 0) {
    $stmt = $conn->prepare("SELECT * FROM places WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $selected_place);
    $stmt->execute();
    $selected_place_info = $stmt->get_result()->fetch_assoc();
}

// ============================================================
// 4. جلب قوائم الخيارات للنموذج (شركات + مناطق)
// ============================================================
// نجيب كل الشركات والمناطق من قاعدة البيانات عشان المستخدم
// يختار منها في القوائم المنسدلة (select boxes).
// الترتيب: حسب الاسم (أبجدي).
// ============================================================
$companies = $conn->query("SELECT id, name FROM companies ORDER BY name");
$places    = $conn->query("SELECT id, name, location FROM places ORDER BY name");

// ============================================================
// 5. تضمين رأس الصفحة (header)
// ============================================================
// header.php فيه فتح وسم <html> و <head> والقائمة العلوية.
include __DIR__ . '/includes/header.php';
?>

<!-- ========================================================== -->
<!-- 6. بداية محتوى الصفحة الرئيسي -->
<!-- ========================================================== -->
<div class="booking-layout">

  <!-- ========================================================== -->
  <!-- 6أ. بطاقة النموذج الأساسية -->
  <!-- ========================================================== -->
  <section class="card form-card booking-form-card">
    <h1><?= svg_icon('booking') ?> تسجيل حجز سياحي جديد</h1>
    <p class="muted">قم بتعبئة النموذج أدناه لحجز رحلة مع إحدى الشركات السياحية.</p>

    <!-- ========================================================== -->
    <!-- رسايل النجاح أو الخطأ -->
    <!-- ========================================================== -->
    <?php if ($success): ?>
      <div class="alert success"><?= h($success) ?></div>
    <?php elseif ($error): ?>
      <div class="alert error"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- ========================================================== -->
    <!-- 6ب. النموذج نفسه (method="post" → يرسل لنفس الصفحة) -->
    <!-- ========================================================== -->
    <form method="post">

      <!-- ---------- الصف الأول: اسم العميل + رقم الهاتف ---------- -->
      <div class="form-row">
        <div>
          <label for="user_name"><?= svg_icon('user') ?> اسم العميل *</label>
          <input type="text" id="user_name" name="user_name" required autocomplete="name">
        </div>
        <div>
          <label for="phone"><?= svg_icon('phone') ?> رقم الهاتف *</label>
          <input type="tel" id="phone" name="phone" required autocomplete="tel" inputmode="tel">
        </div>
      </div>

      <!-- ---------- الصف الثاني: البريد الإلكتروني + تاريخ الحجز ---------- -->
      <div class="form-row">
        <div>
          <label for="email"><?= svg_icon('mail') ?> البريد الإلكتروني</label>
          <input type="email" id="email" name="email" autocomplete="email">
        </div>
        <div>
          <label for="booking_date"><?= svg_icon('calendar') ?> تاريخ الحجز *</label>
          <!-- min="$today" → يمنع اختيار تاريخ فات; value="$today" → القيمة الافتراضية النهاردة -->
          <input type="date" id="booking_date" name="booking_date" min="<?= h($today) ?>" value="<?= h($today) ?>" required>
        </div>
      </div>

      <!-- ---------- الصف الثالث: الشركة + المنطقة ---------- -->
      <div class="form-row">
        <div>
          <label for="company_id"><?= svg_icon('building') ?> الشركة السياحية *</label>
          <select id="company_id" name="company_id" required>
            <option value="">-- اختر الشركة --</option>
            <?php while ($company = $companies->fetch_assoc()): ?>
              <option value="<?= (int)$company['id'] ?>"
                <?= $selected_company === (int)$company['id'] ? 'selected' : '' ?>>
                <?= h($company['name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label for="place_id"><?= svg_icon('pin') ?> المنطقة السياحية</label>
          <select id="place_id" name="place_id">
            <option value="">-- بدون تحديد --</option>
            <?php while ($place = $places->fetch_assoc()): ?>
              <option value="<?= (int)$place['id'] ?>"
                <?= $selected_place === (int)$place['id'] ? 'selected' : '' ?>>
                <?= h($place['name']) ?> - <?= h($place['location']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>

      <!-- ---------- حقل الملاحظات (نص حر) ---------- -->
      <label for="notes"><?= svg_icon('note') ?> ملاحظات</label>
      <textarea id="notes" name="notes" placeholder="أي ملاحظات إضافية ..."></textarea>

      <!-- ---------- أزرار الإجراءات ---------- -->
      <div class="form-actions">
        <button class="btn" type="submit"><?= svg_icon('save') ?> حفظ الحجز</button>
        <a class="btn secondary" href="<?= url('index.php') ?>"><?= svg_icon('back') ?> رجوع</a>
      </div>
    </form>
  </section>

  <!-- ========================================================== -->
  <!-- 7. الشريط الجانبي: ملخص الحجز -->
  <!-- ========================================================== -->
  <!-- هنا بنعرض معلومات مختصرة عن المنطقة والشركة المختارة عشان
       المستخدم يشوف شو اختار قبل لا يرسل النموذج. -->
  <!-- ========================================================== -->
  <aside class="booking-summary">

    <!-- ---------- بطاقة المنطقة السياحية ---------- -->
    <?php if ($selected_place_info): ?>
      <div class="card summary-card">
        <!-- صورة المنطقة (بنستخدم دالة place_cover_url اللي في helpers) -->
        <img src="<?= h(place_cover_url($selected_place_info)) ?>" alt="<?= h($selected_place_info['name']) ?>">
        <div class="card-body">
          <!-- badge: تصنيف المنطقة (مثلاً: شاطئ، جبل، متحف) -->
          <span class="badge"><?= h($selected_place_info['category']) ?></span>
          <h3><?= h($selected_place_info['name']) ?></h3>
          <p class="muted"><?= svg_icon('pin') ?> <?= h($selected_place_info['location']) ?></p>
          <!-- text_excerpt: دالة بتقطع الوصف لو طويل (110 حرف) -->
          <p><?= h(text_excerpt($selected_place_info['description'] ?? '', 110)) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <!-- ---------- بطاقة الشركة المختارة ---------- -->
    <?php if ($selected_company_info): ?>
      <div class="card summary-card compact">
        <div class="card-body">
          <span class="badge">الشركة المختارة</span>
          <h3><?= h($selected_company_info['name']) ?></h3>
          <p class="muted"><?= svg_icon('phone') ?> <?= h($selected_company_info['phone']) ?></p>
          <p class="muted"><?= svg_icon('pin') ?> <?= h($selected_company_info['address']) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <!-- ---------- بطاقة تعليمات (لما ما يكونش في شيء مختار) ---------- -->
    <?php if (!$selected_place_info && !$selected_company_info): ?>
      <div class="card summary-card compact">
        <div class="card-body">
          <span class="badge">نصيحة</span>
          <h3>اختر وجهة أو شركة</h3>
          <p class="muted">يمكنك الرجوع للرئيسية واختيار بطاقة محددة ليتم تعبئة النموذج تلقائياً.</p>
        </div>
      </div>
    <?php endif; ?>
  </aside>
</div>

<?php
// ============================================================
// 8. تضمين ذيل الصفحة (footer)
// ============================================================
// footer.php فيه إغلاق وسم <body> و <html> وحقوق النشر.
include __DIR__ . '/includes/footer.php';
?>
