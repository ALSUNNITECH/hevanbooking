<?php
// ============================================================
// هذا الملف خاص بإدارة المناطق السياحية في لوحة التحكم (admin).
// هنا بنقدر نضيف مناطق جديدة، نعدل في مناطق موجودة،
// أو نحذف أي منطقة. كمان بنعرض كل المناطق في جدول مرتب.
// ============================================================

// السطر ده بيجيب ملف الـ _layout.php اللي فيه الهيدر والفوتر
// والدوال المساعدة حق Admin. لازم يكون موجود عشان الصفحة تشتغل.
require_once __DIR__ . '/_layout.php';

// $message => رسالة نجاح (زي "تمت الإضافة بنجاح")
// $error   => رسالة خطأ (زي لو حقل فاضي)
// $edit    => null يعني ما فيش تعديل دلوقتي، ولو جينا نعدل بنحط فيه بيانات المنطقة
$message = '';
$error   = '';
$edit    = null;

// --- قسم الحذف ---
// لو المستخدم ضغط على "حذف" من الجدول (الرابط فيه delete=رقم)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];          // نجيب رقم المنطقة من الرابط ونحوله لعدد صحيح
    $stmt = $conn->prepare("DELETE FROM places WHERE id = ?"); // جملة حذف
    $stmt->bind_param('i', $id);         // نربط الرقم بالاستعلام
    $stmt->execute();                    // ننفذ الحذف
    redirect('admin/places.php?msg=deleted'); // نرجّع المستخدم لصفحة المناطق مع رسالة نجاح
}

// --- قسم جلب بيانات التعديل ---
// لو المستخدم ضغط على "تعديل"، نجيب بيانات المنطقة عشان نملّي الفورم
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];            // رقم المنطقة من الرابط
    $stmt = $conn->prepare("SELECT * FROM places WHERE id = ?"); // نجيب كل شي عن المنطقة
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); // نحفظ البيانات في $edit عشان تستخدم في الفورم
}

// --- قسم الإضافة والتحديث (لما المستخدم يضغط "حفظ") ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // نجيب القيم من الفورم وننضفها من المسافات
    $id          = (int)($_POST['id'] ?? 0);      // لو id موجود يعني تعديل، لو 0 يعني إضافة جديدة
    $name        = trim($_POST['name'] ?? '');
    $location    = trim($_POST['location'] ?? '');
    $category    = trim($_POST['category'] ?? 'عام'); // التصنيف الافتراضي "عام"
    $image_url   = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // التحقق من الحقول الإجبارية (الاسم والموقع لازم يكونوا موجودين)
    if ($name === '' || $location === '') {
        $error = 'أكمل الحقول المطلوبة: الاسم والموقع.'; // رسالة خطأ بالعربي
    } elseif ($id > 0) {
        // لو id أكبر من 0 يعني هذا تعديل — نحدث البيانات في قاعدة البيانات
        $stmt = $conn->prepare("UPDATE places SET name=?, location=?, category=?, image_url=?, description=? WHERE id=?");
        $stmt->bind_param('sssssi', $name, $location, $category, $image_url, $description, $id);
        $stmt->execute();
        redirect('admin/places.php?msg=updated'); // تحويل مع رسالة "تم التعديل"
    } else {
        // لو id = 0 يعني إضافة جديدة — ندرج سطر جديد في جدول places
        $stmt = $conn->prepare("INSERT INTO places (name, location, category, image_url, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $name, $location, $category, $image_url, $description);
        $stmt->execute();
        redirect('admin/places.php?msg=created'); // تحويل مع رسالة "تمت الإضافة"
    }
}

// --- قسم رسائل النجاح ---
// بعد ما نعمل إضافة/تعديل/حذف، بنرجع عبر الرابط مع msg=
// وهنا نقراها ونعرض رسالة مناسبة
if (!empty($_GET['msg'])) {
    $message = match ($_GET['msg']) {
        'created' => '✅ تمت إضافة المنطقة بنجاح.',
        'updated' => '✅ تم تعديل المنطقة بنجاح.',
        'deleted' => '✅ تم حذف المنطقة بنجاح.',
        default   => ''
    };
}

