<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <title>NeuroAid - Blogs</title>
  <style>
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
      font-size: 32px;
      margin: 0;
      font-weight: 800;
    }

    .top-bar-left h1 span:first-child {
      color: #1D4C43;
      font-weight: 600;
    }

    .top-bar-left h1 span:last-child {
      color: black;
      font-weight: 600;
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

    section.blogs {
      padding: 40px 20px;
      background-color: #f6f9f9;
    }

    .blogs h2 {
      text-align: center;
      color: #1D4C43;
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 30px;
    }

    .blogs-container {
      display: flex;
      justify-content: center;
      gap: 70px;
      flex-wrap: wrap;
      max-width: 1300px;
      margin: 0 auto;
    }

    .blog-card {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      border: 1px solid #1D4C43;
      padding: 20px;
      width: 300px;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .blog-card h3 {
      color: #1D4C43;
      font-size: 16px;
      margin-bottom: 15px;
    }

    .blog-card img {
      width: 100%;
      border-radius: 8px;
      height: 170px;
      object-fit: cover;
      margin-bottom: 15px;
    }

    .blog-card p {
      font-size: 14px;
      color: #333;
      text-align: justify;
      line-height: 1.5;
      margin-bottom: 15px;
    }

    .read-more {
      background-color: white;
      border: 2px solid #1D4C43;
      color: #1D4C43;
      padding: 8px 18px;
      font-size: 14px;
      border-radius: 6px;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
    }

    .read-more:hover {
      background-color: #1D4C43;
      color: white;
    }

    @media (max-width: 600px) {
      .nav-links {
        flex-direction: column;
        gap: 10px;
        margin-left: 0;
      }
      nav {
        flex-direction: column;
        align-items: flex-start;
      }
    }
  </style>
</head>
<body>

<header>
  <div class="top-bar">
    <div class="top-bar-left">
      <img src="image/logo.png" alt="Logo" class="logo">
      <h1><span>Neuro</span><span>Aid</span></h1>
    </div>
    <div class="contact">
      <span style="display: flex; align-items: center;">
        <img src="image/phone.png" alt="Phone" style="height: 16px; margin-right: 6px;"><b>+1 (800) 777-NEUR (6387)</b>
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

<section class="blogs">
  <h2>BLOGS</h2>
  <div class="blogs-container">
    <div class="blog-card">
      <h3>Healing with AI: How NeuroAid Supports Stroke Recovery at Home</h3>
      <img src="image/blog1.png" alt="Blog 1">
      <p>A look into how AI helps elderly stroke patients regain independence.</p>
      <a href="blog1.php" class="read-more">Read More →</a>
    </div>
    <div class="blog-card">
      <h3>From Reminder to Recovery: Tools That Make Daily Stroke Care Easier</h3>
      <img src="image/blog2.png" alt="Blog 2">
      <p>Exploring NeuroAid’s features like medication tracking, cognitive games, and journaling.</p>
      <a href="blog2.php" class="read-more">Read More →</a>
    </div>
    <div class="blog-card">
      <h3>Safe, Smart, and Free: Why NeuroAid Is Changing Stroke Care for the Better</h3>
      <img src="image/blog3.png" alt="Blog 3">
      <p>Focusing on trust, security, and the human-centered mission behind the platform.</p>
      <a href="blog3.php" class="read-more">Read More →</a>
    </div>
  </div>
</section>

</body>
</html>
