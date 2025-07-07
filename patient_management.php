<?php
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header("Location: access_denied.php");
    exit;
}

// Check if user is admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['is_admin']) {
    header("Location: access_denied.php");
    exit();
}

// Get patient list
$search = isset($_GET['search']) ? "%".$_GET['search']."%" : "%";
$stmt = $pdo->prepare("SELECT 
                        u.id, 
                        u.full_name, 
                        u.stroke_type, 
                        TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age,
                        (SELECT mood FROM daily_vitals WHERE user_id = u.id ORDER BY date DESC LIMIT 1) as mood_status
                      FROM users u 
                      WHERE u.role = 'patient' AND u.full_name LIKE ?
                      ORDER BY u.full_name LIMIT 50");
$stmt->execute([$search]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NeuroAid - Patient Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary: #1D4C43;
      --white: #ffffff;
      --light-bg: #f8f9fa;
      --gray: #ced4da;
      --text-dark: #212529;
      --shadow: rgba(0, 0, 0, 0.08);
      --highlight: #31A06A;
      --hover-bg: #D3E2D9;
      --hover-text: #228C3E;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      background: var(--light-bg);
      color: var(--text-dark);
    }

    .header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: var(--white);
      border-bottom: 1px solid var(--gray);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 30px;
      z-index: 1000;
      box-shadow: 0 2px 4px var(--shadow);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo img {
      height: 40px;
    }

    .logo span {
      font-size: 20px;
      font-weight: bold;
    }

    .logo span span {
      color: var(--primary);
    }

    .datetime {
      font-size: 14px;
      font-weight: bold;
      color: var(--primary);
    }

    .layout {
      display: flex;
      margin-top: 60px;
    }

    .sidebar {
      width: 240px;
      background: var(--white);
      padding: 20px;
      border-right: 1px solid var(--gray);
      height: calc(100vh - 60px);
      position: fixed;
      top: 60px;
      left: 0;
      overflow-y: auto;
    }

    .search-wrapper {
      position: relative;
      margin-bottom: 25px;
    }

    .search-wrapper input,
    .search-container input {
      width: 100%;
      padding: 8px 12px 8px 34px;
      border: 1px solid var(--gray);
      border-radius: 6px;
    }

    .search-wrapper i,
    .search-container i {
      position: absolute;
      top: 50%;
      left: 10px;
      transform: translateY(-50%);
      color: #777;
    }

    .menu-section {
      font-size: 12px;
      color: #777;
      margin: 20px 0 12px;
      padding-bottom: 4px;
      border-bottom: 1px solid var(--gray);
      text-transform: uppercase;
    }

    .menu-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      background: none;
      border: none;
      width: 100%;
      padding: 10px 16px;
      margin-bottom: 8px;
      border-radius: 999px;
      text-align: left;
      cursor: pointer;
      color: var(--text-dark);
      text-decoration: none;
      position: relative;
      transition: 0.3s ease;
    }

    .menu-btn i {
      width: 20px;
    }

    .menu-btn:hover,
    .menu-btn.active {
      background-color: var(--hover-bg);
      color: var(--hover-text);
      font-weight: 600;
    }

    .menu-btn:hover::before,
    .menu-btn.active::before {
      content: '';
      position: absolute;
      left: 6px;
      top: 50%;
      transform: translateY(-50%);
      height: 60%;
      width: 6px;
      background-color: var(--highlight);
      border-radius: 4px;
    }

    .logout {
      margin-top: 30px;
    }

    .main {
      margin-left: 240px;
      padding: 30px;
      width: calc(100% - 240px);
    }

    .table-section {
      background: var(--white);
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 6px var(--shadow);
    }

    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .table-header h3 {
      color: var(--primary);
    }

    .search-container {
      position: relative;
      display: flex;
      align-items: center;
    }

    .search-container button {
      margin-left: 10px;
      padding: 8px 14px;
      background-color: var(--highlight);
      border: none;
      color: white;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.3s;
    }

    .search-container button:hover {
      background-color: #268a54;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background-color: #f1f1f1;
    }

    th, td {
      padding: 12px 10px;
      border-bottom: 1px solid #e0e0e0;
      text-align: left;
    }

    tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    tr:hover {
      background-color: #eef4f1;
    }

    .view-btn {
      padding: 6px 12px;
      background: var(--highlight);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }

    .view-btn:hover {
      background: #228c3e;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow-y: auto;
      background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
      position: relative;
      background: white;
      margin: 5% auto;
      padding: 30px;
      width: 80%;
      max-width: 1200px;
      border-radius: 12px;
      border: 3px solid #007bff;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-15px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .modal-title {
      margin-top: 0;
      color: var(--primary);
      font-size: 24px;
      font-weight: 700;
    }

    .modal-close {
      position: absolute;
      top: 16px;
      right: 20px;
      background: none;
      border: none;
      font-size: 24px;
      font-weight: bold;
      color: var(--text-dark);
      cursor: pointer;
    }

    .modal-close:hover {
      color: var(--highlight);
    }

    .modal-group {
      margin: 25px 0;
    }

    .modal-label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: var(--primary);
    }

    .modal-input {
      width: 100%;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid var(--gray);
      background-color: #f9f9f9;
      font-size: 14px;
    }

    .modal-flex {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .modal-box {
      flex: 1 1 48%;
      background-color: #f9f9f9;
      padding: 20px;
      border-left: 4px solid var(--highlight);
      border-radius: 8px;
    }

    .modal-box h4 {
      margin-bottom: 12px;
      color: var(--primary);
    }

    .modal-box p {
      margin: 8px 0;
      font-size: 14px;
    }

    .modal-footer {
      margin-top: 30px;
      text-align: right;
    }

    .modal-close-btn {
      padding: 10px 20px;
      background: #3e4c59;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
    }

    .modal-close-btn:hover {
      background-color: #2d3a45;
    }

    /* Health Summary Styles */
    .health-summary {
      margin-top: 30px;
    }

    .summary-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .summary-header h3 {
      color: var(--primary);
    }

    .chart-container {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      margin-bottom: 30px;
    }

    .chart-box {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .chart-box h4 {
      margin-bottom: 15px;
      color: var(--primary);
      text-align: center;
    }

    .chart-wrapper {
      position: relative;
      height: 250px;
      width: 100%;
    }

    .vital-stats {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      margin-top: 20px;
    }

    .vital-card {
      background: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      text-align: center;
    }

    .vital-card h5 {
      color: var(--primary);
      margin-bottom: 10px;
    }

    .vital-value {
      font-size: 24px;
      font-weight: bold;
      color: #333;
    }

    .vital-label {
      font-size: 12px;
      color: #666;
      margin-top: 5px;
    }

    .bp-value { color: #e63946; }
    .bs-value { color: #457b9d; }
    .hr-value { color: #2a9d8f; }
    .mood-value { color: #9c89b8; }

    .recent-entries {
      margin-top: 30px;
    }

    .recent-entries h4 {
      color: var(--primary);
      margin-bottom: 15px;
    }

    .entries-table {
      width: 100%;
      border-collapse: collapse;
    }

    .entries-table th {
      background-color: #f1f1f1;
      padding: 10px;
      text-align: left;
    }

    .entries-table td {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    .entries-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .mood-Happy { color: #2a9d8f; }
    .mood-Sad { color: #e63946; }
    .mood-Anxious { color: #f4a261; }
    .mood-Angry { color: #d62828; }
    .mood-Tired { color: #457b9d; }
    .mood-Neutral { color: #6c757d; }
    .patient-details-container {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    .details-section {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .details-section h3 {
      color: var(--primary);
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .details-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
    }
    
    .combined-chart-container {
      margin-top: 20px;
    }
    
    .combined-chart-container .chart-box {
      width: 100% !important;
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="logo">
      <img src="image/logo.png" alt="NeuroAid Logo">
      <span>Neuro<span>Aid</span> - Patient Management</span>
    </div>
    <div class="datetime">
      <?php 
      echo date('l, F j, Y - g:i:s A'); 
      ?>
    </div>
  </div>

  <div class="layout">
    <div class="sidebar">
      <div class="search-wrapper">
        <input type="text" placeholder="Search..." />
        <i class="fas fa-search"></i>
      </div>

      <div class="menu-section">Admin Tools</div>
      <a href="admin_dashboard.php" class="menu-btn"><i class="fas fa-home"></i>Dashboard</a>
      <a href="patient_management.php" class="menu-btn active"><i class="fas fa-user"></i>Patient Management</a>
      <a href="monitoring.php" class="menu-btn"><i class="fas fa-heartbeat"></i>Monitoring</a>
      <a href="admin_chats.php" class="menu-btn"><i class="fas fa-comments"></i>Chat</a>
      <a href="caregiver.php" class="menu-btn"><i class="fas fa-hands-helping"></i>CareGiver Management</a>
      <a href="content.php" class="menu-btn"><i class="fas fa-file-alt"></i>Content Manager</a>
      <a href="feedback.php" class="menu-btn"><i class="fas fa-exclamation-circle"></i>Feedback & Issues</a>

      <div class="menu-section">Settings</div>
      <a href="settings.php" class="menu-btn"><i class="fas fa-user-cog"></i>Settings</a>

      <div class="logout">
        <a href="logout.php" class="menu-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
      </div>
    </div>

    <div class="main">
      <h2 style="margin-bottom: 20px; color: var(--primary); font-size: 24px;">Patient Management</h2>

      <div class="table-section">
        <div class="table-header">
          <h3>Patient List</h3>
          <form method="GET" class="search-container">
            <input type="text" name="search" placeholder="Search Patients..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
            <i class="fas fa-search"></i>
            <button type="submit">Search</button>
          </form>
        </div>
        <table>
          <thead>
            <tr>
              <th>Patient's Name</th>
              <th>Stroke Type</th>
              <th>Age</th>
              <th>Mood</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($patients as $patient): ?>
            <tr>
              <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
              <td><?php echo htmlspecialchars($patient['stroke_type']); ?></td>
              <td><?php echo htmlspecialchars($patient['age']); ?></td>
              <td class="mood-<?php echo htmlspecialchars($patient['mood_status'] ?? 'Neutral'); ?>">
                <?php echo htmlspecialchars($patient['mood_status'] ?? 'N/A'); ?>
              </td>
              <td><button class="view-btn" data-id="<?php echo $patient['id']; ?>">View Details</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Patient Details Modal -->
  <div id="patientModal" class="modal">
  <div class="modal-content" style="max-width: 1200px;">
    <button class="modal-close">&times;</button>
    <h2 class="modal-title">Patient Health Dashboard</h2>
    <div id="modalContent"></div>
    <div class="modal-footer">
      <button class="modal-close-btn">Close</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Global variables to store chart instances
  let bpChart = null;
  let bsChart = null;
  let hrChart = null;
  let moodChart = null;

  const modal = document.getElementById("patientModal");
  const viewButtons = document.querySelectorAll(".view-btn");
  const closeBtns = document.querySelectorAll(".modal-close, .modal-close-btn");
  const modalContent = document.getElementById("modalContent");

  viewButtons.forEach(btn => {
    btn.addEventListener("click", async () => {
      const patientId = btn.getAttribute("data-id");
      
      try {
        // Show loading state
        modalContent.innerHTML = "<p>Loading patient data...</p>";
        modal.style.display = "block";
        
        // Fetch patient details
        const [detailsResponse, vitalsResponse] = await Promise.all([
          fetch(`get_patient_details.php?id=${patientId}`),
          fetch(`get_patient_vitals.php?id=${patientId}`)
        ]);
        
        const data = await detailsResponse.json();
        const vitalsData = await vitalsResponse.json();
        
        if (!detailsResponse.ok || !vitalsResponse.ok) {
          throw new Error(data.message || 'Failed to load patient data');
        }
          
        // Populate modal with patient data
        modalContent.innerHTML = `
    <div class="patient-details-container">
      <!-- Personal Information Section -->
      <div class="details-section">
        <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
        <div class="details-grid">
          <div>
            <p><strong>Full Name:</strong> ${data.full_name}</p>
            <p><strong>Patient ID:</strong> ${data.id}</p>
            <p><strong>Date of Birth:</strong> ${data.date_of_birth} (Age: ${data.age})</p>
          </div>
          <div>
            <p><strong>Gender:</strong> ${data.gender}</p>
            <p><strong>Contact:</strong> ${data.phone || 'N/A'}</p>
            <p><strong>Email:</strong> ${data.email}</p>
          </div>
          <div>
            <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
          </div>
        </div>
      </div>

      <!-- Medical Information Section -->
      <div class="details-section">
        <h3><i class="fas fa-heartbeat"></i> Medical Information</h3>
        <div class="details-grid">
          <div>
            <p><strong>Stroke Type:</strong> ${data.stroke_type}</p>
            <p><strong>Stroke Date:</strong> ${data.stroke_date}</p>
            <p><strong>Affected Side:</strong> ${data.affected_side || 'N/A'}</p>
          </div>
          <div>
            <p><strong>Severity:</strong> ${data.stroke_severity || 'N/A'}</p>
            <p><strong>Rehab Status:</strong> ${data.rehabilitation_status || 'N/A'}</p>
            <p><strong>Medical History:</strong> ${data.medical_history ? data.medical_history.substring(0, 50) + '...' : 'N/A'}</p>
          </div>
          <div>
            <p><strong>Current Medications:</strong> ${data.current_medications ? data.current_medications.substring(0, 50) + '...' : 'N/A'}</p>
            <p><strong>Allergies:</strong> ${data.allergies || 'None reported'}</p>
          </div>
        </div>
      </div>

      <!-- Caregiver Information Section -->
      <div class="details-section">
        <h3><i class="fas fa-hands-helping"></i> Caregiver Information</h3>
        <div class="details-grid">
          <div>
            <p><strong>Name:</strong> ${data.caregiver_name || 'N/A'}</p>
            <p><strong>Relationship:</strong> ${data.caregiver_relationship || 'N/A'}</p>
          </div>
          <div>
            <p><strong>Contact:</strong> ${data.caregiver_phone || 'N/A'}</p>
            <p><strong>Email:</strong> ${data.caregiver_email || 'N/A'}</p>
          </div>
        </div>
      </div>

      <!-- Combined Health Metrics Section -->
      <div class="health-summary">
        <div class="summary-header">
          <h3><i class="fas fa-chart-line"></i> Combined Health Metrics</h3>
          <div>
            <select id="timeRange" class="modal-input" style="width: auto; padding: 5px 10px;">
              <option value="7">Last 7 Days</option>
              <option value="14">Last 14 Days</option>
              <option value="30">Last 30 Days</option>
            </select>
          </div>
        </div>

        <!-- Vital Stats Cards -->
        <div class="vital-stats">
          <div class="vital-card">
            <h5><i class="fas fa-heartbeat"></i> Blood Pressure</h5>
            <div class="vital-value bp-value">${data.blood_pressure || 'N/A'}</div>
            <div class="vital-label">Latest Reading</div>
          </div>
          <div class="vital-card">
            <h5><i class="fas fa-tint"></i> Blood Sugar</h5>
            <div class="vital-value bs-value">${data.blood_sugar ? data.blood_sugar + ' mg/dL' : 'N/A'}</div>
            <div class="vital-label">Latest Reading</div>
          </div>
          <div class="vital-card">
            <h5><i class="fas fa-heart"></i> Heart Rate</h5>
            <div class="vital-value hr-value">${data.heart_rate ? data.heart_rate + ' bpm' : 'N/A'}</div>
            <div class="vital-label">Latest Reading</div>
          </div>
          <div class="vital-card">
            <h5><i class="fas fa-smile"></i> Mood</h5>
            <div class="vital-value mood-value">${data.mood_status || 'N/A'}</div>
            <div class="vital-label">Current Mood</div>
          </div>
        </div>

        <!-- Combined Chart -->
        <div class="combined-chart-container">
          <div class="chart-box" style="width: 100%;">
            <h4>Health Metrics Trend</h4>
            <div class="chart-wrapper" style="height: 400px;">
              <canvas id="combinedChart"></canvas>
            </div>
          </div>
        </div>

        <!-- Recent Entries -->
        <div class="recent-entries">
          <h4><i class="fas fa-history"></i> Recent Vital Entries</h4>
          <table class="entries-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Blood Pressure</th>
                <th>Blood Sugar</th>
                <th>Heart Rate</th>
                <th>Mood</th>
              </tr>
            </thead>
            <tbody id="vitalsTableBody">
              ${vitalsData.slice(0, 5).map(vital => `
                <tr>
                  <td>${vital.date}</td>
                  <td>${vital.blood_pressure_systolic}/${vital.blood_pressure_diastolic}</td>
                  <td>${vital.blood_sugar || 'N/A'}</td>
                  <td>${vital.heart_rate}</td>
                  <td class="mood-${vital.mood}">${vital.mood}</td>
                </tr>
              `).join('')}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  `;

        // Add event listener for time range change
        document.getElementById('timeRange').addEventListener('change', async function() {
          const days = parseInt(this.value);
          try {
            const response = await fetch(`get_patient_vitals.php?id=${patientId}&days=${days}`);
            const newData = await response.json();
            updateCharts(newData);
            // Update recent entries table
            document.getElementById('vitalsTableBody').innerHTML = newData.slice(0, 5).map(vital => `
              <tr>
                <td>${vital.date}</td>
                <td>${vital.blood_pressure_systolic}/${vital.blood_pressure_diastolic}</td>
                <td>${vital.blood_sugar || 'N/A'}</td>
                <td>${vital.heart_rate}</td>
                <td class="mood-${vital.mood}">${vital.mood}</td>
              </tr>
            `).join('');
          } catch (error) {
            console.error("Error fetching updated vitals:", error);
          }
        });

        // Initialize charts after DOM is updated
        setTimeout(() => {
          try {
            createCharts(vitalsData);
          } catch (chartError) {
            console.error("Error creating charts:", chartError);
            modalContent.innerHTML += `<p class="error">Error loading charts. Please try again.</p>`;
          }
        }, 100);

      } catch (error) {
        console.error("Error fetching patient details:", error);
        modalContent.innerHTML = `
          <p class="error">Error loading patient details: ${error.message}</p>
          <button onclick="window.location.reload()">Try Again</button>
        `;
      }
    });
  });

  function destroyCharts() {
    [bpChart, bsChart, hrChart, moodChart].forEach(chart => {
      if (chart) {
        try {
          chart.destroy();
        } catch (e) {
          console.error("Error destroying chart:", e);
        }
      }
    });
    bpChart = bsChart = hrChart = moodChart = null;
  }

  function createCharts(vitalsData) {
    destroyCharts();
    
    const combinedCanvas = document.getElementById('combinedChart');
    if (!combinedCanvas) return;
    
    const dates = vitalsData.map(v => v.date);
    const moodMap = {'Happy':5,'Neutral':4,'Tired':3,'Anxious':2,'Sad':1,'Angry':0};
    
    combinedChart = new Chart(combinedCanvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: dates,
        datasets: [
          {
            label: 'Systolic BP',
            data: vitalsData.map(v => v.blood_pressure_systolic),
            borderColor: '#e63946',
            backgroundColor: 'rgba(230, 57, 70, 0.1)',
            yAxisID: 'y',
            tension: 0.3
          },
          {
            label: 'Diastolic BP',
            data: vitalsData.map(v => v.blood_pressure_diastolic),
            borderColor: '#9e2a2b',
            backgroundColor: 'rgba(158, 42, 43, 0.1)',
            yAxisID: 'y',
            tension: 0.3
          },
          {
            label: 'Blood Sugar',
            data: vitalsData.map(v => v.blood_sugar),
            borderColor: '#457b9d',
            backgroundColor: 'rgba(69, 123, 157, 0.1)',
            yAxisID: 'y1',
            tension: 0.3
          },
          {
            label: 'Heart Rate',
            data: vitalsData.map(v => v.heart_rate),
            borderColor: '#2a9d8f',
            backgroundColor: 'rgba(42, 157, 143, 0.1)',
            yAxisID: 'y',
            tension: 0.3
          },
          {
            label: 'Mood',
            data: vitalsData.map(v => moodMap[v.mood] || 3),
            borderColor: '#9c89b8',
            backgroundColor: 'rgba(156, 137, 184, 0.1)',
            yAxisID: 'y2',
            tension: 0.3,
            pointBackgroundColor: vitalsData.map(v => getMoodColor(v.mood))
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            title: { display: true, text: 'BP/HR' },
            grid: { drawOnChartArea: false }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            title: { display: true, text: 'Blood Sugar (mg/dL)' },
            grid: { drawOnChartArea: false }
          },
          y2: {
            type: 'linear',
            display: true,
            position: 'right',
            title: { display: true, text: 'Mood' },
            min: 0,
            max: 5,
            ticks: {
              callback: function(value) {
                const moods = ['Angry','Sad','Anxious','Tired','Neutral','Happy'];
                return moods[value] || '';
              }
            },
            grid: { drawOnChartArea: false }
          }
        }
      }
    });
  }

  function chartOptions(unit) {
    return {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: false,
          title: { display: true, text: unit }
        }
      }
    };
  }

  function getMoodColor(mood) {
    const colors = {
      'Happy': '#2a9d8f',
      'Neutral': '#6c757d',
      'Tired': '#457b9d',
      'Anxious': '#f4a261',
      'Sad': '#e63946',
      'Angry': '#d62828'
    };
    return colors[mood] || '#6c757d';
  }

  function updateCharts(vitalsData) {
    if (!combinedChart) return;
    
    const dates = vitalsData.map(v => v.date);
    const moodMap = {'Happy':5,'Neutral':4,'Tired':3,'Anxious':2,'Sad':1,'Angry':0};
    
    combinedChart.data.labels = dates;
    combinedChart.data.datasets[0].data = vitalsData.map(v => v.blood_pressure_systolic);
    combinedChart.data.datasets[1].data = vitalsData.map(v => v.blood_pressure_diastolic);
    combinedChart.data.datasets[2].data = vitalsData.map(v => v.blood_sugar);
    combinedChart.data.datasets[3].data = vitalsData.map(v => v.heart_rate);
    combinedChart.data.datasets[4].data = vitalsData.map(v => moodMap[v.mood] || 3);
    combinedChart.data.datasets[4].pointBackgroundColor = vitalsData.map(v => getMoodColor(v.mood));
    combinedChart.update();
  }

  closeBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      modal.style.display = "none";
      destroyCharts();
    });
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
      destroyCharts();
    }
  });
</script>
</body>
</html>