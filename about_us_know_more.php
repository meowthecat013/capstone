<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us - NeuroAid</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #ffffff;
      color: #1D4C43;
    }

    header {
      background-color: white;
      border-bottom: 1px solid #ccc;
    }

    .page-title {
      text-align: center;
      margin-top: 60px;
      margin-bottom: -20px;
    }

    .page-title h2 {
      font-size: 36px;
      color: #1D4C43;
      font-weight: 900;
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
      text-align: right;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
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
      transition: 0.3s;
      padding: 6px 10px;
      border-radius: 5px;
    }

    nav a:hover {
      background-color: #1D4C43;
      color: white;
    }

    .nav-links {
      display: flex;
      gap: 80px;
      margin-left: 100px;
    }

    .nav-login {
      margin-right: 60px;
    }

    main.content-wrapper {
      display: flex;
      max-width: 1200px;
      margin: 40px auto;
      gap: 40px;
      min-height: 500px;
      align-items: center; 
    }

    .left-image {
      flex: 0 0 46%;
      margin-left: 20px;
      border: 6px solid #1D4C43;
      max-height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .left-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .about p {
      font-size: 16px;
      line-height: 1.6;
      text-align: justify;
      color: #1D4C43;
      padding: 0 40px;
    }

    .about h4 {
      font-size: 18px;
      margin: 20px 40px 5px;
      color: #1D4C43;
    }

    .right-content {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .right-content h2 {
      font-size: 28px;
      margin-bottom: 15px;
      color: #1D4C43;
    }

    .right-content p {
      font-size: 16px;
      line-height: 1.6;
      text-align: justify;
      color: #1D4C43;
    }

    .right-content h4 {
      margin-top: 20px;
      font-size: 18px;
      color: #1D4C43;
    }

    ul.custom-bullets {
      padding-left: 20px;
      list-style-type: square;
      color: #1D4C43;
      text-align: justify;
    }

    hr.section-line {
      border: none;
      border-top: 2px solid #ccc;
      margin: 60px auto;
      width: 85%;
    }

    @media (max-width: 768px) {
      .nav-links {
        flex-direction: column;
        gap: 10px;
      }

      nav, .top-bar {
        flex-direction: column;
        align-items: flex-start;
      }

      main.content-wrapper {
        flex-direction: column;
        padding: 0 20px;
      }

      .left-image {
        margin: 0;
        width: 100%;
      }
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
  </style>
</head>
<body>

<!-- HEADER -->
<header>
  <div class="top-bar">
    <div class="top-bar-left">
      <img src="image/logo.png" alt="Logo" class="logo">
      <h1><span style="color: #1D4C43; font-weight: 600;">Neuro</span><span style="color: black; font-weight: 600;">Aid</span></h1>
    </div>
    <div class="contact">
      <span style="display: flex; align-items: center;">
        <img src="image/phone-icon.png" alt="Phone" style="height: 16px; margin-right: 6px;">+1 (800) 777-NEUR (6387)
      </span>
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

<!-- CENTERED PAGE TITLE -->
<section class="page-title">
  <h2>About Us</h2>
</section>

<!-- SECTION 1 -->
<main class="content-wrapper" id="about-us">
  <div class="left-image">
    <img src="image/about_us1.png" alt="Elderly Care">
  </div>
  <section class="about">
    <p>NeuroAid is an AI-powered care system dedicated to supporting elderly stroke patients in their physical, mental, and emotional recovery. Our mission is to empower patients and ease the caregiver’s role through smart, personalized, and compassionate technology.</p>

    <h4>Personalized Stroke Care</h4>
    <p>We tailor physical and cognitive rehabilitation plans based on each patient’s needs, using AI to provide relevant and effective support.</p>

    <h4>Empowering Independence</h4>
    <p>Our tools—like medication reminders, health tracking, and journaling—are designed to help stroke survivors manage their daily routines confidently.</p>

    <h4>Support for Caregivers</h4>
    <p>We provide caregivers with real-time insights and tools that reduce stress and improve care quality at home.</p>
  </section>
</main>

<hr class="section-line">

<!-- SECTION 2 -->
<main class="content-wrapper" id="our-story">
  <div class="left-image">
    <img src="image/about_us2.png" alt="Our Story">
  </div>
  <div class="right-content">
    <h2>Our Story & Why We Built NeuroAid</h2>
    <p>Stroke survivors face daily challenges—remembering tasks, staying active, and keeping their minds engaged. Many elderly patients struggle with systems that are not personalized or require too much manual input.</p>
    
    <p><strong>NeuroAid</strong> was created to bridge this gap: using AI, we provide real-time, tailored support that adapts to each patient’s unique needs, helping them regain independence and confidence.</p>

    <h4>Impact & Significance</h4>
    <ul class="custom-bullets">
      <li><strong>For Patients:</strong> More independence, better recovery, and a higher quality of life.</li>
      <li><strong>For Caregivers:</strong> Less stress, more support, and timely alerts.</li>
      <li><strong>For Healthcare Professionals:</strong> Accurate, real-time data for better care decisions.</li>
      <li><strong>For the Community:</strong> Lower healthcare costs and improved well-being for everyone.</li>
    </ul>
  </div>
</main>

<hr class="section-line">

<!-- SECTION 3 -->
<main class="content-wrapper" id="empowerment">
  <div class="left-image">
    <img src="image/about_us3.png" alt="Empowerment">
  </div>
  <div class="right-content">
    <h2>Why Empowerment Matters</h2>
    <p>Every stroke survivor’s journey is unique. For many elderly patients, daily routines and mental engagement become challenging after a stroke. True empowerment means more than just recovery—it’s about restoring dignity, autonomy, and joy in everyday life.</p>

    <h4>Empowerment Through Connection</h4>
    <ul class="custom-bullets">
      <li><strong>For Patients:</strong> Independence is regained, not just through reminders, but through encouragement, insight, and the ability to set and achieve personal goals.</li>
      <li><strong>For Families:</strong> Peace of mind comes from real-time updates and knowing support is always available, even from afar.</li>
      <li><strong>For Healthcare Providers:</strong> Better data means better care—personalized, proactive, and timely.</li>
    </ul>
  </div>
</main>

<!-- TESTIMONIALS -->
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
