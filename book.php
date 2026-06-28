<?php
require_once __DIR__ . '/auth.php';
$title = 'تسجيل حجز جديد';
$success = '';
$error   = '';
$today   = date('Y-m-d');

$selected_company = (int)($_GET['company_id'] ?? 0);
$selected_place   = (int)($_GET['place_id'] ?? 0);
$selected_company_info = null;
$selected_place_info   = null;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name    = trim($_POST['user_name'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $company_id   = (int)($_POST['company_id'] ?? 0);
    $place_id     = !empty($_POST['place_id']) ? (int)$_POST['place_id'] : null;
    $booking_date = $_POST['booking_date'] ?? '';
    $notes        = trim($_POST['notes'] ?? '');

    if ($user_name === '' || $phone === '' || $company_id <= 0 || $booking_date === '') {
        $error = '⚠️ أكمل الحقول المطلوبة: اسم العميل، رقم الهاتف، الشركة، تاريخ الحجز.';
    } elseif ($booking_date < $today) {
        $error = '⚠️ تاريخ الحجز لا يمكن أن يكون قبل تاريخ اليوم.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '⚠️ البريد الإلكتروني غير صحيح.';
    } else {
        $stmt = $conn->prepare("INSERT INTO bookings (user_name, phone, email, company_id, place_id, booking_date, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param('sssiiss', $user_name, $phone, $email, $company_id, $place_id, $booking_date, $notes);
        $stmt->execute();
        $success = '✅ تم تسجيل الحجز بنجاح! الحالة الآن: قيد الانتظار.';
        $selected_company = $company_id;
        $selected_place   = $place_id ?? 0;
    }
}

if ($selected_company > 0) {
    $stmt = $conn->prepare("SELECT id, name, phone, address FROM companies WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $selected_company);
    $stmt->execute();
    $selected_company_info = $stmt->get_result()->fetch_assoc();
}

if ($selected_place > 0) {
    $stmt = $conn->prepare("SELECT * FROM places WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $selected_place);
    $stmt->execute();
    $selected_place_info = $stmt->get_result()->fetch_assoc();
}

// جلب بيانات النماذج
$companies = $conn->query("SELECT id, name FROM companies ORDER BY name");
$places    = $conn->query("SELECT id, name, location FROM places ORDER BY name");

include __DIR__ . '/includes/header.php';
?>

<div class="booking-layout">
  <section class="card form-card booking-form-card">
    <h1>📋 تسجيل حجز سياحي جديد</h1>
    <p class="muted">قم بتعبئة النموذج أدناه لحجز رحلة مع إحدى الشركات السياحية.</p>

    <?php if ($success): ?>
      <div class="alert success"><?= h($success) ?></div>
    <?php elseif ($error): ?>
      <div class="alert error"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="form-row">
        <div>
          <label for="user_name">👤 اسم العميل *</label>
          <input type="text" id="user_name" name="user_name" required autocomplete="name">
        </div>
        <div>
          <label for="phone">📞 رقم الهاتف *</label>
          <input type="tel" id="phone" name="phone" required autocomplete="tel" inputmode="tel">
        </div>
      </div>

      <div class="form-row">
        <div>
          <label for="email">📧 البريد الإلكتروني</label>
          <input type="email" id="email" name="email" autocomplete="email">
        </div>
        <div>
          <label for="booking_date">📅 تاريخ الحجز *</label>
          <input type="date" id="booking_date" name="booking_date" min="<?= h($today) ?>" value="<?= h($today) ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div>
          <label for="company_id">🏢 الشركة السياحية *</label>
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
          <label for="place_id">📍 المنطقة السياحية</label>
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

      <label for="notes">📝 ملاحظات</label>
      <textarea id="notes" name="notes" placeholder="أي ملاحظات إضافية ..."></textarea>

      <div class="form-actions">
        <button class="btn" type="submit">💾 حفظ الحجز</button>
        <a class="btn secondary" href="<?= url('index.php') ?>">🔙 رجوع</a>
      </div>
    </form>
  </section>

  <aside class="booking-summary">
    <?php if ($selected_place_info): ?>
      <div class="card summary-card">
        <img src="<?= h(place_cover_url($selected_place_info)) ?>" alt="<?= h($selected_place_info['name']) ?>">
        <div class="card-body">
          <span class="badge"><?= h($selected_place_info['category']) ?></span>
          <h3><?= h($selected_place_info['name']) ?></h3>
          <p class="muted">📍 <?= h($selected_place_info['location']) ?></p>
          <p><?= h(text_excerpt($selected_place_info['description'] ?? '', 110)) ?></p>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($selected_company_info): ?>
      <div class="card summary-card compact">
        <div class="card-body">
          <span class="badge">الشركة المختارة</span>
          <h3><?= h($selected_company_info['name']) ?></h3>
          <p class="muted">📞 <?= h($selected_company_info['phone']) ?></p>
          <p class="muted">📍 <?= h($selected_company_info['address']) ?></p>
        </div>
      </div>
    <?php endif; ?>

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

<?php include __DIR__ . '/includes/footer.php'; ?>
