<?php
// stroke1.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Ischemic Stroke – NeuroAid</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    html {
      scroll-behavior: smooth;
    }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f6f9f9;
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
    .nav-login {
      display: flex;
      margin-right: 60px;
    }
    .hero {
      background: white;
      padding: 30px;
      text-align: center;
    }
    .hero h2 {
      color: #1D4C43;
      margin-bottom: 12px;
      font-size: 24px;
    }
    .hero p {
      max-width: 680px;
      margin: 0 auto 20px;
      line-height: 1.6;
      font-size: 15px;
    }
    .hero button {
      padding: 12px 32px;
      background: white;
      border: 2px solid #1D4C43;
      color: #1D4C43;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
      position: relative;
      font-size: 16px;
    }
    .hero button:after {
      content: '▼';
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 0.8rem;
    }
    .images-row {
      display: flex;
      justify-content: center;
      background: white;
      padding: 30px;
      gap: 24px;
    }
    .images-row img {
      width: 320px;
      height: 320px;
      border-radius: 10px;
      object-fit: cover;
      border: 6px solid #1D4C43;
      box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }
    .extra-content {
      display: none;
      background: white;
    }
    .extra-content .hero {
      padding-top: 0;
    }
    .charts {
      display: flex;
      justify-content: center;
      padding: 30px 50px;
      background: white;
      gap: 40px;
      align-items: stretch;
    }
    .chart {
      flex: 1 1 45%;
      max-width: 500px;
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
    }
    canvas {
      width: 100% !important;
      height: 500px !important;
      margin-bottom: 15px;
    }
    .chart p {
      font-size: 14px;
      color: #333;
      background: #eaf7f5;
      padding: 12px;
      border-radius: 6px;
      line-height: 1.5;
    }
  </style>
</head>
<body>

<header>
  <div class="top-bar">
    <div class="top-bar-left">
      <img src="logo.png" alt="Logo" class="logo">
      <h1><span style="color: #1D4C43; font-weight: 600;">Neuro</span><span style="color: black; font-weight: 600;">Aid</span></h1>
    </div>
    <div class="contact">
      <span style="display: flex; align-items: center;"><img src="phone-icon.png" alt="Phone" style="height: 16px; margin-right: 6px;">+1 (800) 777-NEUR (6387)</span><br>
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

<section class="hero" id="ischemicSection">
  <h2>ISCHEMIC STROKE</h2>
  <p>An ischemic stroke occurs when a blood vessel supplying the brain becomes blocked. It is the most common type, accounting for nearly 87% of all cases.</p>
  <button id="toggleIschemic">Show More</button>
</section>
<div class="images-row">
  <img src="image/ischemic1.jpg" alt="Scan 1">
  <img src="image/ischemic2.jpg" alt="Scan 2">
  <img src="image/ischemic3.jpg" alt="Brain Diagram">
  <img src="image/ischemic4.jpg" alt="Blood Vessel">
</div>
<div class="extra-content" id="extraIschemic">
  <div class="hero"><button id="hideIschemic">Show Less</button></div>
  <div class="charts">
    <div class="chart"><h3>Gender Distribution</h3><canvas id="pieChartIschemic"></canvas><p>Ischemic strokes affect both genders nearly equally. Men have a slightly higher rate due to lifestyle factors.</p></div>
    <div class="chart"><h3>Age Group Distribution</h3><canvas id="barChartIschemic"></canvas><p>Strokes increase significantly with age, particularly among those over 65.</p></div>
  </div>
</div>

<section class="hero" id="hemorrhagicSection">
  <h2>HEMORRHAGIC STROKE</h2>
  <p>Occurs when a blood vessel bursts in the brain, leading to bleeding and pressure on brain tissue. It's less common but more severe.</p>
  <button id="toggleHemorrhagic">Show More</button>
</section>
<div class="images-row">
  <img src="image/hemorrhagic1.jpg" alt="Scan 1">
  <img src="image/hemorrhagic2.jpg" alt="Scan 2">
  <img src="image/hemorrhagic3.jpg" alt="Brain Diagram">
  <img src="image/hemorrhagic4.jpg" alt="Blood Vessel">
