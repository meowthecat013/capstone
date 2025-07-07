<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <title>NeuroAid - Services</title>
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

    section.services {
      padding: 40px 20px;
      background-color: #f6f9f9;
    }

    .services h2 {
      text-align: center;
      color: #1D4C43;
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 30px;
    }

    .services-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-start;
      max-width: 1300px;
      margin: 0 auto;
      gap: 10px;
    }

    .service-box {
      width: calc(30% - 14px); 
      background-color: white;
      text-align: center;
      padding:25px 20px;
      box-sizing: border-box;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      margin-left: 37px;
      margin-bottom: 25px;
      
    }

    .service-box.hidden {
      display: none;
    }

    .service-box img {
      height: 50px;
      margin-bottom: 10px;
    }

    .service-box h3 {
      color: #1D4C43;
      font-size: 16px;
      margin-bottom: 10px;
    }

    .service-box p {
      font-size: 14px;
      color: #333;
    }

    .show-more-container {
      display: flex;
      justify-content: flex-end;
      max-width: 1200px;
      margin: 40px auto 0;
      padding-right: 20px;
    }

    .show-more-btn {
      background-color: white;
      border: 2px solid #1D4C43;
      color: #1D4C43;
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s, color 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .show-more-btn:hover {
      background-color: #1D4C43;
      color: white;
    }

    .arrow-icon {
      width: 18px;
      height: 18px;
      fill: currentColor;
    }


   .why-use-neuroaid {
  background-color: #ffffff;
  padding: 60px 20px;
  text-align: center;
}

.why-use-neuroaid h2 {
  color: #1D4C43;
  font-size: 28px;
  font-weight: 800;
  margin-bottom: 30px;
}

.why-cards-row {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 80px; 
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 25px; 
  box-sizing: border-box;
}

.why-card {
  background-color: #e8ecf6;
  border-radius: 14px;
  width: 320px;
  height: 190px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 50px; 
  padding-left: 18px;
  padding-right: 18px;
  padding-bottom: 20px;
  box-sizing: border-box;
  position: relative; 
  text-align: center;
}

.why-card h3 {
  font-family: 'Inter', Arial, sans-serif;
  position: absolute;
  top: 10px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #1D4C43;
  color: white;
  padding: 14px 30px;     
  border-radius: 12px;
  font-size: 17px;         
  font-weight: 400;       
  margin: 0;
  white-space: nowrap;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
  letter-spacing: 0.2px;
}





.why-card p {
  font-size: 15px;
  color: #333;
  line-height: 1.4;
  margin: auto 0;
}


.trust-list {
  list-style: none;
  padding: 0;
  text-align: left;
  max-width: 900px;
  margin: 40px auto 0;
  font-size: 17px;
  color: #1D4C43;
  line-height: 2;
  text-align: justify;
}

.trust-list li {
  display: flex;
  align-items: flex-start;
  margin-bottom: 18px;
  width: 1000px;
}

.trust-list .icon {
  width: 26px;
  height: 26px;
  margin-right: 12px;
  margin-top: 2px;
}


    @media (max-width: 1024px) {
      .service-box {
        width: calc(50% - 10px); 
      }
    }

    @media (max-width: 600px) {
      .service-box {
        width: 100%;
      }

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
      <h1><span style="color: #1D4C43; font-weight: 600;">Neuro</span><span style="color: black; font-weight: 600;">Aid</span></h1>
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

<section class="services">
  <h2>OUR SERVICES</h2>
  <div class="services-container">

    <div class="service-box">
      <img src="image/cognitive.png" alt="Activity Icon">
      <h3>Cognitive Exercises Using AI</h3>
      <p>Delivers customized brain-training tasks to maintain cognitive health, improve memory, and enhance mental focus tailored to the patient's condition and progress.</p>
    </div>

    <div class="service-box">
      <img src="image/qoutes.png" alt="Robot Icon">
      <h3>AI-Generated Inspirational Quotes</h3>
      <p>Displays motivational quotes generated by AI every time the page is refreshed to uplift patients and encourage a positive mindset during recovery.</p>
    </div>

    <div class="service-box">
      <img src="image/journal.png" alt="Speech Icon">
      <h3>Personal Health Journal</h3>
      <p>Offers a digital space for users to log daily activities, experiences, and health notes, which can assist in personal reflection and caregiver communication.</p>
    </div>

    <div class="service-box">
      <img src="image/music.png" alt="Video Icon">
      <h3>Calm Music Therapy for Stroke Patients</h3>
      <p>Plays soothing, AI-selected music upon login to reduce stress and anxiety, helping stroke patients achieve emotional balance and relaxation.</p>
    </div>

    <div class="service-box">
      <img src="image/games.png" alt="Shield Icon">
      <h3>Interactive Games for Cognitive Recovery</h3>
      <p>Includes AI-based games like bubble popping, word typing, color matching, and speech training tools (e.g., Pic to Voice, Voice to Word) to strengthen cognitive and verbal functions.</p>
    </div>

    <div class="service-box">
      <img src="image/reminder.png" alt="Puzzle Icon">
      <h3>Reminder System for Daily Activities</h3>
      <p>Lets users set alarms and reminders for important tasks like taking medication, attending therapy, or performing daily exercises.</p>
    </div>

    <div class="service-box hidden">
      <img src="image/physical.png" alt="Speech Icon">
      <h3>AI-Powered Physical Activity Insights</h3>
      <p>Uses AI to analyze user-inputted physical activity data (e.g., steps, exercises) and provides personalized suggestions to support stroke recovery and promote a healthy lifestyle.</p>
    </div>

    <div class="service-box hidden">
      <img src="image/recreational.png" alt="Video Icon">
      <h3>AI-Based Recreational Activity Suggestions</h3>
      <p> Recommends physical and relaxing activities (like stretching, yoga, walking, etc.) based on health data, mood, and condition to enhance mental and physical well-being.</p>
    </div>

    <div class="service-box hidden">
      <img src="image/speech.png" alt="Shield Icon">
      <h3>Speech Therapy Assistance</h3>
      <p> Features tools that support speech rehabilitation through voice interaction, pronunciation feedback, and engaging speech-related games.</p>
    </div>

    <div class="service-box hidden">
      <img src="image/video.png" alt="Activity Icon">
      <h3>Motivational Video and Exercise Recommendations</h3>
      <p>Suggests YouTube-based therapeutic videos tailored to the user's mood, health metrics, and stroke type, helping promote continuous learning and recovery.</p>
    </div>

    <div class="service-box hidden">
      <img src="image/secure.png" alt="Robot Icon">
      <h3>Secure User Authentication and Data Privacy</h3>
      <p> Implements encrypted storage and OTP-based login verification to ensure personal health data is safe and accessible only to authorized users.</p>
    </div>
  </div>

  <div class="show-more-container">
    <button class="show-more-btn" onclick="toggleMoreServices()" id="toggle-btn">
      Show More
      <svg class="arrow-icon" viewBox="0 0 20 20">
        <path d="M10 15l-6-6h12l-6 6z"/>
      </svg>
    </button>
  </div>

  
</section>


<script>
  function toggleMoreServices() {
    const hiddenServices = document.querySelectorAll('.service-box.hidden');
    const toggleBtn = document.getElementById("toggle-btn");
    
    if (hiddenServices[0].style.display === "none" || hiddenServices[0].style.display === "") {
     
      hiddenServices.forEach(service => {
        service.style.display = "block";
      });
      toggleBtn.innerHTML = `Show Less
        <svg class="arrow-icon" viewBox="0 0 20 20">
          <path d="M10 5l6 6H4l6-6z"/>
        </svg>`;
    } else {
      
      hiddenServices.forEach(service => {
        service.style.display = "none";
      });
      toggleBtn.innerHTML = `Show More
        <svg class="arrow-icon" viewBox="0 0 20 20">
          <path d="M10 15l-6-6h12l-6 6z"/>
        </svg>`;
    }
  }
</script>

<section class="why-use-neuroaid">
  <h2>Why Use NeuroAid?</h2>
  <div class="why-cards-row">
    <div class="why-card">
      <h3>Personalized Stroke Recovery</h3>
      <p>Our AI customizes exercises, reminders, and support based on your unique health needs.</p>
    </div>
    <div class="why-card">
      <h3>Free, Compassionate Care</h3>
      <p>NeuroAid is 100% free—because everyone deserves access to quality recovery tools.</p>
    </div>
    <div class="why-card">
      <h3>All-in-One Daily Support</h3>
      <p>From medication tracking to calming music and cognitive games, everything you need is in one simple platform.</p>
    </div>
  </div>

  <h2 style="margin-top: 60px;">Why You Can Trust NeuroAid:</h2>
  <ul class="trust-list">
    <li><img src="image/check.png" alt="icon" class="icon"> Privacy & Security First: Your data is protected with encrypted storage and strict confidentiality measures.</li>
    <li><img src="image/check.png" alt="icon" class="icon"> Human-Centered & Free: We offer our full support at no cost—because everyone deserves accessible care.</li>
    <li><img src="image/check.png" alt="icon" class="icon"> Built by a Passionate Team: Created by students and guided by real-world healthcare needs, NeuroAid is developed with heart and purpose.</li>
    <li><img src="image/check.png" alt="icon" class="icon"> Smart, Personalized Support: AI analyzes your input to provide meaningful insights tailored to your recovery needs.</li>
  </ul>
</section>





</body>
</html>