<?php
require_once __DIR__ . '/auth.php';
$title = 'تسجيل دخول الإداري';
$error = '';

if (is_admin()) {
    redirect('admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (login_user($username, $password)) {
        redirect('admin/index.php');
    } else {
        $error = '❌ اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="card form-card" style="max-width:480px;margin:40px auto">
  <h1>🔐 تسجيل دخول الإداري</h1>

  <div class="alert info">
    💡 بيانات الدخول التجريبية:<br>
    المستخدم: <b>admin</b> &nbsp;|&nbsp; كلمة المرور: <b>admin123</b>
  </div>

  <?php if ($error): ?>
    <div class="alert error"><?= h($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <label for="username">👤 اسم المستخدم</label>
    <input type="text" id="username" name="username" required autofocus>

    <label for="password">🔑 كلمة المرور</label>
    <input type="password" id="password" name="password" required>

    <div class="form-actions" style="margin-top:20px">
      <button class="btn" type="submit">دخول ➡️</button>
    </div>
  </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
