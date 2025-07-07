<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NeuroAid - Meet Our Team</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    * { box-sizing: border-box; }

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


    /* Team Section */
    .team-section {
      text-align: center;
      padding: 50px 20px;
    }

    .team-section h2 {
      color: #1D4C43;
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 30px;
    }

    .team-section p {
      max-width: 800px;
      margin: auto;
      color: #1D4C43;
      font-size: 18px;
    }

  .team-members {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 40px;
  margin-top: 40px;
}

.member {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px; /* space between image and info box */
}

.member:hover {
  transform: translateY(-6px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.member-img-box {
  width: 280px;
  height: 230px;
  background-color: #E3EDEB;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.member-img-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.member-info-box {
  width: 260px;
  background-color: #E3EDEB;
  border-radius: 16px;
  padding: 15px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.member-info-box hr {
  border: none;
  border-top: 1px solid #aaa;
  margin: 10px auto;
  width: 60%;
}

.member-info-box h3 {
  margin: 0 0 6px;
  color: #1D4C43;
  font-size: 20px;
  font-weight: 700;
}

.member-info-box p {
  font-size: 15px;
  color: #444;
  margin: 0;
}

.social-icons a {
  margin: 0 6px;
  color: #1D4C43;
  font-size: 1.3em;
  transition: color 0.3s ease;
}

.social-icons a:hover {
  color: #0b2520;
}

.info-cards {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 30px;
 padding: 10px 20px 5px;
  background: #1D4C43;
}

.info-card {
  display: flex;
  flex-direction: column;
  width: 340px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  position: relative;
  font-size: 18px;
}
.info-card1 {
  display: flex;
  flex-direction: column;
  width:550px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  position: relative;
  font-size: 18px;
}


.info-card:last-child {
  width: 400px;
}
.half-green {
  background: #E3EDEB;
  height: 60px;
  width: 100%;
  display: flex;
  align-items: flex-end;
  padding: 0px 20px 10px 20px;
  box-sizing: border-box;
}

.half-green h3 {
  margin: 0;
  color: #1D4C43;
  font-size: 26px;
}

.info-content {
  padding:5px 20px 20px 20px;
  color: #333;
}

.info-content hr {
  border: none;
  border-top: 1px solid white;
  width: 90%;
  margin: 4px auto 12px auto;
}

.info-content p {
  font-size: 17px;
  line-height: 1.6;
  margin: 0;
}
.contact-section {
  display: flex;
  flex-wrap: wrap;
  padding: 50px 20px;
  background: #1D4C43;
  color: white;
  gap: 300px;
  justify-content: center;
}

.contact-info, .contact-form {
  max-width: 360px;
  flex: 1;
}
.contact-info p, .contact-info a {
  margin: 10px 0;
  color: white;
  text-decoration: none;
  font-size: 1.5em;
}
.contact-info p1 {
  margin: 10px 0;
  color: white;
  text-decoration: none;
  font-size: 3em;
}
.contact-info p2 {
  margin: 10px 0;
  color: black;
  text-decoration: none;
  font-size: 3em;
}
.contact-info {
margin-left: 100px;

}
.contact-form label {
  display: block;
  margin-top: 10px;
}
.contact-form input, .contact-form textarea {
  width: 100%;
  padding: 8px;
  margin-top: 4px;
  border-radius: 4px;
  border: none;
}
.contact-form textarea {
  resize: vertical;
  min-height: 100px;
}
.contact-form button {
  margin-top: 10px;
  padding: 10px 20px;
  background: white;
  color: #1D4C43;
  border: none;
  border-radius: 4px;
  font-weight: bold;
  cursor: pointer;
}
footer {
  text-align: center;
  padding: 20px;
  background: #1D4C43;
  color: white;
}
footer a {
  color: white;
  text-decoration: underline;
}

.info-content p {
      max-width: 800px;
      margin: auto;
      color: #1D4C43;
      font-size: 18px;
    }
    .contact-form {
  max-width: 600px;
  margin: 0 auto;
}

.name-group {
  display: flex;
  gap: 20px;
}

.form-field {
  flex: 1;
  display: flex;
  flex-direction: column;
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
        margin-left: 0;
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

<section class="team-section">
  <h2>MEET OUR TEAM</h2>
  <p>We are a dedicated team of student developers passionate about using AI to support stroke recovery. Together, we built NeuroAid to make care smarter, more personal, and accessible for everyone.</p>

 <div class="team-members">
  <div class="member">
    <div class="member-img-box">
      <img src="image/sofia.png" alt="Sofia Adeline Alarcon">
    </div>
    <div class="member-info-box">
      <h3>Sofia Adeline Alarcon</h3>
      <p>Partner 1</p>
      <hr>
      <div class="social-icons">
    <a href="https://www.instagram.com/sofiaa_alrcn?igsh=cXE4aWJob216N3ln" target="_blank"><i class="fab fa-instagram"></i></a>
    <a href="https://facebook.com/piang.kwon" target="_blank"><i class="fab fa-facebook"></i></a>
    <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
  </div>

  <div class="member">
    <div class="member-img-box">
      <img src="image/rosa.png" alt="Rosa Lee Miravalles">
    </div>
    <div class="member-info-box">
      <h3>Rosa Lee Miravalles</h3>
      <p>Partner 2</p>
      <hr>
      <div class="social-icons">
     <a href="https://www.instagram.com/rozaleii03?igsh=cWthZHN0dHBvNDJz" target="_blank"><i class="fab fa-instagram"></i></a>
    <a href="https://facebook.com/BAMBIEMILLES?_rdc=1&_rdr#" target="_blank"><i class="fab fa-facebook"></i></a>
    <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>


      </div>
    </div>
  </div>

  <div class="member">
    <div class="member-img-box">
      <img src="image/anjanet.png" alt="Anjanet Toñacao">
    </div>
    <div class="member-info-box">
      <h3>Anjanet Toñacao</h3>
      <p>Partner 3</p>
      <hr>
      <div class="social-icons">
       <a href="https://www.instagram.com/anj_srfn?igsh=bGNpYWNpbW0wd3Bx" target="_blank"><i class="fab fa-instagram"></i></a>
    <a href="https://facebook.com/anjanetsrfn/" target="_blank"><i class="fab fa-facebook"></i></a>
    <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
  </div>
</div>
</section>

<!-- Mission, Vision, Core Values Section -->
<section class="info-cards">
  <div class="info-card">
    <div class="half-green">
      <h3>Mission</h3>
    </div>
    <div class="info-content">
      <hr>
      <p style="text-align: justify; text-align-last: center;"> To empower individuals and communities through innovative solutions in neurological care, education, and research, fostering a better understanding and management of neurological health challenges.</p>
    </div>
  </div>
  <div class="info-card">
    <div class="half-green">
      <h3>Vision</h3>
    </div>
    <div class="info-content">
      <hr>
     <p style="text-align: justify; text-align-last: center;"> To be a global leader in advancing neurological health and 
      well-being, ensuring access to compassionate care, groundbreaking research, and transformative education for all.</p>
    </div>
  </div>
  <div class="info-card1">
    <div class="half-green">
      <h3>Core Values</h3>
    </div>
    <div class="info-content">
      <hr>
      <p>
        <strong>Compassion:</strong> We prioritize empathetic care and support, placing the well-being of individuals at the heart of everything we do.<br/>
        <strong>Innovation:</strong> We embrace cutting-edge technology and research to pioneer solutions that redefine the future of neurological health.<br/>
        <strong>Integrity:</strong> We uphold the highest ethical standards, ensuring trust, transparency, and accountability in all our actions.<br/>
        <strong>Collaboration:</strong> We foster partnerships with healthcare providers, researchers, and communities to achieve holistic and impactful solutions.<br/>
        <strong>Inclusivity:</strong> We commit to accessible and equitable care and services, addressing the diverse needs of individuals across the globe.<br/>
        <strong>Excellence:</strong> We strive for continues improvement and excellence in caare delivery, research, and education.
      </p>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
  <div class="contact-info">
    <p1>CONTACT</p1> <p2>US</p2>
    <p>Feel free to use the form or drop us an email. Old-fashioned phone calls work too.</p>
    <p><i class="fas fa-phone"></i> +63 912 345 6789</p>
    <p><i class="fas fa-envelope"></i> neurosupport@gmail.com</p>
    <p><i class="fas fa-map-marker-alt"></i> 123 Neuro Street, Pasig City, Philippines</p>
  </div>
  <div class="contact-form">
  <form>
    <div class="name-group">
      <div class="form-field">
        <label>First name *</label>
        <input type="text" required>
      </div>
      <div class="form-field">
        <label>Last name *</label>
        <input type="text" required>
      </div>
    </div>

    <label>Email (Privacy Policy) *</label>
    <input type="email" placeholder="example@gmail.com" required>

    <label>Message *</label>
    <textarea placeholder="Type your message here..." required></textarea>

    <button type="submit">Submit</button>
  </form>
</div>

</section>

<!-- Footer -->
<footer>
  <small>Copyright © 2025 © Neuro | All Rights Reserved | <a href="#">Privacy Policy</a></small>
</footer>


</body>
</html>
