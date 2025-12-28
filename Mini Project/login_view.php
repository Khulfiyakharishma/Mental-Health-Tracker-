<?php
// login_view.php
// Styled login view that POSTS to your existing login.php (no backend changes)
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Login — Mental Health Tracker</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#f3eefb;
    --violet1:#654ea3;
    --violet2:#eaafc8;
    --accent:#7b61ff;
    --card:#fff;
    --muted:#6b6b7a;
  }
  *{box-sizing:border-box}
  body{font-family:Inter,system-ui,Segoe UI,Arial;background:linear-gradient(180deg,var(--bg),#f7f5ff);margin:0;padding:28px;min-height:100vh}
  .wrap{max-width:1100px;margin:12px auto;display:flex;gap:28px;align-items:center}
  .left{
    flex:1;padding:38px;border-radius:20px;background:linear-gradient(135deg,var(--violet1),#9b59b6);color:white;box-shadow:0 12px 40px rgba(99,64,255,0.12);
  }
  .left h1{font-size:2rem;margin:0 0 12px 0}
  .left p{opacity:.95;line-height:1.5}
  .hero-anim{margin-top:18px;display:flex;gap:12px;align-items:center}
  .floating-card{width:72px;height:72px;border-radius:14px;background:rgba(255,255,255,0.13);display:flex;align-items:center;justify-content:center;font-size:28px;backdrop-filter: blur(4px);animation:float 4s ease-in-out infinite}
  @keyframes float {0%{transform:translateY(0)}50%{transform:translateY(-10px)}100%{transform:translateY(0)}}

  .right{width:420px;background:var(--card);padding:20px;border-radius:16px;box-shadow:0 12px 30px rgba(40,20,80,.06)}
  h2{margin:0 0 6px 0;font-size:1.2rem;color:#2b2b3a}
  label{display:block;margin-top:12px;color:var(--muted);font-size:0.9rem}
  input{width:100%;padding:12px;border-radius:10px;border:1px solid #eee;margin-top:8px}
  button{margin-top:16px;width:100%;padding:12px;border-radius:10px;border:0;background:linear-gradient(90deg,var(--accent),#936bff);color:white;font-weight:700;cursor:pointer}
  .muted{font-size:.85rem;color:var(--muted);text-align:center;margin-top:12px}
  .ghost{background:transparent;border:1px solid #eee;color:#555;padding:10px;border-radius:10px;width:100%}
  .or{display:flex;align-items:center;gap:12px;margin:14px 0}
  .or div{flex:1;height:1px;background:#eee}
  .small-cta{display:flex;gap:8px;justify-content:center;margin-top:8px}
  a{text-decoration:none;color:var(--accent);font-weight:600}
  @media(max-width:980px){.wrap{flex-direction:column;padding:12px}.right{width:100%}}
</style>
</head>
<body>
  <div class="wrap">
    <div class="left" aria-hidden="false">
      <h1>Hello — welcome back</h1>
      <p>Track your mood with a tap. Share quick journals, get gentle suggestions and mindful reminders — all in one safe place.</p>
      <div class="hero-anim">
        <div class="floating-card" title="Happy">😊</div>
        <div class="floating-card" title="Breathe">🧘</div>
        <div class="floating-card" title="Walk">🚶</div>
      </div>
    </div>

    <div class="right" role="region" aria-label="Login form">
      <h2>Sign in to continue</h2>

      <!-- POSTS to your login.php (unchanged backend) -->
      <form action="login.php" method="post" autocomplete="on">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" required placeholder="you@example.com">

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required placeholder="Your password">

        <button type="submit">Login</button>
      </form>

      <div class="or" aria-hidden="true"><div></div><div style="font-size:.85rem;color:#aaa">or</div><div></div></div>
      <div class="small-cta">
        <a href="signup.php" class="ghost">Create account</a>
      </div>
      <div class="muted">Don't have an account? <a href="signup.php">Sign up</a></div>
    </div>
  </div>
</body>
</html>
