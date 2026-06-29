<?php
// نستدعي ملف القالب حق لوحة التحكم عشان نستخدم نفس التصميم
require_once __DIR__ . '/_layout.php';

// --- إحصائيات لوحة التحكم ---
// هنا نجيب عدد كل حاجة من قاعدة البيانات عشان نعرضها في البطاقات
$stats = [];
// عدد الشركات المسجلة في النظام
$stats['companies'] = (int)$conn->query("SELECT COUNT(*) AS c FROM companies")->fetch_assoc()['c'];
// عدد المناطق / الأماكن المتاحة
$stats['places']    = (int)$conn->query("SELECT COUNT(*) AS c FROM places")->fetch_assoc()['c'];
// إجمالي الحجوزات اللي حصلت
$stats['bookings']  = (int)$conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'];
// الحجوزات اللي لسا ما تأكدت (قيد الانتظار)
$stats['pending']   = (int)$conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='pending'")->fetch_assoc()['c'];

// --- آخر الحجوزات ---
// نجيب آخر 8 حجوزات مسجلة، مع اسم الشركة والمنطقة عن طريق الربط بين الجداول
$latest = $conn->query("SELECT b.*, c.name AS company_name, p.name AS place_name
                        FROM bookings b
                        JOIN companies c ON c.id = b.company_id
                        LEFT JOIN places p ON p.id = b.place_id
                        ORDER BY b.id DESC LIMIT 8");

// نبدأ عرض صفحة لوحة التحكم بالعنوان والأيقونة
admin_header('لوحة التحكم');
?>

<!-- عنوان الصفحة الرئيسي -->
<h1><?= svg_icon('chart') ?> لوحة التحكم</h1>
<p class="muted">ملخص سريع لنظام الحجوزات التجريبي.</p>

<!-- البطاقات الأربعة حق الإحصائيات -->
<div class="stats-row">
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('building') ?></span>
    <strong><?= $stats['companies'] ?></strong> <!-- عدد الشركات -->
    <span class="muted">الشركات</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('pin') ?></span>
    <strong><?= $stats['places'] ?></strong> <!-- عدد المناطق -->
    <span class="muted">المناطق</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('booking') ?></span>
    <strong><?= $stats['bookings'] ?></strong> <!-- إجمالي الحجوزات -->
    <span class="muted">كل الحجوزات</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('clock') ?></span>
    <strong><?= $stats['pending'] ?></strong> <!-- الحجوزات المعلقة -->
    <span class="muted">قيد الانتظار</span>
  </div>
</div>

<!-- عنوان قسم آخر الحجوزات مع رابط للإدارة -->
<div class="section-title">
  <h2>آخر الحجوزات</h2>
  <a class="btn small" href="<?= url('admin/bookings.php') ?>">إدارة الحجوزات</a>
</div>

<!-- الجدول اللي يعرض آخر الحجوزات -->
<div class="card table-wrap mobile-cards">
  <table>
    <thead>
      <tr>
        <th>#</th> <!-- رقم الحجز -->
        <th>العميل</th> <!-- اسم العميل -->
        <th>الهاتف</th> <!-- رقم التلفون -->
        <th>الشركة</th> <!-- اسم الشركة -->
        <th>المنطقة</th> <!-- اسم المنطقة أو المكان -->
        <th>التاريخ</th> <!-- تاريخ الحجز -->
        <th>الحالة</th> <!-- حالة الحجز (مؤكد، قيد الانتظار، الخ) -->
      </tr>
    </thead>
    <tbody>
      <!-- ندور على كل حجز في النتائج ونعرضه في صف -->
      <?php while ($row = $latest->fetch_assoc()): ?>
        <tr>
          <td data-label="#"><?= (int)$row['id'] ?></td> <!-- رقم الحجز -->
          <td data-label="العميل"><b><?= h($row['user_name']) ?></b></td> <!-- اسم العميل -->
          <td data-label="الهاتف"><?= h($row['phone']) ?></td> <!-- رقم الهاتف -->
          <td data-label="الشركة"><?= h($row['company_name']) ?></td> <!-- الشركة المحجوز عندها -->
          <td data-label="المنطقة"><?= h($row['place_name'] ?? '-') ?></td> <!-- المنطقة (لو ما في، نكتب شرطة) -->
          <td data-label="التاريخ"><?= h($row['booking_date']) ?></td> <!-- تاريخ الحجز -->
          <!-- شارة الحالة لونها يتغير حسب نوع الحالة -->
          <td data-label="الحالة"><span class="badge <?= status_class($row['status']) ?>"><?= status_label($row['status']) ?></span></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php
// هنا نستدعي دالة الفوتر عشان نقفل الصفحة صح
admin_footer();
?>
