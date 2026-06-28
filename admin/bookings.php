<?php
require_once __DIR__ . '/_layout.php';
$message = '';

// --- تحديث الحالة ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['id'])) {
    $id     = (int)$_POST['id'];
    $status = $_POST['status'];
    if (in_array($status, ['pending', 'accepted', 'rejected'], true)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        redirect('admin/bookings.php?msg=status');
    }
}

// --- حذف ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    redirect('admin/bookings.php?msg=deleted');
}

// --- رسائل ---
if (!empty($_GET['msg'])) {
    $message = $_GET['msg'] === 'deleted' ? '✅ تم حذف الحجز.' : '✅ تم تحديث حالة الحجز.';
}

// --- فلترة ---
$status_filter = $_GET['status'] ?? '';
$where = '';
$params = [];
$types = '';
if (in_array($status_filter, ['pending', 'accepted', 'rejected'], true)) {
    $where = 'WHERE b.status = ?';
    $params[] = $status_filter;
    $types = 's';
}

$sql = "SELECT b.*, c.name AS company_name, p.name AS place_name
        FROM bookings b
        JOIN companies c ON c.id = b.company_id
        LEFT JOIN places p ON p.id = b.place_id
        $where
        ORDER BY b.id DESC";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bookings = $stmt->get_result();

admin_header('إدارة الحجوزات');
?>

<h1>📋 إدارة الحجوزات</h1>
<p class="muted">عرض الحجوزات وتحديث حالتها: قيد الانتظار، مقبول، مرفوض.</p>

<?php if ($message): ?><div class="alert success"><?= h($message) ?></div><?php endif; ?>

<div class="section-title">
  <h2>فلترة حسب الحالة</h2>
  <form method="get" class="inline-form">
    <select name="status" onchange="this.form.submit()">
      <option value="">📋 كل الحالات</option>
      <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>⏳ قيد الانتظار</option>
      <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>✅ مقبول</option>
      <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>❌ مرفوض</option>
    </select>
  </form>
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
        <th>تاريخ الحجز</th>
        <th>ملاحظات</th>
        <th>الحالة</th>
        <th>إجراءات</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $bookings->fetch_assoc()): ?>
        <tr>
          <td data-label="#"><?= (int)$row['id'] ?></td>
          <td data-label="العميل"><b><?= h($row['user_name']) ?></b></td>
          <td data-label="الهاتف"><?= h($row['phone']) ?></td>
          <td data-label="الشركة"><?= h($row['company_name']) ?></td>
          <td data-label="المنطقة"><?= h($row['place_name'] ?? '-') ?></td>
          <td data-label="تاريخ الحجز"><?= h($row['booking_date']) ?></td>
          <td data-label="ملاحظات" class="text-sm"><?= h($row['notes'] ? text_excerpt($row['notes'], 40) : '-') ?></td>
          <td data-label="الحالة"><span class="badge <?= status_class($row['status']) ?>"><?= status_label($row['status']) ?></span></td>
          <td data-label="إجراءات" class="actions-cell">
            <form method="post" class="inline-form">
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
              <select name="status" class="small-select">
                <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>⏳ انتظار</option>
                <option value="accepted" <?= $row['status'] === 'accepted' ? 'selected' : '' ?>>✅ قبول</option>
                <option value="rejected" <?= $row['status'] === 'rejected' ? 'selected' : '' ?>>❌ رفض</option>
              </select>
              <button class="btn small" type="submit">تحديث</button>
            </form>
            <a class="btn small danger" onclick="return confirm('❗ حذف الحجز؟')"
               href="<?= url('admin/bookings.php?delete=' . (int)$row['id']) ?>">🗑️</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php admin_footer(); ?>
