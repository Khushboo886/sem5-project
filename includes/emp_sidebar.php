<?php
// includes/emp_sidebar.php
// Sidebar for employee pages — matches admin sidebar UI & behavior.

define('BASE_URL', '/cloudconnect');

// Determine active page
$current = basename($_SERVER['PHP_SELF']);

function is_active($names) {
    global $current;
    if (!is_array($names)) $names = [$names];
    foreach ($names as $n) {
        if (strpos($current, $n) !== false) return true;
    }
    return false;
}
?>
<style>
/* Uses SAME CSS as admin sidebar for perfect consistency */
.cc-sidebar {
  --w: 260px;
  position: fixed;
  left: 20px;
  top: 84px;              /* ⬅ header height (64px) + gap */
  bottom: 20px;
  width: var(--w);
  background: var(--card-bg);
  border: 1px solid var(--glass-border);
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: 0 12px 30px rgba(2,6,23,0.35);
  transition: width .32s cubic-bezier(.2,.9,.2,1), transform .25s ease, opacity .25s ease;
  z-index: 40;
  overflow: hidden;
}

.cc-sidebar.collapsed { width: 72px; }

.cc-sidebar .brand {
  display:flex;
  align-items:center;
  gap:12px;
}

.cc-sidebar .logo {
  width:42px;height:42px;border-radius:10px;
  display:inline-flex;align-items:center;justify-content:center;
  background:linear-gradient(135deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
  border:1px solid rgba(255,255,255,0.03);
}

.cc-sidebar h3 { font-size:16px; margin:0; color:var(--text); font-weight:700; }
.cc-sidebar p { margin:0; font-size:12px; color:var(--muted); }

.cc-nav { display:flex;flex-direction:column; gap:6px; margin-top:8px; }

.cc-nav a {
  display:flex;
  gap:12px;
  align-items:center;
  padding:10px;
  border-radius:10px;
  color:var(--text);
  text-decoration:none;
  transition: background .22s ease, transform .14s ease, color .22s ease;
  position:relative;
}

.cc-nav a:hover {
  background: linear-gradient(90deg, rgba(79,139,255,0.06), rgba(125,80,255,0.04));
  transform:translateX(6px);
}

.cc-nav a.active {
  background: linear-gradient(90deg, rgba(79,139,255,0.12), rgba(125,80,255,0.08));
  border-left: 3px solid var(--accent);
  box-shadow: 0 8px 24px rgba(47,91,255,0.08);
}

.cc-nav .icon { width:20px; height:20px; display:inline-flex; }
.cc-nav .label { font-weight:600; font-size:14px; }

.cc-sidebar.collapsed .label,
.cc-sidebar.collapsed .brand h3,
.cc-sidebar.collapsed .brand p {
  opacity:0;
  pointer-events:none;
}

.cc-nav a .tooltip {
  display:none;
  position:absolute;
  left:calc(var(--w) + 10px);
  top:50%;
  transform:translateY(-50%);
  background:var(--card-bg);
  border:1px solid var(--glass-border);
  padding:8px 10px;
  border-radius:8px;
  font-size:13px;
  box-shadow:0 10px 30px rgba(2,6,23,0.3);
}

.cc-sidebar.collapsed .cc-nav a:hover .tooltip { display:block; }

.sidebar-footer {
  margin-top:auto;
  display:flex;
  justify-content:space-between;
  align-items:center;
}

.cc-toggle-btn {
  padding:8px;
  border-radius:10px;
  border:0;
  cursor:pointer;
  background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
  color:var(--text);
}
</style>

<aside class="cc-sidebar" id="ccSidebar" aria-label="Employee sidebar">

  <!-- BRAND -->
  <div class="brand">
    <div class="logo">
      <svg width="28" height="28" viewBox="0 0 64 64" fill="none">
        <defs>
          <linearGradient id="empbg" x1="0" x2="1" y1="0" y2="1">
            <stop offset="0" stop-color="#77A9FF"/>
            <stop offset="1" stop-color="#7C5BFF"/>
          </linearGradient>
        </defs>
        <path d="M45 36c6 0 11-5 11-11s-5-11-11-11c-1.3 0-2.6.2-3.8.6A12 12 0 0 0 20 30.4 8 8 0 0 0 28 54h17z" fill="url(#empbg)"/>
      </svg>
    </div>
    <div>
      <h3>CloudConnect</h3>
      <p>Employee Panel</p>
    </div>
  </div>

  <!-- NAV -->
  <nav class="cc-nav">
    <a href="<?= BASE_URL ?>/employee/dashboard.php" class="<?= is_active('dashboard.php')?'active':'' ?>">
      <span class="icon">🏠</span>
      <span class="label">Dashboard</span>
      <span class="tooltip">Dashboard</span>
    </a>

    <a href="<?= BASE_URL ?>/employee/attendance.php" class="<?= is_active('attendance.php')?'active':'' ?>">
      <span class="icon">⏱️</span>
      <span class="label">Attendance</span>
      <span class="tooltip">Attendance</span>
    </a>

    <a href="<?= BASE_URL ?>/employee/leaves.php" class="<?= is_active('leaves.php')?'active':'' ?>">
      <span class="icon">📝</span>
      <span class="label">Leaves</span>
      <span class="tooltip">Leaves</span>
    </a>

     <a href="<?= BASE_URL ?>/employee/announcements.php" class="<?= is_active('announcements.php')?'active':'' ?>"><span class="icon" aria-hidden="true">
        <!-- megaphone/announcement icon -->
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 11v4a2 2 0 0 0 2 2h1l5 3V6L6 9H5a2 2 0 0 0-2 2z" fill="currentColor" opacity="0.9"/><path d="M16 8l4-2v10l-4-2V8z" fill="currentColor" opacity="0.6"/></svg>
      </span>
      <span class="label">Announcements</span>
      <span class="tooltip">Company Announcements</span>
    </a>
      

    <a href="<?= BASE_URL ?>/employee/documents.php" class="<?= is_active('documents.php')?'active':'' ?>">
      <span class="icon">📂</span>
      <span class="label">Documents</span>
      <span class="tooltip">Documents</span>
    </a>

    <a href="<?= BASE_URL ?>/employee/profile.php" class="<?= is_active('profile.php')?'active':'' ?>">
      <span class="icon">👤</span>
      <span class="label">My Profile</span>
      <span class="tooltip">Profile</span>
    </a>

    <a href="<?= BASE_URL ?>/logout.php" style="margin-top:8px;">
      <span class="icon">🚪</span>
      <span class="label">Logout</span>
      <span class="tooltip">Logout</span>
    </a>
  </nav>

  <!-- FOOTER -->
  <div class="sidebar-footer">
    <button id="ccCollapseBtn" class="cc-toggle-btn">◀</button>
    <span style="font-size:12px;color:var(--muted)">v1.0</span>
  </div>
</aside>

<script>
(function(){
  const sidebar = document.getElementById('ccSidebar');
  const btn = document.getElementById('ccCollapseBtn');
  const key = 'cc_sidebar_collapsed';

  if (localStorage.getItem(key) === '1') {
    sidebar.classList.add('collapsed');
    btn.textContent = '▶';
  }

  btn.onclick = () => {
    const collapsed = sidebar.classList.toggle('collapsed');
    btn.textContent = collapsed ? '▶' : '◀';
    localStorage.setItem(key, collapsed ? '1' : '0');
  };
})();
</script>
