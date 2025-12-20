<?php
// includes/header.php
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CloudConnect</title>

<style>
:root{
  --bg: #f4f7fb;
  --card-bg: #ffffff;
  --text: #0b1220;
  --muted: #6b7280;
  --accent: #4f8bff;
  --border: rgba(0,0,0,.08);
}

body.dark{
  --bg:#0b1220;
  --card-bg:#111827;
  --text:#e5e7eb;
  --muted:#9ca3af;
  --border: rgba(255,255,255,.08);
}

*{box-sizing:border-box}
body{
  margin:0;
  font-family: Inter, system-ui, Arial;
  background:var(--bg);
  color:var(--text);
}

/* ===== HEADER ===== */
.cc-header{
  position: fixed;
  top:0;
  left:0;
  right:0;
  height:64px;
  background:var(--card-bg);
  border-bottom:1px solid var(--border);
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding:0 24px;
  z-index:100;
}

.cc-header .brand{
  display:flex;
  align-items:center;
  gap:10px;
  font-weight:700;
  font-size:18px;
}

/* Cloud icon wrapper */
.cc-header .logo{
  display:flex;
  align-items:center;
  justify-content:center;
  width:34px;
  height:34px;
}

/* Theme button */
.cc-header .actions button{
  padding:8px 14px;
  border-radius:10px;
  border:1px solid var(--border);
  background:transparent;
  cursor:pointer;
  font-weight:600;
  color:var(--text);
}

/* ===== PAGE OFFSET ===== */
.cc-app{
  padding-top:64px; /* pushes content below header */
}
</style>
</head>

<body>

<header class="cc-header">
  <div class="brand">

    <!-- ✅ CLOUD ICON (Option 1) -->
    <span class="logo">
      <svg width="22" height="22" viewBox="0 0 64 64" fill="none"
           xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="cloudGrad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#77A9FF"/>
            <stop offset="100%" stop-color="#7C5BFF"/>
          </linearGradient>
        </defs>
        <path
          d="M45 36c6 0 11-5 11-11s-5-11-11-11
             c-1.3 0-2.6.2-3.8.6
             A12 12 0 0 0 20 30.4
             A8 8 0 0 0 28 54h17z"
          fill="url(#cloudGrad)"
        />
      </svg>
    </span>

    CloudConnect
  </div>

  <div class="actions">
    <button id="themeToggle">🌙 Dark Mode</button>
  </div>
</header>

<script>
(function(){
  const btn = document.getElementById('themeToggle');
  const body = document.body;
  const saved = localStorage.getItem('cloudconnect_theme');

  if(saved === 'dark'){
    body.classList.add('dark');
    btn.textContent = '☀️ Light Mode';
  }

  btn.onclick = () => {
    body.classList.toggle('dark');
    const dark = body.classList.contains('dark');
    localStorage.setItem('cloudconnect_theme', dark ? 'dark' : 'light');
    btn.textContent = dark ? '☀️ Light Mode' : '🌙 Dark Mode';
  };
})();
</script>
