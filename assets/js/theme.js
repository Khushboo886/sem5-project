(function () {
  const body = document.body;
  const KEY = 'cloudconnect_theme';

  // Support BOTH selectors
  const toggleBtn =
    document.getElementById('themeToggle') ||
    document.querySelector('[data-theme-toggle]');

  if (!toggleBtn) return;

  function apply(theme) {
    if (theme === 'light') {
      body.classList.add('light');
      toggleBtn.textContent = '🌙 Dark Mode';
    } else {
      body.classList.remove('light');
      toggleBtn.textContent = '☀️ Light Mode';
    }
    localStorage.setItem(KEY, theme);
  }

  // INITIAL LOAD
  const saved = localStorage.getItem(KEY);

  if (saved === 'light' || saved === 'dark') {
    apply(saved);
  } else {
    const prefersLight =
      window.matchMedia &&
      window.matchMedia('(prefers-color-scheme: light)').matches;
    apply(prefersLight ? 'light' : 'dark');
  }

  // TOGGLE HANDLER
  toggleBtn.addEventListener('click', () => {
    const nowLight = body.classList.toggle('light');
    apply(nowLight ? 'light' : 'dark');

    // Optional animation (only if shapes exist)
    document
      .querySelectorAll('.bg-shape, .bg-shape-2')
      .forEach(el => {
        el.style.transform = 'scale(0.98)';
        setTimeout(() => (el.style.transform = ''), 220);
      });
  });
})();
