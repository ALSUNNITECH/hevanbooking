</div><!-- /.container.page -->

<!-- الفوتر: آخر الصفحة، فيه حقوق النشر واسم المشروع -->
<div class="footer">
  <div class="container">
    <!-- عرض السنة الحالية تلقائياً ونص حقوق الملكية -->
    <p>© <?= date('Y') ?> Hevan Booking - مشروع طلابي للمناقشة</p>
  </div>
</div>

<!--
سكريبت الدارك مود:
- يشتغل تلقائياً أول ما الصفحة تتحمل
- يخزن الوضع في localStorage عشان يفضل محفوظ حتى لو غيرت الصفحة
- يغير سمة <html> بين light و dark
- لو الزرار موجود بيحدث النص الموصوف (aria-label) عشان الوصولية
-->
<script>
(function () {
  var root = document.documentElement;        // العنصر <html> اللي بنغير عليه السمة
  var button = document.getElementById('themeToggle'); // زرار التبديل لو موجود
  var key = 'hevan-theme';                    // المفتاح اللي بنستخدمه في التخزين المحلي

  // دالة تغيير السمة: تضبط data-theme على <html> وتحفظ الخيار وتحدث وصف الزرار
  function setTheme(theme) {
    root.setAttribute('data-theme', theme);
    try { localStorage.setItem(key, theme); } catch (e) {}
    if (button) button.setAttribute('aria-label', theme === 'dark' ? 'تفعيل الوضع الفاتح' : 'تفعيل الوضع الداكن');
  }

  // نشوف الوضع الحالي من السمة (أو نبدأ فاتح لو مافي سمة)
  var current = root.getAttribute('data-theme') || 'light';
  setTheme(current);

  // لو الزرار موجود، نضيف له حدث الضغط عشان يبدل بين dark و light
  if (button) {
    button.addEventListener('click', function () {
      setTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
  }
})();
</script>

</body>
</html>
