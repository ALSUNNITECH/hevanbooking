<?php
require_once __DIR__ . '/_layout.php';

$stats = [];
$stats['companies'] = (int)$conn->query("SELECT COUNT(*) AS c FROM companies")->fetch_assoc()['c'];
$stats['places']    = (int)$conn->query("SELECT COUNT(*) AS c FROM places")->fetch_assoc()['c'];
$stats['bookings']  = (int)$conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'];
$stats['pending']   = (int)$conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='pending'")->fetch_assoc()['c'];

$latest = $conn->query("SELECT b.*, c.name AS company_name, p.name AS place_name
                        FROM bookings b
                        JOIN companies c ON c.id = b.company_id
                        LEFT JOIN places p ON p.id = b.place_id
                        ORDER BY b.id DESC LIMIT 8");

admin_header('لوحة التحكم');
?>

<h1><?= svg_icon('chart') ?> لوحة التحكم</h1>
<p class="muted">ملخص سريع لنظام الحجوزات التجريبي.</p>

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
    <span class="muted">كل الحجوزات</span>
  </div>
  <div class="stat-box">
    <span class="stat-icon"><?= svg_icon('clock') ?></span>
    <strong><?= $stats['pending'] ?></strong>
    <span class="muted">قيد الانتظار</span>
  </div>
</div>

<div class="section-title">
  <h2>آخر الحجوزات</h2>
  <a class="btn small" href="<?= url('admin/bookings.php') ?>">إدارة الحجوزات</a>
</div>

<div class="card table-wrap mobile-cards">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>العميل</th>
        <th>الهاتف</th>
        <th>الشركة</th>
        <th>المنطقة</th>
        <th>التاريخ</th>
        <th>الحالة</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $latest->fetch_assoc()): ?>
        <tr>
          <td data-label="#"><?= (int)$row['id'] ?></td>
          <td data-label="العميل"><b><?= h($row['user_name']) ?></b></td>
          <td data-label="الهاتف"><?= h($row['phone']) ?></td>
          <td data-label="الشركة"><?= h($row['company_name']) ?></td>
          <td data-label="المنطقة"><?= h($row['place_name'] ?? '-') ?></td>
          <td data-label="التاريخ"><?= h($row['booking_date']) ?></td>
          <td data-label="الحالة"><span class="badge <?= status_class($row['status']) ?>"><?= status_label($row['status']) ?></span></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php admin_footer(); ?>
