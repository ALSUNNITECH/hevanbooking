</div><!-- /.container.page -->

<div class="footer">
  <div class="container">
    <p>© <?= date('Y') ?> Hevan Booking - مشروع طلابي للمناقشة</p>
  </div>
</div>

<script>
(function(){
  const key = 'hevan-theme';
  const root = document.documentElement;
  const btn = document.getElementById('themeToggle');

  function apply(theme) {
    root.setAttribute('data-theme', theme);
    localStorage.setItem(key, theme);
    if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
  }

  const saved = localStorage.getItem(key);
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  apply((saved === 'light' || saved === 'dark') ? saved : (prefersDark ? 'dark' : 'light'));

  if (btn) {
    btn.addEventListener('click', function() {
      const current = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      apply(current);
    });
  }
})();
</script>

</body>
</html>
