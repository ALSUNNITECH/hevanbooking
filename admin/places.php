<?php
require_once __DIR__ . '/_layout.php';
$message = '';
$error   = '';
$edit    = null;

// --- حذف ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM places WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    redirect('admin/places.php?msg=deleted');
}

// --- تعديل (جلب) ---
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM places WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
}

// --- إضافة / تحديث ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = trim($_POST['name'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $category    = trim($_POST['category'] ?? 'عام');
    $image_url   = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || $location === '') {
        $error = 'أكمل الحقول المطلوبة: الاسم والموقع.';
    } elseif ($id > 0) {
        $stmt = $conn->prepare("UPDATE places SET name=?, location=?, category=?, image_url=?, description=? WHERE id=?");
        $stmt->bind_param('sssssi', $name, $location, $category, $image_url, $description, $id);
        $stmt->execute();
        redirect('admin/places.php?msg=updated');
    } else {
        $stmt = $conn->prepare("INSERT INTO places (name, location, category, image_url, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $location, $category, $image_url, $description);
        $stmt->execute();
        redirect('admin/places.php?msg=created');
    }
}

// --- رسائل ---
if (!empty($_GET['msg'])) {
    $message = match ($_GET['msg']) {
        'created' => '✅ تمت إضافة المنطقة بنجاح.',
        'updated' => '✅ تم تعديل المنطقة بنجاح.',
        'deleted' => '✅ تم حذف المنطقة بنجاح.',
        default   => ''
    };
}

$places = $conn->query("SELECT * FROM places ORDER BY id DESC");
admin_header('إدارة المناطق السياحية');
?>

<h1><?= svg_icon('pin') ?> إدارة المناطق السياحية</h1>
<p class="muted">إضافة، تعديل، وحذف المناطق والوجهات السياحية مع صورة اختيارية من assets.</p>

<?php if ($message): ?><div class="alert success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

<div class="card form-card">
  <h2><?= $edit ? svg_icon('edit') . ' تعديل منطقة' : svg_icon('plus') . ' إضافة منطقة جديدة' ?></h2>
  <form method="post">
    <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
    <div class="form-row">
      <div>
        <label for="name">اسم المنطقة *</label>
        <input type="text" id="name" name="name" value="<?= h($edit['name'] ?? '') ?>" required>
      </div>
      <div>
        <label for="location">الموقع *</label>
        <input type="text" id="location" name="location" value="<?= h($edit['location'] ?? '') ?>" required>
      </div>
    </div>
    <div class="form-row">
      <div>
        <label for="category">التصنيف</label>
        <select id="category" name="category">
          <?php foreach (['عام', 'آثار', 'طبيعة', 'تاريخ', 'شاطئ', 'جبل', 'مدينة'] as $cat): ?>
            <option value="<?= h($cat) ?>" <?= ($edit['category'] ?? 'عام') === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="image_url">اسم الصورة داخل assets</label>
        <input type="text" id="image_url" name="image_url" value="<?= h($edit['image_url'] ?? '') ?>" placeholder="مثال: Suakin1.png">
      </div>
    </div>
    <label for="description">الوصف</label>
    <textarea id="description" name="description"><?= h($edit['description'] ?? '') ?></textarea>
    <div class="form-actions">
      <button class="btn" type="submit"><?= svg_icon('save') ?> حفظ</button>
      <?php if ($edit): ?>
        <a class="btn secondary" href="<?= url('admin/places.php') ?>"><?= svg_icon('x') ?> إلغاء التعديل</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="section-title"><h2>قائمة المناطق</h2></div>
<div class="card table-wrap mobile-cards">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>الصورة</th>
        <th>الاسم</th>
        <th>الموقع</th>
        <th>التصنيف</th>
        <th>الوصف</th>
        <th>إجراءات</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $places->fetch_assoc()): ?>
        <tr>
          <td data-label="#"><?= (int)$row['id'] ?></td>
          <td data-label="الصورة"><img class="table-thumb" src="<?= h(place_cover_url($row)) ?>" alt="<?= h($row['name']) ?>"></td>
          <td data-label="الاسم"><b><?= h($row['name']) ?></b></td>
          <td data-label="الموقع"><?= h($row['location']) ?></td>
          <td data-label="التصنيف"><span class="badge"><?= h($row['category']) ?></span></td>
          <td data-label="الوصف" class="text-sm"><?= h(text_excerpt($row['description'] ?? '', 60)) ?></td>
          <td data-label="إجراءات" class="actions-cell">
            <a class="btn small" href="<?= url('admin/places.php?edit=' . (int)$row['id']) ?>"><?= svg_icon('edit') ?></a>
            <a class="btn small danger" onclick="return confirm('حذف المنطقة: <?= h($row['name']) ?>؟')"
               href="<?= url('admin/places.php?delete=' . (int)$row['id']) ?>"><?= svg_icon('trash') ?></a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php admin_footer(); ?>
