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
  <h2>HEALING WITH AI: HOW NEUROAID SUPPORTS STROKE RECOVERY AT HOME</h2>
  <p style="text-align:center; font-size: 19px; color:#444; margin-top: -10px;">
    A look into how AI helps elderly stroke patients regain independence.
  </p>
  <img src="image/blog1.png" alt="AI stroke recovery">
  
  <h3>A look into how AI helps elderly stroke patients regain independence.</h3>
  <p>
    In the face of rising stroke cases among the elderly, the challenge of recovery has shifted from hospital settings to the comfort of one's home. 
    NeuroAid, a forward-thinking solution, blends artificial intelligence with personalized care to support post-stroke patients in reclaiming their independence—safely and effectively.
  </p>

  <h3>The Role of AI in Stroke Recovery</h3>
  <p>
    Artificial Intelligence has transformed many aspects of modern medicine, and stroke rehabilitation is no exception. NeuroAid 
    utilizes cutting-edge AI technology to monitor, guide, and assist patients during their recovery process—right from their living rooms. 
    By analyzing patient behavior, movement patterns, and daily routines, NeuroAid delivers customized support tailored to each individual's needs.
  </p>

  <h3>How NeuroAid Makes a Difference</h3>
  <p><strong>1. Personalized Exercise Reminders</strong><br>
    NeuroAid sends gentle reminders for cognitive and physical exercises crucial 
    for brain recovery and mobility restoration. These are based on each patient’s progress, helping avoid both under- and over-exertion.
  </p>

  <p><strong>2. Voice-Activated Assistance</strong><br>
    Elderly patients can interact with the system through simple voice commands. Whether
    it's to schedule therapy, request help, or access emergency services, the AI system ensures convenience and safety.
  </p>

  <p><strong>3. Real-Time Monitoring & Alerts</strong><br>
    Equipped with AI-powered sensors and visual analysis tools, NeuroAid can detect irregular 
    activities or risks—like sudden inactivity, potential falls, or cognitive lapses—and notify caregivers or medical professionals immediately.
  </p>

  <p><strong>4. Cognitive Health Support</strong><br>
    AI-driven memory games, personalized cognitive tasks, and emotional engagement exercises are all part of NeuroAid’s intelligent interface, helping
     prevent mental decline during stroke recovery.
  </p>

  <h3>Why At-Home Recovery Matters</h3>
  <p>Home-based stroke recovery offers psychological comfort and a sense of normalcy, both of which are crucial for healing. With NeuroAid, patients are no longer 
    isolated in their efforts; AI becomes a digital companion, aiding in every step of the journey to rehabilitation.</p>

  <h3>Final Thoughts</h3>
  <p>NeuroAid is not just a product—it's a movement toward smarter, more compassionate elderly care. By leveraging the power of AI, it brings hope and autonomy back 
    to stroke survivors and their families. As the population continues to age, innovations like NeuroAid are leading the way in making independent living a safe and achievable reality for all.</p>

 <h3>POPULAR POST:</h3>

<div class="related-posts-vertical">

  <div class="related-item">
    <img src="image/blog2.png" alt="Blog 2">
    <div class="related-text">
      <a href="blog2.php"><p>From Reminder to Recovery: Tools That Make Daily Stroke Care Easier</p></a>
    </div>
  </div>

  <div class="related-item">
    <img src="image/blog3.png" alt="Blog 3">
    <div class="related-text">
      <a href="blog3.php"><p>Safe, Smart, and Free: Why NeuroAid Is Changing Stroke Care for the Better</p></a>
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
