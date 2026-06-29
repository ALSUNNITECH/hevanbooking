<?php
// ============================================================
// ملف: admin/bookings.php
// الوظيفة: صفحة إدارة الحجوزات (عرض، فلترة، تحديث حالة، حذف)
// ============================================================

// نستدعي ملف الـ layout تبع الأدمن (فيها header و footer و svg_icon والاتصال بـ MySQL)
require_once __DIR__ . '/_layout.php';

// متغير رسالة النجاح اللي بنعرضها للمستخدم بعد أي عملية (تحديث أو حذف)
$message = '';

// ============================================================
// 1. معالجة تحديث حالة الحجز (POST)
// ============================================================
// بنشيك لو المستخدم ضغط على زر تحديث الحالة وجاب معاه status و id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['id'])) {
    $id     = (int)$_POST['id'];                      // نحولو id لرقم صحيح عشان نمنع الحقن
    $status = $_POST['status'];                        // الحالة الجديدة (pending, accepted, rejected)
    // بنتأكد إن الحالة دي ضمن القيم المسموح ليها عشان ما يحصلش اختراق
    if (in_array($status, ['pending', 'accepted', 'rejected'], true)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);          // 's' للنص، 'i' للرقم
        $stmt->execute();                               // ننفذ التحديث
        redirect('admin/bookings.php?msg=status');      // نرجع تاني للصفحة مع رسالة نجاح
    }
}

// ============================================================
// 2. معالجة حذف الحجز (GET)
// ============================================================
// بنشيك لو في باراميتر delete في الرابط (زي admin/bookings.php?delete=5)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];                     // نجيب id الحجز ونحولو لرقم صحيح
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");  // نجهز أمر المسح
    $stmt->bind_param('i', $id);                    // نربط المتغير
    $stmt->execute();                               // ننفذ المسح
    redirect('admin/bookings.php?msg=deleted');     // نرجع للصفحة برسالة تأكيد الحذف
}

// ============================================================
// 3. تحديد رسالة النجاح اللي نعرضها
// ============================================================
// لو في msg في الرابط، نحدد الرسالة حسب نوعها (deleted ولا status)
if (!empty($_GET['msg'])) {
    $message = $_GET['msg'] === 'deleted' ? '✅ تم حذف الحجز.' : '✅ تم تحديث حالة الحجز.';
}

// ============================================================
// 4. فلترة الحجوزات حسب الحالة
// ============================================================
// نجيب قيمة الفلترة من الرابط (لو ما في حاجة نحطها فاضية)
$status_filter = $_GET['status'] ?? '';
$where = '';           // جزء WHERE في جملة SQL
$params = [];           // باراميترات الـ bind
$types = '';            // أنواعهم (s, i, d)
// لو الفلترة قيمة صحيحة (pending, accepted, rejected) نضيف شرط WHERE
if (in_array($status_filter, ['pending', 'accepted', 'rejected'], true)) {
    $where = 'WHERE b.status = ?';
    $params[] = $status_filter;
    $types = 's';       // string
}

// ============================================================
// 5. جلب الحجوزات من قاعدة البيانات مع JOIN
// ============================================================
// نجيب كل بيانات الحجز + اسم الشركة + اسم المنطقة (لو في)
// بنستخدم LEFT JOIN عشان المنطقة ممكن تكون فاضية (ما اختراها العميل)
$sql = "SELECT b.*, c.name AS company_name, p.name AS place_name
        FROM bookings b
        JOIN companies c ON c.id = b.company_id
        LEFT JOIN places p ON p.id = b.place_id
        $where
        ORDER BY b.id DESC";                         // الأحدث أولاً
$stmt = $conn->prepare($sql);                        // نجهز الاستعلام
if ($params) {                                      // لو في فلترة نضيف الباراميترات
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();                                    // ننفذ الاستعلام
$bookings = $stmt->get_result();                     // نجيب النتيجة كـ object قابل للتكرار

// ============================================================
// 6. عرض الهيدر بتاع صفحة الأدمن
// ============================================================
admin_header('إدارة الحجوزات');
?>

<!-- ======== عنوان الصفحة الرئيسي ======== -->
<h1><?= svg_icon('booking') ?> إدارة الحجوزات</h1>
<p class="muted">عرض الحجوزات وتحديث حالتها: قيد الانتظار، مقبول، مرفوض.</p>

<!-- رسالة النجاح (تحديث أو حذف) -->
<?php if ($message): ?><div class="alert success"><?= h($message) ?></div><?php endif; ?>

<!-- ======== قسم الفلترة ======== -->
<div class="section-title">
  <h2>فلترة حسب الحالة</h2>
  <!-- الفورم دي بتعمل submit تلقائي أول ما تختار قيمة (onchange) -->
  <form method="get" class="inline-form">
    <select name="status" onchange="this.form.submit()">
      <option value="">كل الحالات</option>
      <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
      <option value="accepted" <?= $status_filter === 'accepted' ? 'selected' : '' ?>>مقبول</option>
      <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
    </select>
  </form>
</div>

<!-- ======== جدول الحجوزات (متجاوب مع الموبايل) ======== -->
<div class="card table-wrap mobile-cards">
  <table>
    <!-- ترويسة الجدول -->
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
      <!-- نلف على كل صف في نتيجة الاستعلام -->
      <?php while ($row = $bookings->fetch_assoc()): ?>
        <tr>
          <td data-label="#"><?= (int)$row['id'] ?></td>                                    <!-- رقم الحجز -->
          <td data-label="العميل"><b><?= h($row['user_name']) ?></b></td>                     <!-- اسم العميل -->
          <td data-label="الهاتف"><?= h($row['phone']) ?></td>                                <!-- رقم الهاتف -->
          <td data-label="الشركة"><?= h($row['company_name']) ?></td>                         <!-- اسم الشركة (من JOIN) -->
          <td data-label="المنطقة"><?= h($row['place_name'] ?? '-') ?></td>                   <!-- اسم المنطقة (أو شرطة لو ما في) -->
          <td data-label="تاريخ الحجز"><?= h($row['booking_date']) ?></td>                    <!-- تاريخ الحجز -->
          <td data-label="ملاحظات" class="text-sm"><?= h($row['notes'] ? text_excerpt($row['notes'], 40) : '-') ?></td> <!-- ملاحظات مختصرة -->
          <td data-label="الحالة"><span class="badge <?= status_class($row['status']) ?>"><?= status_label($row['status']) ?></span></td> <!-- شارة الحالة -->
          <td data-label="إجراءات" class="actions-cell">
            <!-- فورم تحديث الحالة (كل صف ليها فورم خاصة) -->
            <form method="post" class="inline-form">
              <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">  <!-- نمرر id الحجز مخفي -->
              <select name="status" class="small-select">                      <!-- اختيار الحالة الجديدة -->
                <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>انتظار</option>
                <option value="accepted" <?= $row['status'] === 'accepted' ? 'selected' : '' ?>>قبول</option>
                <option value="rejected" <?= $row['status'] === 'rejected' ? 'selected' : '' ?>>رفض</option>
              </select>
              <button class="btn small" type="submit">تحديث</button>          <!-- زر التحديث -->
            </form>
            <!-- رابط حذف الحجز (بيظهر confirm قبل الحذف الفعلي) -->
            <a class="btn small danger" onclick="return confirm('حذف الحجز؟')"
               href="<?= url('admin/bookings.php?delete=' . (int)$row['id']) ?>"><?= svg_icon('trash') ?></a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php
// ============================================================
// 7. عرض الفوتر بتاع صفحة الأدمن
// ============================================================
admin_footer();
?>
