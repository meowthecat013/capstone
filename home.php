<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NeuroAid - Stroke Info</title>
  <style>
    body {
      background: url('bg.png') no-repeat center center/cover;
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
    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 5px 30px 20px 30px;
      border-top: 1px solid #eee;
    }
    .nav-links {
      display: flex;
      gap: 80px;
      margin-left: 100px;
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
    .hero {
      position: relative;
      min-height: 500px;
      padding: 30px;
      display: flex;
      justify-content: center;
      align-items: flex-start;
    }
    .content {
      position: relative;
      z-index: 2;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      width: 100%;
      max-width: 1200px;
      margin-top: 10px;
      padding: 0 50px;
    }
    .left-box {
      display: flex;
      flex-direction: column;
      width: 350px;
    }
    .right-box {
      display: flex;
      flex-direction: column;
      width: 350px;
      margin-left: 100px;
    }
    .box-title {
      color: #1D4C43;
      margin-bottom: 10px;
      font-size: 32px;
    }
    .box1, .box2 {
      background-color: rgba(232, 232, 232, 0.25);
      padding: 15px;
      border-radius: 10px;
      text-align: justify;
      width: 100%;
      height: 250px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
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
    }
    .btn:hover {
      background-color: #1D4C43;
      color: white;
    }
    .nav-login {
      display: flex;
      margin-right: 60px;
    }

    /* HOME PART 2 SECTION STYLES */
    .header-dark {
      background: #222;
      color: #fff;
      font-size: 2rem;
      font-weight: 800;
      padding: 18px 0 14px 32px;
      letter-spacing: 0.5px;
      margin-top: 60px;
    }
    .hero2 {
      min-height: 500px;
      padding: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      position: relative;
      width: 100%;
      /* Ensures background behaves like .hero */
    }
    .feature-banner {
      position: relative;
      margin-top: 38px;
      background: #256455;
      color: #fff;
      padding: 22px 48px 22px 80px;
      border-radius: 32px;
      font-size: 1.7rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      box-shadow: 0 8px 32px rgba(0,0,0,0.10);
      z-index: 2;
      min-width: 480px;
      max-width: 90vw;
    }
    .feature-banner img {
      width: 38px;
      height: 38px;
      margin-right: 22px;
      margin-left: -48px;
    }
    .feature-banner-text {
      display: flex;
      flex-direction: column;
    }
    .feature-banner-text span {
      font-size: 1.1rem;
      font-weight: 400;
      color: #e8e8e8;
      margin-top: 4px;
    }
    .videos-row {
      margin-top: 110px;
      width: 90vw;
      max-width: 960px;
      display: flex;
      justify-content: center;
      gap: 36px;
      z-index: 1;
      position: relative;
    }
    .video-card {
      background: rgba(255,255,255,0.12);
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 2px 16px rgba(0,0,0,0.10);
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 410px;
      min-width: 300px;
      max-width: 100%;
      position: relative;
      border: 4px solid rgba(37,100,85,0.18);
    }
    .video-card iframe {
      width: 100%;
      height: 235px;
      border-radius: 14px 14px 0 0;
      border: none;
      display: block;
      background: #000;
    }
    .video-link {
      position: absolute;
      bottom: 10px;
      left: 0;
      width: 100%;
      text-align: center;
      background: rgba(32,32,32,0.32);
      color: #fff;
      font-size: 1rem;
      text-shadow: 0 1px 4px #000;
      padding: 6px 0;
      border-radius: 0 0 14px 14px;
      text-decoration: none;
      transition: background 0.2s;
      font-family: monospace;
    }
    .video-link:hover {
      background: #1D4C43;
      color: #fff;
    }
    @media (max-width: 900px) {
      .videos-row {
        flex-direction: column;
        gap: 30px;
        width: 98vw;
        margin: 0 auto;
      }
      .feature-banner {
        min-width: unset;
        padding: 16px 10vw 16px 72px;
        font-size: 1.1rem;
      }
      .video-card {
        width: 98vw;
        min-width: unset;
      }
    }
    @media (max-width: 600px) {
      .feature-banner {
        font-size: 1rem;
        padding: 12px 5vw 12px 52px;
      }
      .video-card iframe {
        height: 180px;
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
      <span style="display: flex; align-items: center;"><img src="image/phone-icon.png" alt="Phone" style="height: 16px; margin-right: 6px;">+1 (800) 777-NEUR (6387)</span><br>
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

<section class="hero">
  <div class="overlay"></div>
  <div class="content">
    <div class="left-box">
      <h2 class="box-title">Health Matters</h2>
      <div class="box1">
        <p><strong>Do you know the types of stroke?</strong><br>
          A stroke can happen in three main ways: an <em style="color: #1D4C43; font-weight: bold;">ischemic stroke</em>, when a blood vessel in the brain is blocked; a <em style="color: #1D4C43; font-weight: bold;">hemorrhagic stroke</em>, when a blood vessel bursts and causes bleeding in the brain; or a <em style="color: #1D4C43; font-weight: bold;">transient ischemic attack (TIA)</em>, often called a mini-stroke, which is a temporary blockage that resolves on its own but may signal a warning for future strokes.
        </p>
        <a href="stroke1.php" class="btn">Know More →</a>
      </div>
    </div>
    <div class="right-box">
      <h2 class="box-title">What happens during a stroke?</h2>
      <div class="box2">
        <p>A stroke occurs when blood flow to a part of the brain is interrupted or reduced, depriving brain tissue of oxygen and nutrients. Strokes often cause sudden weakness, confusion, difficulty speaking, or loss of coordination, and they require emergency medical care to minimize damage.</p>
      </div>
    </div>
  </div>
</section>

<!-- HOME PART 2 SECTION (SCROLL DOWN TO SEE) -->
<section class="hero2">
  <div class="feature-banner">
    <img src="https://img.icons8.com/ios-filled/50/ffffff/megaphone.png" alt="Megaphone">
    <div class="feature-banner-text">
      Experience our features
      <span>Watch short videos about NeuroAids</span>
    </div>
  </div>
  <div class="videos-row">
  <div class="video-card">
    <iframe src="https://www.youtube.com/embed/jWREPuLBDHM?si=2hBypxlPC3E-gsCp" allowfullscreen></iframe>
    <a class="video-link" href="https://youtu.be/jWREPuLBDHM?si=2hBypxlPC3E-gsCp" target="_blank">
      https://youtu.be/jWREPuLBDHM?si=2hBypxlPC3E-gsCp
    </a>
  </div>
  <div class="video-card">
  <iframe src="https://www.youtube.com/embed/eFcCzbEBckE?si=i4IJ098-hI-0VorG" allowfullscreen></iframe>
    <a class="video-link" href="https://youtu.be/eFcCzbEBckE?si=i4IJ098-hI-0VorG" target="_blank">
      https://youtu.be/eFcCzbEBckE?si=i4IJ098-hI-0VorG
    </a>
  </div>
</div>
</section>

</body>
</html>