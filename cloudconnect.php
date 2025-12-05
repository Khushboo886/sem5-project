<?php
// cloudconnect_landing.php
// Single-file PHP + HTML landing page for "CloudConnect".
// This file is static (no DB). Save as cloudconnect_landing.php and run with: php -S localhost:8000
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>CloudConnect — Employee Management</title>
  <meta name="description" content="CloudConnect — modern employee management & HR dashboard" />
  <style>
    :root{
      --bg-grad: linear-gradient(135deg,#08122f 0%, #0b2a5f 30%, #3b1c6b 70%, #2b0f46 100%);
      --text: #E6EEF8;
      --muted: rgba(230,238,248,0.85);
      --card-bg: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.02));
      --glass-border: rgba(255,255,255,0.03);
      --btn-grad: linear-gradient(135deg,#5a8eff,#3b5bff);
      --btn-shadow: rgba(47,91,255,0.35);
      --accent: #4f8bff;
    }

    /* Light mode overrides */
    body.light{
      --bg-grad: linear-gradient(135deg,#f5f7fa,#e6ecf5,#dce3ff);
      --text: #0b1220;
      --muted: rgba(11,18,32,0.65);
      --card-bg: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(245,247,255,0.6));
      --glass-border: rgba(13,30,60,0.06);
      --btn-grad: linear-gradient(135deg,#2f5bff,#5a8eff);
      --btn-shadow: rgba(47,91,255,0.12);
      --accent: #2f5bff;
    }

    /* Reset */
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{
      font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
      color:var(--text);
      background: var(--bg-grad);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      overflow-x:hidden;
      transition:background .45s ease,color .3s ease;
    }

    /* Page container */
    .wrap{max-width:1200px;margin:40px auto;padding:36px}

    /* Top navigation */
    .nav{display:flex;align-items:center;justify-content:space-between}
    .brand{display:flex;align-items:center;gap:12px}
    .logo{width:48px;height:48px;display:inline-flex;align-items:center;justify-content:center;border-radius:10px;background:rgba(255,255,255,0.04);backdrop-filter:blur(6px);}
    .logo svg{display:block;width:28px;height:28px}
    .brand h1{font-size:18px;color:var(--text);font-weight:600}

    .cta{display:flex;gap:12px;align-items:center}
    .btn{padding:10px 18px;border-radius:10px;border:0;cursor:pointer;font-weight:600;background:transparent;color:var(--text);border:1px solid rgba(255,255,255,0.06)}
    .btn-ghost{background:transparent}
    .btn-solid{background:var(--btn-grad);color:white;box-shadow:0 8px 30px var(--btn-shadow);border:1px solid rgba(255,255,255,0.06)}

    /* Hero */
    .hero{display:grid;grid-template-columns:1fr 520px;gap:40px;align-items:center;margin-top:40px}
    .hero-left{max-width:640px}
    .eyebrow{display:inline-block;padding:6px 10px;background:rgba(255,255,255,0.04);border-radius:999px;font-size:13px;margin-bottom:18px;color:var(--text)}
    .headline{font-size:64px;line-height:1.03;font-weight:700;color:var(--text);margin-bottom:18px}
    .sub{font-size:18px;color:var(--muted);margin-bottom:28px}
    .primary-cta{display:inline-block;padding:18px 34px;border-radius:14px;background:var(--btn-grad);font-size:20px;font-weight:700;box-shadow:0 8px 30px var(--btn-shadow);transform:translateY(0);transition:all .25s ease;border:1px solid rgba(255,255,255,0.18);position:relative;overflow:hidden;color:#fff}
    .primary-cta::before{content:"";position:absolute;top:0;left:-120%;width:120%;height:100%;background:linear-gradient(120deg,rgba(255,255,255,0.15),rgba(255,255,255,0));transform:skewX(-20deg);transition:0.6s;}
    .primary-cta:hover::before{left:120%;}
    .primary-cta:hover{transform:translateY(-5px) scale(1.03);box-shadow:0 15px 40px rgba(47,91,255,0.55);} 

    /* Right mockup card (glass) */
    .mockup{width:520px;margin-left:auto;border-radius:18px;padding:22px;background:var(--card-bg);box-shadow:0 30px 70px rgba(2,6,23,0.6);backdrop-filter:blur(8px);transform:translateY(20px) rotate(-6deg);transition:all .4s ease}
    .mockup .row{display:flex;gap:12px;margin-bottom:18px}
    .card{flex:1;padding:16px;border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));border:1px solid var(--glass-border);}
    .card .num{font-size:22px;font-weight:700;color:var(--text)}
    .card .label{font-size:12px;color:var(--muted)}

    .dir{margin-top:10px;border-radius:12px;overflow:hidden;border:1px solid var(--glass-border)}
    .dir header{display:flex;justify-content:space-between;padding:14px 16px;background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.005));font-weight:700;color:var(--text)}
    .dir table{width:100%;border-collapse:collapse}
    .dir tr{border-top:1px solid rgba(255,255,255,0.02)}
    .dir td{padding:12px 16px;font-size:14px;color:var(--text)}
    .avatar{width:36px;height:36px;border-radius:999px;display:inline-block;vertical-align:middle;margin-right:10px;object-fit:cover;border:2px solid rgba(255,255,255,0.03)}
    .status{display:inline-block;padding:6px 10px;border-radius:999px;font-size:12px}
    .status.active{background:linear-gradient(90deg,#1db954,#39d353);color:#081018}
    .status.inactive{background:linear-gradient(90deg,#ff6b6b,#ff8a8a);color:#140b0b}

    /* small screens */
    @media (max-width:980px){
      .hero{grid-template-columns:1fr;gap:28px}
      .mockup{width:100%;transform:none}
      .headline{font-size:44px}
    }

    /* entrance animations */
    .fade-in{opacity:0;transform:translateY(12px);animation:fadeInUp .9s forwards}
    .delay-1{animation-delay:.08s}
    .delay-2{animation-delay:.16s}
    .delay-3{animation-delay:.24s}
    @keyframes fadeInUp{to{opacity:1;transform:none}}

    /* subtle animated background shapes */
    .bg-shape{position:fixed;right:-120px;bottom:-120px;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle at 30% 30%, rgba(79,139,255,0.18), rgba(79,139,255,0.06) 40%, transparent 60%);filter:blur(40px);pointer-events:none;transition:all .45s ease}
    .bg-shape-2{position:fixed;left:-100px;top:-100px;width:420px;height:420px;border-radius:50%;background:radial-gradient(circle at 60% 60%, rgba(90,50,255,0.14), rgba(90,50,255,0.05) 40%, transparent 60%);filter:blur(40px);pointer-events:none;transition:all .45s ease}

    /* small footer */
    footer{margin-top:60px;color:var(--muted);font-size:13px;text-align:center}

  </style>
</head>
<body>
  <div class="bg-shape"></div>
  <div class="bg-shape-2"></div>

  <main class="wrap">
    <nav class="nav fade-in delay-1">
      <div class="brand">
        <div class="logo" aria-hidden="true">
          <!-- Cloud SVG logo -->
          <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <linearGradient id="g1" x1="0" x2="1" y1="0" y2="1">
                <stop offset="0" stop-color="#77A9FF"/>
                <stop offset="1" stop-color="#7C5BFF"/>
              </linearGradient>
            </defs>
            <path d="M45 36c6 0 11-5 11-11s-5-11-11-11c-1.3 0-2.6.2-3.8.6A12 12 0 0 0 20 30.4 8 8 0 0 0 28 54h17z" fill="url(#g1)"/>
          </svg>
        </div>
        <h1>CloudConnect</h1>
      </div>

      <div class="cta">
        <button id="themeToggle" class="btn btn-ghost">🌙 Dark Mode</button>
      </div>
    </nav>

    <section class="hero">
      <div class="hero-left">
        <div class="eyebrow fade-in delay-2">Enterprise HR · Cloud-first</div>
        <h2 class="headline fade-in delay-3">Manage Your Employees Effectively</h2>
        <p class="sub fade-in delay-3">Streamline your employee management process with CloudConnect — track employee information, manage roles, and improve productivity with a secure, cloud-native HR platform.</p>

        <a href="company_register.php" class="primary-cta fade-in delay-3">Get Started</a>
      </div>

      <!-- mockup -->
      <div class="mockup fade-in delay-2" role="img" aria-label="CloudConnect dashboard preview">
        <div class="row">
          <div class="card">
            <div class="num">120</div>
            <div class="label">Employees</div>
          </div>
          <div class="card">
            <div class="num">24</div>
            <div class="label">Departments</div>
          </div>
          <div class="card">
            <div class="num">16</div>
            <div class="label">On Leave</div>
          </div>
          <div class="card">
            <div class="num">32</div>
            <div class="label">Attendance</div>
          </div>
        </div>

        <div class="dir">
          <header>
            <div>Employee Directory</div>
            <div style="opacity:.8;font-size:13px">Quick view</div>
          </header>
          <table>
            <tbody>
              <tr>
                <td><img class="avatar" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='36' height='36'><rect fill='%232f5bff' width='36' height='36' rx='18'/></svg>" alt="Eleanor"> <strong>Eleanor Pena</strong></td>
                <td style="color:rgba(230,238,248,0.6)">Marketing Coordinator</td>
                <td><span class="status active">Active</span></td>
                <td style="text-align:right;opacity:.8">1001</td>
              </tr>
              <tr>
                <td><img class="avatar" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='36' height='36'><rect fill='%236b2fff' width='36' height='36' rx='18'/></svg>" alt="Cody"> <strong>Cody Fischr</strong></td>
                <td style="color:rgba(230,238,248,0.6)">HR Manager</td>
                <td><span class="status inactive">Inactive</span></td>
                <td style="text-align:right;opacity:.8">1002</td>
              </tr>
              <tr>
                <td><img class="avatar" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='36' height='36'><rect fill='%2322c1ff' width='36' height='36' rx='18'/></svg>" alt="Courtney"> <strong>Courtney Henry</strong></td>
                <td style="color:rgba(230,238,248,0.6)">Sales Representative</td>
                <td><span class="status active">Active</span></td>
                <td style="text-align:right;opacity:.8">1003</td>
              </tr>
              <tr>
                <td><img class="avatar" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='36' height='36'><rect fill='%232a8cff' width='36' height='36' rx='18'/></svg>" alt="Bessie"> <strong>Bessie Cooper</strong></td>
                <td style="color:rgba(230,238,248,0.6)">Software Engineer</td>
                <td><span class="status active">Active</span></td>
                <td style="text-align:right;opacity:.8">1004</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </section>

    <footer>
      © <?php echo date('Y'); ?> CloudConnect — Built with care
    </footer>
  </main>

  <script>
    // Lightweight interaction: subtle parallax for the mockup on mouse move
    (function(){
      const mock = document.querySelector('.mockup');
      if(!mock) return;
      let rect = mock.getBoundingClientRect();
      function onMove(e){
        const x = (e.clientX - (rect.left + rect.width/2)) / rect.width;
        const y = (e.clientY - (rect.top + rect.height/2)) / rect.height;
        mock.style.transform = `translateY(${8 - y*12}px) rotate(${ -6 - x*6 }deg) translateX(${ -x*8 }px)`;
      }
      function onResize(){rect = mock.getBoundingClientRect()}
      window.addEventListener('mousemove', onMove);
      window.addEventListener('resize', onResize);
    })();

    // Theme toggle with persistence and UI feature swaps
    (function(){
      const toggleBtn = document.getElementById('themeToggle');
      const body = document.body;
      // Initialize from localStorage or prefers-color-scheme
      const saved = localStorage.getItem('cloudconnect_theme');
      if(saved === 'light'){
        body.classList.add('light');
        toggleBtn.textContent = '🌙 Dark Mode';
      } else if(saved === 'dark'){
        body.classList.remove('light');
        toggleBtn.textContent = '☀️ Light Mode';
      } else {
        // default to user's OS preference
        const prefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
        if(prefersLight){ body.classList.add('light'); toggleBtn.textContent = '🌙 Dark Mode'; }
        else { body.classList.remove('light'); toggleBtn.textContent = '☀️ Light Mode'; }
      }

      toggleBtn.addEventListener('click', ()=>{
        const nowLight = body.classList.toggle('light');
        if(nowLight){
          toggleBtn.textContent = '🌙 Dark Mode';
          localStorage.setItem('cloudconnect_theme','light');
        } else {
          toggleBtn.textContent = '☀️ Light Mode';
          localStorage.setItem('cloudconnect_theme','dark');
        }
        // small animation for background shapes
        document.querySelectorAll('.bg-shape, .bg-shape-2').forEach(el=>el.style.transform = 'scale(0.98)');
        setTimeout(()=>document.querySelectorAll('.bg-shape, .bg-shape-2').forEach(el=>el.style.transform = ''),220);
      });

    })();

    // Accessibility: keyboard activation for primary CTA
    document.querySelectorAll('.primary-cta, .btn').forEach(el=>{
      el.addEventListener('keydown', (e)=>{ if(e.key === 'Enter' || e.key === ' ') e.click(); });
    });
  </script>
</body>
</html>
