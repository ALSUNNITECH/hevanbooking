<?php
require_once __DIR__ . '/_layout.php';
$message = '';
$error   = '';
$edit    = null;

// --- حذف ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    redirect('admin/companies.php?msg=deleted');
}

// --- تعديل (جلب البيانات) ---
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

// --- إضافة / تحديث ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $image_url   = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $address === '' || $phone === '') {
        $error = 'أكمل الحقول المطلوبة: الاسم، العنوان، الهاتف.';
    } elseif ($id > 0) {
        $stmt = $conn->prepare("UPDATE companies SET name=?, address=?, phone=?, image_url=?, description=? WHERE id=?");
        $stmt->bind_param('sssssi', $name, $address, $phone, $image_url, $description, $id);
        $stmt->execute();
        redirect('admin/companies.php?msg=updated');
    } else {
        $stmt = $conn->prepare("INSERT INTO companies (name, address, phone, image_url, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $address, $phone, $image_url, $description);
        $stmt->execute();
        redirect('admin/companies.php?msg=created');
    }
}

// --- رسائل ---
if (!empty($_GET['msg'])) {
    $message = match ($_GET['msg']) {
        'created' => '✅ تمت إضافة الشركة بنجاح.',
        'updated' => '✅ تم تعديل الشركة بنجاح.',
        'deleted' => '✅ تم حذف الشركة بنجاح.',
        default   => ''
    };
}

$companies = $conn->query("SELECT * FROM companies ORDER BY id DESC");
admin_header('إدارة الشركات السياحية');
?>

<h1><?= svg_icon('building') ?> إدارة الشركات السياحية</h1>
<p class="muted">إضافة، تعديل، وحذف الشركات المسجلة في النظام.</p>

<?php if ($message): ?><div class="alert success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

<!-- نموذج الإضافة / التعديل -->
<div class="card form-card">
  <h2><?= $edit ? svg_icon('edit') . ' تعديل شركة' : svg_icon('plus') . ' إضافة شركة جديدة' ?></h2>
  <form method="post">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-row">
      <div>
        <label for="name">اسم الشركة *</label>
        <input type="text" id="name" name="name" value="<?= h($edit['name'] ?? '') ?>" required>
      </div>
      <div>
        <label for="phone">رقم الهاتف *</label>
        <input type="tel" id="phone" name="phone" value="<?= h($edit['phone'] ?? '') ?>" required>
      </div>
    </div>
    <div class="form-row">
      <div>
        <label for="address">العنوان *</label>
        <input type="text" id="address" name="address" value="<?= h($edit['address'] ?? '') ?>" required>
      </div>
      <div>
        <label for="image_url">اسم صورة الشركة داخل assets</label>
        <input type="text" id="image_url" name="image_url" value="<?= h($edit['image_url'] ?? '') ?>" placeholder="اختياري: hram.png">
      </div>
    </div>
    <label for="description">الوصف</label>
    <textarea id="description" name="description"><?= h($edit['description'] ?? '') ?></textarea>
    <div class="form-actions">
      <button class="btn" type="submit"><?= svg_icon('save') ?> حفظ</button>
      <?php if ($edit): ?>
        <a class="btn secondary" href="<?= url('admin/companies.php') ?>"><?= svg_icon('x') ?> إلغاء التعديل</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- جدول الشركات -->
<div class="section-title"><h2>قائمة الشركات</h2></div>
<div class="card table-wrap mobile-cards">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>الصورة</th>
        <th>الاسم</th>
        <th>الهاتف</th>
        <th>العنوان</th>
        <th>الوصف</th>
        <th>إجراءات</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $companies->fetch_assoc()): ?>
        <tr>
          <td data-label="#"><?= (int)$row['id'] ?></td>
          <td data-label="الصورة"><img class="table-thumb" src="<?= h(!empty($row['image_url']) ? asset_url($row['image_url']) : asset_url('hram.png')) ?>" alt="<?= h($row['name']) ?>"></td>
          <td data-label="الاسم"><b><?= h($row['name']) ?></b></td>
          <td data-label="الهاتف"><?= h($row['phone']) ?></td>
          <td data-label="العنوان"><?= h($row['address']) ?></td>
          <td data-label="الوصف" class="text-sm"><?= h(text_excerpt($row['description'] ?? '', 60)) ?></td>
          <td data-label="إجراءات" class="actions-cell">
            <a class="btn small" href="<?= url('admin/companies.php?edit=' . (int)$row['id']) ?>"><?= svg_icon('edit') ?></a>
            <a class="btn small danger" onclick="return confirm('حذف الشركة: <?= h($row['name']) ?>؟')"
               href="<?= url('admin/companies.php?delete=' . (int)$row['id']) ?>"><?= svg_icon('trash') ?></a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php admin_footer(); ?>
