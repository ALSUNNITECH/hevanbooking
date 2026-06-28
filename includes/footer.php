</div><!-- /.container.page -->

<div class="footer">
  <div class="container">
    <p>© <?= date('Y') ?> Hevan Booking - مشروع طلابي للمناقشة</p>
  </div>
</div>

<script>
(function () {
  var root = document.documentElement;
  var button = document.getElementById('themeToggle');
  var key = 'hevan-theme';

  function setTheme(theme) {
    root.setAttribute('data-theme', theme);
    try { localStorage.setItem(key, theme); } catch (e) {}
    if (button) button.setAttribute('aria-label', theme === 'dark' ? 'تفعيل الوضع الفاتح' : 'تفعيل الوضع الداكن');
  }

  var current = root.getAttribute('data-theme') || 'light';
  setTheme(current);

  if (button) {
    button.addEventListener('click', function () {
      setTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
    });
  }
})();
</script>

</body>
</html>