</div>
<div class="extra-content" id="extraHemorrhagic">
  <div class="hero"><button id="hideHemorrhagic">Show Less</button></div>
  <div class="charts">
    <div class="chart"><h3>Gender Distribution</h3><canvas id="pieChartHemorrhagic"></canvas><p>The pie chart shows that hemorrhagic strokes are more common in men, who make up 54.6% of the cases, while women account for 45.4%. This indicates a moderate gender difference, with men experiencing hemorrhagic strokes more frequently. The disparity may be influenced by factors such as lifestyle, underlying health conditions, and biological differences, warranting gender-specific awareness and prevention strategies.</p></div>
    <div class="chart"><h3>Age Group Distribution</h3><canvas id="barChartHemorrhagic"></canvas><p>The bar chart indicates that the highest incidence of hemorrhagic stroke occurs in individuals aged 50–69 and 70+, with both age groups showing nearly equal and elevated rates compared to the 15–49 age group. Those aged 15–49 have a noticeably lower incidence, suggesting that age is a significant risk factor. This trend underscores the importance of monitoring and managing stroke risk factors more closely as individuals age.</p></div>
  </div>
</div>

<section class="hero" id="tiaSection">
  <h2>TRANSIENT ISCHEMIC ATTACK</h2>
  <p>A TIA is a brief blockage of blood flow to the brain that resolves within minutes to hours without lasting damage. It serves as a warning stroke.</p>
  <button id="toggleTIA">Show More</button>
</section>
<div class="images-row">
  <img src="image/transient1.jpg" alt="Scan 1">
  <img src="image/transient2.jpg" alt="Scan 2">
  <img src="image/transient3.jpg" alt="Brain Diagram">
  <img src="image/transient4.jpg" alt="Blood Vessel">
</div>
<div class="extra-content" id="extraTIA">
  <div class="hero"><button id="hideTIA">Show Less</button></div>
  <div class="charts">
    <div class="chart"><h3>Gender Distribution</h3><canvas id="pieChartTIA"></canvas><p>The pie chart illustrates the gender distribution of Transient Ischemic Attack cases, showing that women account for 54% while men represent 46% of the reported cases. This suggests a slightly higher prevalence of TIAs among women compared to men. The data may indicate gender-related risk factors or differences in health-seeking behavior and diagnosis rates, highlighting the need for targeted awareness and prevention strategies for both genders, with a particular focus on women.</p></div>
    <div class="chart"><h3>Age Group Distribution</h3><canvas id="barChartTIA"></canvas><p>The bar chart presents the distribution of TIA cases across three age groups: 15–49, 50–69, and 70+. The data reveals that the majority of TIAs occur in individuals aged 50 and older, with the 50–69 age group accounting for approximately 45% and the 70+ group close behind at around 43%. In contrast, only about 12% of cases occur in the 15–49 age range. This indicates that age is a significant risk factor for TIAs, underscoring the importance of early detection and preventive care in older adults.</p></div>
  </div>
</div>

<script>
function setupToggle(section, toggleBtnId, hideBtnId, extraId) {
  document.getElementById(toggleBtnId).addEventListener('click', () => {
    document.getElementById(extraId).style.display = 'block';
    document.getElementById(toggleBtnId).style.display = 'none';
  });
  document.getElementById(hideBtnId).addEventListener('click', () => {
    document.getElementById(extraId).style.display = 'none';
    document.getElementById(toggleBtnId).style.display = 'inline-block';
  });
}

setupToggle('ischemicSection', 'toggleIschemic', 'hideIschemic', 'extraIschemic');
setupToggle('hemorrhagicSection', 'toggleHemorrhagic', 'hideHemorrhagic', 'extraHemorrhagic');
setupToggle('tiaSection', 'toggleTIA', 'hideTIA', 'extraTIA');

function createCharts(pieId, barId, pieData, barData) {
  new Chart(document.getElementById(pieId), {
    type: 'pie',
    data: {
      labels: ['Men', 'Women'],
      datasets: [{
        data: pieData,
        backgroundColor: ['#1D4C43', '#9AD0C2']
      }]
    },
    options: {
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });

  new Chart(document.getElementById(barId), {
    type: 'bar',
    data: {
      labels: ['15–49', '50–64', '70+'],
      datasets: [{
        label: 'Percentage of Cases',
        data: barData,
        backgroundColor: '#1D4C43'
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 10,
            callback: value => value + '%'
          }
        }
      },
      plugins: {
        legend: { display: false }
      }
    }
  });
}

createCharts('pieChartIschemic', 'barChartIschemic', [52, 48], [10, 30, 60]);
createCharts('pieChartHemorrhagic', 'barChartHemorrhagic', [54.6, 45.4], [22, 39, 38]);
createCharts('pieChartTIA', 'barChartTIA', [46, 54], [11, 45, 43]);
</script>
</body>
</html>
