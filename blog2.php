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
  <h2>From Reminder to Recovery: Tools That Make Daily Stroke Care Easier</h2>
  <p style="text-align:center; font-size: 19px; color:#444; margin-top: -10px;">
    Exploring NeuroAid’s features like medication tracking, cognitive games, and journaling.
  </p>
  <img src="image/blog2.png" alt="AI stroke recovery">
  
  <h3>Helping Stroke Survivors Navigate the Day-to-Day</h3>
  <p>
    NeuroAid focuses on streamlining care with simple yet powerful features tailored to the unique needs of stroke survivors. 
These tools help ensure patients not only follow their recovery plan but do so with dignity and confidence.
  </p>

  <h3>Daily Tools for Real-Life Challenges</h3>
  <p>
    NeuroAid focuses on streamlining care with simple yet powerful features tailored to the unique needs of stroke survivors. 
These tools help ensure patients not only follow their recovery plan but do so with dignity and confidence.
  </p>

  <h3>How NeuroAid Makes a Difference</h3>
  <p><strong>1. Medication Tracking</strong><br>
   Forgetfulness can compromise recovery. NeuroAid offers daily medication reminders and logs, ensuring patients stay on schedule
    while keeping family or caregivers informed.
  </p>

  <p><strong>2. Interactive Journaling</strong><br>
    Patients can document symptoms, moods, and progress with easy voice-to-text journaling. This not only aids self-awareness but also 
    helps medical professionals personalize ongoing treatment.
  </p>

  <p><strong>3. Cognitive Games & Exercises</strong><br>
    NeuroAid includes a library of stroke-friendly games and activities that stimulate memory, language, and motor skills—offering 
    fun and therapeutic value.
  </p>

  <p><strong>4. Daily Health Summaries</strong><br>
     At the end of each day, users receive a brief health summary, outlining progress, areas of concern, and encouraging milestones.
  </p>

  <h3>Recovery Through Routine</h3>
  <p>Consistency plays a key role in stroke rehabilitation. NeuroAid transforms daily routines into structured recovery steps—making each action meaningful 
    and each improvement measurable.</p>

  <h3>Final Thoughts</h3>
  <p>Stroke care doesn’t have to be complicated. NeuroAid bridges the gap between patients and their recovery goals through intuitive, everyday tools. By turning 
    reminders into routines, it empowers survivors to take charge of their healing—one day at a time.</p>

 <h3>POPULAR POST:</h3>

<div class="related-posts-vertical">

  <div class="related-item">
    <img src="image/blog1.png" alt="Blog 1">
    <div class="related-text">
      <a href="blog1.php"><p>Healing with AI: How NeuroAid Supports Stroke Recovery at Home</p></a>
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
