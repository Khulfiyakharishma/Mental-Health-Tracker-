<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mental Health Tracker</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: #f4f8fb;
      color: #333;
    }

    header {
      background: #8ac6d1;
      padding: 20px 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header h1 {
      color: #fff;
      font-weight: 600;
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: #fff;
      font-weight: 500;
      transition: 0.3s;
    }

    nav a:hover {
      color: #2f2f2f;
    }

    .hero {
      text-align: center;
      padding: 80px 20px;
      background: linear-gradient(120deg, #8ac6d1, #b8e0d2);
      color: white;
    }

    .hero h2 {
      font-size: 2.5rem;
    }

    .hero p {
      margin-top: 15px;
      font-size: 1.2rem;
    }

    .hero .btn {
      background: #fff;
      color: #2f2f2f;
      padding: 12px 30px;
      border-radius: 25px;
      text-decoration: none;
      display: inline-block;
      margin-top: 25px;
      font-weight: 500;
    }

    section {
      padding: 60px 80px;
    }

    h2.section-title {
      text-align: center;
      margin-bottom: 30px;
      color: #444;
    }

    .about p, .why p {
      max-width: 900px;
      margin: 0 auto;
      text-align: center;
      line-height: 1.7;
    }

    .reviews {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
    }

    .review {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      width: 280px;
      padding: 20px;
      text-align: center;
    }

    .review h4 {
      margin-top: 10px;
      color: #8ac6d1;
    }

    .feedback {
      background: #e3f6f5;
      border-radius: 10px;
      padding: 30px;
      width: 70%;
      margin: 0 auto;
    }

    .feedback input, .feedback textarea {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .feedback button {
      background: #8ac6d1;
      color: #fff;
      border: none;
      padding: 10px 25px;
      border-radius: 20px;
      cursor: pointer;
    }

    footer {
      text-align: center;
      background: #8ac6d1;
      color: white;
      padding: 15px;
      margin-top: 40px;
    }

  </style>
</head>
<body>

  <header>
    <h1>Mental Health Tracker</h1>
    <nav>
      <a href="#about">About Us</a>
      <a href="#why">Why It Matters</a>
      <a href="#reviews">Reviews</a>
      <a href="#feedback">Feedback</a>
      <a href="login_view.php">Login</a>
    </nav>
  </header>

  <div class="hero">
    <h2>Track Your Emotions, Understand Yourself Better 💚</h2>
    <p>Your daily companion for self-reflection, journaling, and mental wellness.</p>
    <a href="signup_view.php" class="btn">Get Started</a>
  </div>

  <section id="about" class="about">
    <h2 class="section-title">About Us</h2>
    <p>
      We believe mental health is as important as physical health. Our platform helps users
      express their emotions, track daily moods, and reflect through journals. With insights,
      motivational quotes, and mindful suggestions — we aim to make mental wellness a daily habit.
    </p>
  </section>

  <section id="why" class="why">
    <h2 class="section-title">Why Mental Health Matters</h2>
    <p>
      Mental health affects how we think, feel, and act. It influences how we handle stress,
      relate to others, and make choices. By tracking your emotions regularly, you can identify
      patterns, prevent burnout, and develop emotional resilience. It’s okay to not be okay —
      but it’s powerful to understand why.
    </p>
  </section>

  <section id="reviews">
    <h2 class="section-title">What Our Users Say</h2>
    <div class="reviews">
      <div class="review">
        <p>“This app helped me understand my emotions better. The daily journal keeps me grounded.”</p>
        <h4>– Aisha R.</h4>
      </div>
      <div class="review">
        <p>“I love how the dashboard shows my mood trends. The quotes make my day brighter.”</p>
        <h4>– Rajesh K.</h4>
      </div>
      <div class="review">
        <p>“The gentle reminders and activities helped me stay mindful during tough times.”</p>
        <h4>– Meera L.</h4>
      </div>
    </div>
  </section>

  <section id="feedback">
    <h2 class="section-title">Design Feedback / Share Your Thoughts</h2>
    <div class="feedback">
      <form>
        <input type="text" placeholder="Your Name" required>
        <input type="email" placeholder="Your Email" required>
        <textarea rows="5" placeholder="Your Feedback..."></textarea>
        <button type="submit">Submit Feedback</button>
      </form>
    </div>
  </section>

  <footer>
    <p>© 2025 Mental Health Tracker | Made with 💚 for well-being</p>
  </footer>

</body>
</html>