// نجيب كل المناطق من قاعدة البيانات، مرتبة من الأحدث للأقدم
$places = $conn->query("SELECT * FROM places ORDER BY id DESC");

// نستدعي دالة admin_header عشان تطبع فتحة الصفحة (الهيدر والقوائم الجانبية)
admin_header('إدارة المناطق السياحية');
?>

<!-- هنا يبدأ محتوى الصفحة HTML -->

<h1><?= svg_icon('pin') ?> إدارة المناطق السياحية</h1>
<p class="muted">إضافة، تعديل، وحذف المناطق والوجهات السياحية مع صورة اختيارية من assets.</p>

<!-- رسائل النجاح أو الخطأ -->
<?php if ($message): ?><div class="alert success"><?= h($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>

<!-- بطاقة النموذج: إضافة جديدة أو تعديل منطقة موجودة -->
<div class="card form-card">
  <!-- العنوان يتغير حسب لو في تعديل ولا لا -->
  <h2><?= $edit ? svg_icon('edit') . ' تعديل منطقة' : svg_icon('plus') . ' إضافة منطقة جديدة' ?></h2>
  <form method="post">
    <!-- حقل مخفي فيه id المنطقة (لما نعدل) — لو 0 يعني إضافة جديدة -->
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
        <!-- قائمة منسدلة بخيارات التصنيف: عام، آثار، طبيعة، تاريخ، شاطئ، جبل، مدينة -->
        <select id="category" name="category">
          <?php foreach (['عام', 'آثار', 'طبيعة', 'تاريخ', 'شاطئ', 'جبل', 'مدينة'] as $cat): ?>
            <option value="<?= h($cat) ?>" <?= ($edit['category'] ?? 'عام') === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="image_url">اسم الصورة داخل assets</label>
        <!-- حقل إدخال اسم الصورة (زي Suakin1.png) — الصورة تكون مجهزة في مجلد assets -->
        <input type="text" id="image_url" name="image_url" value="<?= h($edit['image_url'] ?? '') ?>" placeholder="مثال: Suakin1.png">
      </div>
    </div>
    <label for="description">الوصف</label>
    <!-- حقل نصي كبير للوصف (textarea) -->
    <textarea id="description" name="description"><?= h($edit['description'] ?? '') ?></textarea>
    <div class="form-actions">
      <button class="btn" type="submit"><?= svg_icon('save') ?> حفظ</button>
      <?php if ($edit): ?>
        <!-- زر إلغاء يظهر بس لو في تعديل — يرجع لصفحة المناطق بدون تعديل -->
        <a class="btn secondary" href="<?= url('admin/places.php') ?>"><?= svg_icon('x') ?> إلغاء التعديل</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- قسم عرض المناطق في جدول -->
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
      <!-- نلف على كل منطقة في نتيجة الاستعلام -->
      <?php while ($row = $places->fetch_assoc()): ?>
        <tr>
          <td data-label="#"><?= (int)$row['id'] ?></td>
          <!-- الصورة المصغرة للمنطقة — بنجيبها من assets -->
          <td data-label="الصورة"><img class="table-thumb" src="<?= h(place_cover_url($row)) ?>" alt="<?= h($row['name']) ?>"></td>
          <td data-label="الاسم"><b><?= h($row['name']) ?></b></td>
          <td data-label="الموقع"><?= h($row['location']) ?></td>
          <!-- التصنيف يظهر في badge صغير -->
          <td data-label="التصنيف"><span class="badge"><?= h($row['category']) ?></span></td>
          <!-- اختصار الوصف لأول 60 حرف عشان ما يطول الجدول -->
          <td data-label="الوصف" class="text-sm"><?= h(text_excerpt($row['description'] ?? '', 60)) ?></td>
          <!-- أزرار الإجراءات: تعديل + حذف -->
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

<?php
// هنا نستدعي دالة admin_footer() عشان تقفل الصفحة وتطبع الفوتر
admin_footer();
?>
