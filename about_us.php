<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>NeuroAid - About Us</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f6f9f9;
    }

    header {
      background-color: white;
      border-bottom: 1px solid #ccc;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 15px 30px 5px 30px;
    }

    .top-bar-left {
      display: flex;
      align-items: center;
    }

    .top-bar-left img.logo {
      height: 40px;
      margin-right: 10px;
    }

    .top-bar-left h1 {
      color: #1D4C43;
      font-size: 32px;
      margin: 0;
      font-weight: 800;
    }

    .contact {
      font-size: 14px;
      color: #1D4C43;
      text-align: right;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
    }

    .contact span {
      display: flex;
      align-items: center;
    }

    .contact img {
      height: 16px;
      margin-right: 6px;
    }

    .contact small {
      margin-top: 4px;
    }

    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 30px 20px 30px;
      border-top: 1px solid #eee;
    }

    nav a {
      text-decoration: none;
      color: #1D4C43;
      font-weight: bold;
      font-size: 17px;
      transition: color 0.3s, background-color 0.3s;
      padding: 6px 10px;
      border-radius: 5px;
    }

    nav a:hover {
      color: white;
      background-color: #1D4C43;
    }

    .btn-login {
      font-weight: bold;
      font-size: 17px;
      border: 2px solid #1D4C43;
      background-color: white;
      color: #1D4C43;
      border-radius: 5px;
      text-decoration: none;
      padding: 6px 10px;
    }

    .btn-login:hover {
      background-color: #1D4C43;
      color: white;
    }

    .nav-links {
      display: flex;
      gap: 80px;
      margin-left: 100px;
    }

    .nav-login {
      display: flex;
      margin-right: 60px;
    }

    .container {
      width: 90%;
      max-width: 1200px;
      margin: auto;
    }

    .hero-banner {
      background-color: #1D4C43;
      color: white;
      text-align: center;
      padding: 40px 20px;
      border-radius: 0 0 15px 20px;
      margin: 0;
      width: 100%;
      margin-top: 30px;
      margin-bottom: 40px;
    }

    .hero-banner h2 {
      font-size: 24px;
      margin-bottom: 8px;
      text-align: center;
    }

    .hero-banner p {
      font-size: 16px;
      margin: 0 auto;
      max-width: 700px;
      text-align: center;
    }

    section.flex-section {
      display: flex;
      justify-content: center;
      align-items: stretch;
      flex-wrap: wrap;
      gap: 30px;
      margin-bottom: 40px;
      max-width: 1200px;
      margin-left: auto;
      margin-right: auto;
    }

    .half {
      flex: 1 1 450px;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
      animation: fadeInUp 0.8s ease-in-out;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .image-box {
      background-size: cover;
      background-position: center;
      height: 130%;
      min-height: 340px;
      max-height: 380px;
      border-radius: 10px;
    }

    .section-title {
      font-size: 22px;
      margin-bottom: 15px;
      color: #1D4C43;
    }

    .btn {
      display: inline-block;
      margin-top: 10px;
      padding: 10px 15px;
      background-color: rgb(255, 255, 255);
      border: 2px solid #1D4C43;
      color: #1D4C43;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      align-self: flex-start;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #1D4C43;
      color: white;
    }

    .flex-section .half p {
      text-align: justify;
    }

    .testimonials-section {
      margin: 60px auto;
      padding: 40px 20px;
      max-width: 1200px;
      border-radius: 25px;
      background: linear-gradient(to right, #1D4C43, #ffffff);
    }

    .testimonials-heading {
      text-align: center;
      font-size: 24px;
      color: #1D4C43;
      margin-bottom: 30px;
      font-weight: bold;
      letter-spacing: 1px;
    }

    .testimonial-container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 20px;
    }

    .testimonial-card {
      background-color: white;
      padding: 30px 20px 20px;
      border-radius: 8px;
      width: 300px;
      text-align: center;
      color: #1D4C43;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      position: relative;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .testimonial-card img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 50%;
      border: 5px solid white;
      position: absolute;
      top: -40px;
      left: 50%;
      transform: translateX(-50%);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .testimonial-card h3 {
      margin-top: 50px;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .testimonial-card .role {
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 15px;
    }

    .testimonial-card .quote {
      font-style: italic;
      font-size: 15px;
      line-height: 1.4;
    }

    .testimonial-card:hover {
      transform: scale(1.05);
      z-index: 2;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .testimonial-card.active {
      transform: scale(1.1);
      z-index: 3;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
    }

    .testimonial-dots {
      display: flex;
      justify-content: center;
      margin-top: 30px;
    }

    .dot {
      height: 10px;
      width: 10px;
      background-color: white;
      border: 2px solid #1D4C43;
      border-radius: 50%;
      margin: 0 5px;
    }

    .dot.active-dot {
      background-color: #1D4C43;
    }

    @keyframes fadeInUp {
      from {opacity: 0; transform: translateY(30px);}
      to {opacity: 1; transform: translateY(0);}
    }

    @media (max-width: 768px) {
      .top-bar, nav {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      .nav-links {
        flex-direction: column;
        gap: 10px;
      }
      section.flex-section {
        flex-direction: column;
      }
      .half {
        aspect-ratio: unset;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="top-bar">
    <div class="top-bar-left">
      <img src="image/logo.png" alt="Logo" class="logo">
      <h1><span style="color: #1D4C43; font-weight: 600;">Neuro</span><span style="color: black; font-weight: 600;">Aid</span></h1>
    </div>
    <div class="contact">
      <span><img src="image/phone.png" alt="Phone"> <b>+1 (800) 777-NEUR (6387)</b></span>
      <small>Call us for any question</small>
    </div>
  </div>
  <nav>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="about_us.php">About Us</a>
      <a href="services.php">Services</a>
      <a href="blogs.php">Blogs</a>
      <a href="team.php">Team</a>
    </div>
    <div class="nav-login">
      <a href="login.php" class="btn-login">Login</a>
    </div>
  </nav>
</header>

<div class="container">
  <section class="hero-banner">
    <h2>Health Is The First Step To Prosperity.</h2>
    <p>NeuroAid is built to provide smart, gentle support through every step of healing.</p>
  </section>

  <section class="flex-section">
    <div class="half image-box" style="background-image: url('image/about_us1.png');"></div>
    <div class="half" style="background-color: #D4EAE1;">
      <h3 class="section-title">What We Do</h3>
      <p>NeuroAid is a smart care system for stroke recovery. It offers daily reminders, calming music, and guided exercises to support healing. With just a few taps, patients gain confidence, and families feel at ease.</p>
      <a href="about_us_know_more.php#about-us" class="btn">Know More →</a>
    </div>
  </section>

  <section class="flex-section">
    <div class="half" style="background-color: #1D4C43; color: white;">
      <h3 class="section-title" style="color: white;">Why We Built NeuroAid</h3>
      <p>We believe stroke recovery should feel empowering, not overwhelming. NeuroAid was created to help patients reclaim confidence daily with the right tools, empathy, and support.</p>
      <a href="about_us_know_more.php#our-story" class="btn">Know More →</a>

    </div>
    <div class="half image-box" style="background-image: url('image/about_us2.png');"></div>
  </section>

  <section class="flex-section">
    <div class="half image-box" style="background-image: url('image/about_us3.png');"></div>
    <div class="half" style="background-color: #D4EAE1;">
      <h3 class="section-title">Why Empowerment Matters</h3>
      <p>Recovery isn’t just physical—it’s also about feeling strong and hopeful. With the right support, stroke survivors can regain independence and rebuild their lives with more peace of mind, for themselves and their loved ones.</p>
      <a href="about_us_know_more.php#empowerment" class="btn">Know More →</a>
    </div>
  </section>
</div>

<h2 class="testimonials-heading">TESTIMONIALS</h2>
<section class="testimonials-section"> 
  <div class="testimonial-container">
    
    
    <div class="testimonial-card">
      <img src="image/josie.png" alt="Josie Lane">
      <h3>Josie Lane</h3>
      <p class="role">Daughter & Caregiver</p>
      <p class="quote">NeuroAid made caring for my father so much easier. He feels more independent, and I feel more at peace knowing he’s guided every day.</p>
    </div>

    <div class="testimonial-card active">
      <img src="image/serena.png" alt="Serena Williams">
      <h3>Serena Williams</h3>
      <p class="role">Stroke Survivor (Age 68)</p>
      <p class="quote">The calming music and reminders really help me stay on track. I feel like I’m slowly getting better—and I’m not alone.</p>
    </div>

    <div class="testimonial-card">
      <img src="image/david.png" alt="David Lewis">
      <h3>David Lewis</h3>
      <p class="role">Son of Patient</p>
      <p class="quote">What I love most is how it encourages my mom with quotes and routines. It brings hope into her day, every day.</p>
    </div>
  </div>

  <div class="testimonial-dots">
    <span class="dot"></span>
    <span class="dot active-dot"></span>
    <span class="dot"></span>
  </div>
</section>


</body>
</html>
