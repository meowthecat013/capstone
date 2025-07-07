<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <title>Healing with AI - Blog 1</title>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      font-weight: 400;
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

    .blog-container {
      max-width: 1100px;
      margin: 40px auto;
      padding: 40px;
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
      border: 1px solid #1D4C43;
    }

    .blog-container h2 {
      color: #1D4C43;
      font-size: 28px;
      font-weight: 800;
      text-align: center;
      margin-bottom: 20px;
    }

    .blog-container img {
      width: 100%;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .blog-container h3 {
      color: #1D4C43;
      font-size: 22px;
      margin-bottom: 10px;
    }

    .blog-container p {
      font-size: 18px;
      color: #333;
      line-height: 1.8;
      text-align: justify;
      margin-bottom: 20px;
    }

  .related-posts-vertical {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
}

.related-item {
  display: flex;
  gap: 20px;
  align-items: center;
  background-color: #f9f9f9;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ddd;
}

.related-item img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
}

.related-text h4 {
  font-size: 16px;
  margin: 0;
  color: #1D4C43;
}
.related-text a {
  text-decoration: none;
}

.back-button {
  margin-top: 40px;
  text-align: center;
}

.back-button a {
  background-color: #1D4C43;
  color: white;
  text-decoration: none;
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 600;
}

.scroll-top {
  position: fixed;
  bottom: 30px;
  left: 80px;
  background-color: #1D4C43;
  color: white;
  padding: 10px 15px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 20px;
  display: none;
  z-index: 1000;
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

<section class="blog-container">
  <h2>Safe, Smart, and Free: Why NeuroAid Is Changing Stroke Care for the Better</h2>
  <p style="text-align:center; font-size: 19px; color:#444; margin-top: -10px;">
    Focusing on trust, security, and the human-centered mission behind the platform
  </p>
  <img src="image/blog3.png" alt="AI stroke recovery">
  
  <h3>Stroke Care That Puts People First</h3>
  <p>
    As stroke recovery continues to evolve, the need for secure, accessible, and empathetic solutions becomes more urgent. NeuroAid is 
    redefining how we approach post-stroke care—not just through smart technology, but through a deeply human-centered mission.
  </p>

  <h3>Designed with Patients in Mind</h3>
  <p>
   From its user-friendly interface to its privacy-first approach, NeuroAid is built to serve the real needs of stroke survivors. 
The goal: make smart recovery accessible to all—without sacrificing safety or dignity.
  </p>

  <h3>How NeuroAid Makes a Difference</h3>
  <p><strong>1. No-Cost Accessibility</strong><br>
    NeuroAid is free to use, removing financial barriers that often prevent patients from accessing critical rehabilitation support.
  </p>

  <p><strong>2. Data Privacy & Security</strong><br>
     User data is encrypted and never shared without consent. Patients can trust that their personal health information is safe and handled with care.
  </p>

  <p><strong>3. Inclusive Design</strong><br>
     Every feature—from large font sizes to voice-activated tools—is crafted for ease of use, especially for elderly or tech-shy users.
  </p>

  <p><strong>4. 24/7 Support Network</strong><br>
    Through built-in caregiver alerts and support integrations, NeuroAid ensures that patients are never alone—even in emergencies.
  </p>

  <h3>A Trustworthy Companion</h3>
  <p>Healing is deeply personal. NeuroAid recognizes this by combining cutting-edge AI with emotional intelligence, offering not just 
    recovery, but reassurance and respect.</p>

  <h3>Final Thoughts</h3>
  <p>NeuroAid stands at the intersection of innovation and empathy. By keeping stroke care free, secure, and focused on real human 
    needs, it’s not just changing lives—it’s setting a new standard for recovery. In a world of complex healthcare systems, NeuroAid 
    offers something simple, powerful, and truly transformative.]</p>

 <h3>POPULAR POST:</h3>

<div class="related-posts-vertical">

  <div class="related-item">
    <img src="image/blog1.png" alt="Blog 1">
    <div class="related-text">
      <a href="blog1.php"><p>From Reminder to Recovery: Tools That Make Daily Stroke Care Easier</p></a>
    </div>
  </div>

  <div class="related-item">
    <img src="image/blog2.png" alt="Blog 2">
    <div class="related-text">
      <a href="blog2.php"><p>Healing with AI: How NeuroAid Supports Stroke Recovery at Home</p></a>
    </div>
  </div>

</div>



<div class="back-button">
  <a href="blogs.php">&larr; Back to Blogs</a>
</div>

<div class="scroll-top" id="scrollTopBtn">↑</div>




</section>

<script>
  const scrollBtn = document.getElementById("scrollTopBtn");
  window.onscroll = () => {
    scrollBtn.style.display = (document.documentElement.scrollTop > 200) ? "block" : "none";
  };
  scrollBtn.onclick = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };
</script>


</body>
</html>
