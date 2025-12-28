<!-- signup_view.php -->
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sign Up - Mental Health Tracker</title>
  <style>
    :root{--bg:#f4f8fb;--card:#fff;--accent:#8ac6d1;--muted:#6b7280}
    *{box-sizing:border-box}
    body{font-family:Inter, Arial, sans-serif;margin:0;background:var(--bg);color:#111}
    .wrap{max-width:900px;margin:36px auto;padding:20px}
    header{display:flex;justify-content:space-between;align-items:center;padding:12px 0}
    header h1{color:var(--accent);margin:0}
    .card{background:var(--card);padding:22px;border-radius:12px;box-shadow:0 10px 30px rgba(8,20,40,.06);max-width:720px;margin:20px auto}
    h2{margin:0 0 8px 0}
    .row{display:flex;gap:12px}
    .col{flex:1}
    label{display:block;font-size:0.85rem;margin-top:12px;color:var(--muted)}
    input, textarea, select{width:100%;padding:10px;margin-top:8px;border-radius:8px;border:1px solid #e1e6ea}
    textarea{min-height:100px;resize:vertical}
    button{margin-top:16px;padding:10px 14px;border-radius:8px;border:0;background:var(--accent);color:#fff;font-weight:700;cursor:pointer}
    .small{text-align:center;margin-top:10px;color:var(--muted)}
    @media (max-width:720px){ .row{flex-direction:column} }
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Mental Health Tracker</h1>
      <nav><a href="home.php" style="color:var(--accent);text-decoration:none">Home</a></nav>
    </header>

    <div class="card" role="main" aria-label="Signup form">
      <h2>Create your account</h2>
      <p style="color:var(--muted);margin-top:6px">We respect your privacy. Friend's number is used only for emergency alerts (with consent).</p>

      <!-- posts to signup.php -->
      <form action="signup.php" method="post" autocomplete="on">
        <div class="row">
          <div class="col">
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" required placeholder="Your full name">
          </div>
          <div class="col">
            <label for="dob">Date of birth</label>
            <input id="dob" name="dob" type="date">
          </div>
        </div>

        <div class="row">
          <div class="col">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required placeholder="you@example.com">
          </div>
          <div class="col">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
              <option value="">Prefer not to say</option>
              <option value="female">Female</option>
              <option value="male">Male</option>
              <option value="other">Other</option>
            </select>
          </div>
        </div>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" required placeholder="Choose a strong password">

        <label for="interests">Interests (comma separated)</label>
        <input id="interests" name="interests" placeholder="music,walking,reading">

        <label for="friend_phone">Friend phone (for alerts) — include country code</label>
        <input id="friend_phone" name="friend_phone" type="tel" placeholder="+91XXXXXXXXXX">

        <label for="signup_answers">Quick questions / notes (optional)</label>
        <textarea id="signup_answers" name="signup_answers" placeholder="Any notes you'd like to share"></textarea>

        <!-- SUBMIT button -->
        <button type="submit" name="signup">Sign up</button>

        <p class="small">Already have an account? <a href="login.php">Log in</a></p>
      </form>
    </div>
  </div>
</body>
</html>
