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

// Get patient list with monitoring data
$search = isset($_GET['search']) ? "%".$_GET['search']."%" : "%";
$stmt = $pdo->prepare("SELECT 
                        u.id, 
                        u.full_name, 
                        u.stroke_type, 
                        TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age,
                        (SELECT mood FROM daily_vitals WHERE user_id = u.id ORDER BY date DESC LIMIT 1) as mood_status,
                        (SELECT CONCAT(blood_pressure_systolic, '/', blood_pressure_diastolic) 
                         FROM daily_vitals WHERE user_id = u.id ORDER BY date DESC LIMIT 1) as bp_status,
                        (SELECT COUNT(*) FROM monitor_game_sessions WHERE user_id = u.id AND DATE(created_at) = CURDATE()) as today_games
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
  <title>NeuroAid - Patient Monitoring</title>
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
      margin-bottom: 20px;
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

    /* Monitoring Dashboard Styles */
    .monitoring-dashboard {
      margin-top: 30px;
    }

    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .dashboard-header h3 {
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

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
      margin-top: 20px;
    }

    .stat-card {
      background: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      text-align: center;
    }

    .stat-card h5 {
      color: var(--primary);
      margin-bottom: 10px;
    }

    .stat-value {
      font-size: 24px;
      font-weight: bold;
      color: #333;
    }

    .stat-label {
      font-size: 12px;
      color: #666;
      margin-top: 5px;
    }

    .bp-value { color: #e63946; }
    .bs-value { color: #457b9d; }
    .hr-value { color: #2a9d8f; }
    .mood-value { color: #9c89b8; }
    .game-value { color: #f4a261; }
    .duration-value { color: #6a4c93; }
    .avg-value { color: #38b000; }

    .recent-data {
      margin-top: 30px;
    }

    .recent-data h4 {
      color: var(--primary);
      margin-bottom: 15px;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th {
      background-color: #f1f1f1;
      padding: 10px;
      text-align: left;
    }

    .data-table td {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }

    .data-table tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .mood-Happy { color: #2a9d8f; }
    .mood-Sad { color: #e63946; }
    .mood-Anxious { color: #f4a261; }
    .mood-Angry { color: #d62828; }
    .mood-Tired { color: #457b9d; }
    .mood-Neutral { color: #6c757d; }

    .tab-container {
      margin-top: 20px;
    }

    .tab-buttons {
      display: flex;
      border-bottom: 1px solid #ddd;
      margin-bottom: 20px;
    }

    .tab-btn {
      padding: 10px 20px;
      background: none;
      border: none;
      cursor: pointer;
      font-weight: 600;
      color: #555;
      border-bottom: 3px solid transparent;
    }

    .tab-btn.active {
      color: var(--primary);
      border-bottom: 3px solid var(--primary);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }

    .badge-success {
      background-color: #d4edda;
      color: #155724;
    }

    .badge-warning {
      background-color: #fff3cd;
      color: #856404;
    }

    .badge-danger {
      background-color: #f8d7da;
      color: #721c24;
    }

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
      <span>Neuro<span>Aid</span> - Patient Monitoring</span>
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
      <a href="patient_management.php" class="menu-btn"><i class="fas fa-user"></i>Patient Management</a>
      <a href="monitoring.php" class="menu-btn active"><i class="fas fa-heartbeat"></i>Monitoring</a>
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
      <h2 style="margin-bottom: 20px; color: var(--primary); font-size: 24px;">Patient Monitoring Dashboard</h2>

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
              <th>Blood Pressure</th>
              <th>Mood</th>
              <th>Today's Games</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($patients as $patient): ?>
            <tr>
              <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
              <td><?php echo htmlspecialchars($patient['stroke_type']); ?></td>
              <td><?php echo htmlspecialchars($patient['age']); ?></td>
              <td><?php echo htmlspecialchars($patient['bp_status'] ?? 'N/A'); ?></td>
              <td class="mood-<?php echo htmlspecialchars($patient['mood_status'] ?? 'Neutral'); ?>">
                <?php echo htmlspecialchars($patient['mood_status'] ?? 'N/A'); ?>
              </td>
              <td><?php echo htmlspecialchars($patient['today_games']); ?></td>
              <td><button class="view-btn" data-id="<?php echo $patient['id']; ?>">View Metrics</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Patient Monitoring Modal -->
  <div id="monitoringModal" class="modal">
    <div class="modal-content" style="max-width: 1400px;">
      <button class="modal-close">&times;</button>
      <h2 class="modal-title">Patient Monitoring Dashboard</h2>
      <div id="modalMonitoringContent"></div>
      <div class="modal-footer">
        <button class="modal-close-btn">Close</button>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Global variables to store chart instances
  let vitalChart = null;
  let gameChart = null;
  let moodChart = null;
  let combinedChart = null;

  const modal = document.getElementById("monitoringModal");
  const viewButtons = document.querySelectorAll(".view-btn");
  const closeBtns = document.querySelectorAll(".modal-close, .modal-close-btn");
  const modalContent = document.getElementById("modalMonitoringContent");

  viewButtons.forEach(btn => {
    btn.addEventListener("click", async () => {
      const patientId = btn.getAttribute("data-id");
      
      try {
        // Show loading state
        modalContent.innerHTML = "<p>Loading patient monitoring data...</p>";
        modal.style.display = "block";
        
        // Fetch all monitoring data
        const [detailsResponse, vitalsResponse, gamesResponse, moodResponse] = await Promise.all([
          fetch(`get_patient_details.php?id=${patientId}`),
          fetch(`get_patient_vitals.php?id=${patientId}`),
          fetch(`get_patient_games.php?id=${patientId}`),
          fetch(`get_patient_mood.php?id=${patientId}`)
        ]);
        
        const data = await detailsResponse.json();
        const vitalsData = await vitalsResponse.json();
        const gamesData = await gamesResponse.json();
        const moodData = await moodResponse.json();
        
        if (!detailsResponse.ok || !vitalsResponse.ok || !gamesResponse.ok || !moodResponse.ok) {
          throw new Error('Failed to load patient monitoring data');
        }
          
        // Populate modal with monitoring data
        modalContent.innerHTML = `
          <div class="patient-details-container">
            <!-- Patient Summary Section -->
            <div class="details-section">
              <h3><i class="fas fa-user-circle"></i> Patient Summary</h3>
              <div class="details-grid">
                <div>
                  <p><strong>Name:</strong> ${data.full_name}</p>
                  <p><strong>Age:</strong> ${data.age}</p>
                  <p><strong>Stroke Type:</strong> ${data.stroke_type}</p>
                </div>
                <div>
                  <p><strong>Last BP:</strong> ${data.blood_pressure || 'N/A'}</p>
                  <p><strong>Last HR:</strong> ${data.heart_rate ? data.heart_rate + ' bpm' : 'N/A'}</p>
                  <p><strong>Last BS:</strong> ${data.blood_sugar ? data.blood_sugar + ' mg/dL' : 'N/A'}</p>
                </div>
                <div>
                  <p><strong>Current Mood:</strong> <span class="mood-${data.mood_status || 'Neutral'}">${data.mood_status || 'N/A'}</span></p>
                  <p><strong>Today's Games:</strong> ${gamesData.today_count || 0}</p>
                  <p><strong>Avg Game Duration:</strong> ${gamesData.avg_duration ? Math.round(gamesData.avg_duration) + ' mins' : 'N/A'}</p>
                </div>
              </div>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-container">
              <div class="tab-buttons">
                <button class="tab-btn active" data-tab="vitals">Vital Signs</button>
                <button class="tab-btn" data-tab="cognitive">Cognitive Games</button>
                <button class="tab-btn" data-tab="mood">Mood Tracking</button>
                <button class="tab-btn" data-tab="combined">Combined View</button>
              </div>

              <!-- Vitals Tab -->
              <div id="vitals" class="tab-content active">
                <div class="monitoring-dashboard">
                  <div class="dashboard-header">
                    <h3><i class="fas fa-heartbeat"></i> Vital Signs Monitoring</h3>
                    <div>
                      <select id="vitalsTimeRange" class="modal-input" style="width: auto; padding: 5px 10px;">
                        <option value="7">Last 7 Days</option>
                        <option value="14">Last 14 Days</option>
                        <option value="30">Last 30 Days</option>
                      </select>
                    </div>
                  </div>

                  <!-- Vital Stats Cards -->
                  <div class="stats-grid">
                    <div class="stat-card">
                      <h5><i class="fas fa-heartbeat"></i> Avg BP</h5>
                      <div class="stat-value bp-value">${vitalsData.avg_bp || 'N/A'}</div>
                      <div class="stat-label">Systolic/Diastolic</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-tint"></i> Avg Blood Sugar</h5>
                      <div class="stat-value bs-value">${vitalsData.avg_bs ? vitalsData.avg_bs + ' mg/dL' : 'N/A'}</div>
                      <div class="stat-label">Last ${vitalsData.length} readings</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-heart"></i> Avg Heart Rate</h5>
                      <div class="stat-value hr-value">${vitalsData.avg_hr ? vitalsData.avg_hr + ' bpm' : 'N/A'}</div>
                      <div class="stat-label">Last ${vitalsData.length} readings</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-chart-line"></i> Compliance</h5>
                      <div class="stat-value avg-value">${vitalsData.compliance_rate ? vitalsData.compliance_rate + '%' : 'N/A'}</div>
                      <div class="stat-label">Vital recording rate</div>
                    </div>
                  </div>

                  <!-- Charts -->
                  <div class="chart-container">
                    <div class="chart-box">
                      <h4>Blood Pressure Trend</h4>
                      <div class="chart-wrapper">
                        <canvas id="bpChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Blood Sugar Trend</h4>
                      <div class="chart-wrapper">
                        <canvas id="bsChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Heart Rate Trend</h4>
                      <div class="chart-wrapper">
                        <canvas id="hrChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Vital Signs Overview</h4>
                      <div class="chart-wrapper">
                        <canvas id="vitalOverviewChart"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Recent Vitals Table -->
                  <div class="recent-data">
                    <h4><i class="fas fa-history"></i> Recent Vital Entries</h4>
                    <table class="data-table">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Blood Pressure</th>
                          <th>Blood Sugar</th>
                          <th>Heart Rate</th>
                          <th>Mood</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody id="vitalsTableBody">
                        ${vitalsData.slice(0, 10).map(vital => `
                          <tr>
                            <td>${vital.date}</td>
                            <td>${vital.blood_pressure_systolic}/${vital.blood_pressure_diastolic}</td>
                            <td>${vital.blood_sugar || 'N/A'}</td>
                            <td>${vital.heart_rate}</td>
                            <td class="mood-${vital.mood}">${vital.mood}</td>
                            <td>
                              ${getVitalStatusBadge(vital.blood_pressure_systolic, vital.blood_pressure_diastolic, vital.heart_rate)}
                            </td>
                          </tr>
                        `).join('')}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Cognitive Games Tab -->
              <div id="cognitive" class="tab-content">
                <div class="monitoring-dashboard">
                  <div class="dashboard-header">
                    <h3><i class="fas fa-gamepad"></i> Cognitive Games Monitoring</h3>
                    <div>
                      <select id="gamesTimeRange" class="modal-input" style="width: auto; padding: 5px 10px;">
                        <option value="7">Last 7 Days</option>
                        <option value="14">Last 14 Days</option>
                        <option value="30">Last 30 Days</option>
                      </select>
                    </div>
                  </div>

                  <!-- Game Stats Cards -->
                  <div class="stats-grid">
                    <div class="stat-card">
                      <h5><i class="fas fa-gamepad"></i> Total Sessions</h5>
                      <div class="stat-value game-value">${gamesData.total_sessions || 0}</div>
                      <div class="stat-label">Completed games</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-clock"></i> Avg Duration</h5>
                      <div class="stat-value duration-value">${gamesData.avg_duration ? Math.round(gamesData.avg_duration) + ' mins' : 'N/A'}</div>
                      <div class="stat-label">Per session</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-calendar-day"></i> Today's Sessions</h5>
                      <div class="stat-value game-value">${gamesData.today_count || 0}</div>
                      <div class="stat-label">Games played</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-trophy"></i> Favorite Game</h5>
                      <div class="stat-value avg-value">${gamesData.favorite_game || 'N/A'}</div>
                      <div class="stat-label">Most played</div>
                    </div>
                  </div>

                  <!-- Charts -->
                  <div class="chart-container">
                    <div class="chart-box">
                      <h4>Daily Game Sessions</h4>
                      <div class="chart-wrapper">
                        <canvas id="gameSessionsChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Game Duration Trend</h4>
                      <div class="chart-wrapper">
                        <canvas id="gameDurationChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Game Distribution</h4>
                      <div class="chart-wrapper">
                        <canvas id="gameDistributionChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Session Times</h4>
                      <div class="chart-wrapper">
                        <canvas id="gameTimesChart"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Recent Games Table -->
                  <div class="recent-data">
                    <h4><i class="fas fa-history"></i> Recent Game Sessions</h4>
                    <table class="data-table">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Game</th>
                          <th>Start Time</th>
                          <th>Duration</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody id="gamesTableBody">
                        ${gamesData.recent_sessions.map(game => `
                          <tr>
                            <td>${game.date}</td>
                            <td>${game.game_name}</td>
                            <td>${game.start_time}</td>
                            <td>${game.duration} mins</td>
                            <td>
                              <span class="badge ${game.duration > 15 ? 'badge-success' : 'badge-warning'}">
                                ${game.duration > 15 ? 'Good' : 'Short'}
                              </span>
                            </td>
                          </tr>
                        `).join('')}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Mood Tracking Tab -->
              <div id="mood" class="tab-content">
                <div class="monitoring-dashboard">
                  <div class="dashboard-header">
                    <h3><i class="fas fa-smile"></i> Mood Tracking</h3>
                    <div>
                      <select id="moodTimeRange" class="modal-input" style="width: auto; padding: 5px 10px;">
                        <option value="7">Last 7 Days</option>
                        <option value="14">Last 14 Days</option>
                        <option value="30">Last 30 Days</option>
                      </select>
                    </div>
                  </div>

                  <!-- Mood Stats Cards -->
                  <div class="stats-grid">
                    <div class="stat-card">
                      <h5><i class="fas fa-smile"></i> Current Mood</h5>
                      <div class="stat-value mood-${data.mood_status || 'Neutral'}">${data.mood_status || 'N/A'}</div>
                      <div class="stat-label">Today's mood</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-chart-pie"></i> Mood Distribution</h5>
                      <div class="stat-value mood-value">${moodData.most_common_mood || 'N/A'}</div>
                      <div class="stat-label">Most frequent</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-percentage"></i> Positive Days</h5>
                      <div class="stat-value avg-value">${moodData.positive_percentage ? moodData.positive_percentage + '%' : 'N/A'}</div>
                      <div class="stat-label">Happy/Neutral days</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-calendar-check"></i> Tracking Rate</h5>
                      <div class="stat-value avg-value">${moodData.tracking_rate ? moodData.tracking_rate + '%' : 'N/A'}</div>
                      <div class="stat-label">Mood recorded</div>
                    </div>
                  </div>

                  <!-- Charts -->
                  <div class="chart-container">
                    <div class="chart-box">
                      <h4>Mood Trend</h4>
                      <div class="chart-wrapper">
                        <canvas id="moodTrendChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Mood Distribution</h4>
                      <div class="chart-wrapper">
                        <canvas id="moodDistributionChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Mood by Day of Week</h4>
                      <div class="chart-wrapper">
                        <canvas id="moodDayChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Mood & Game Correlation</h4>
                      <div class="chart-wrapper">
                        <canvas id="moodGameChart"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Recent Mood Table -->
                  <div class="recent-data">
                    <h4><i class="fas fa-history"></i> Recent Mood Entries</h4>
                    <table class="data-table">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Mood</th>
                          <th>Blood Pressure</th>
                          <th>Heart Rate</th>
                          <th>Notes</th>
                        </tr>
                      </thead>
                      <tbody id="moodTableBody">
                        ${moodData.recent_entries.map(entry => `
                          <tr>
                            <td>${entry.date}</td>
                            <td class="mood-${entry.mood}">${entry.mood}</td>
                            <td>${entry.blood_pressure_systolic}/${entry.blood_pressure_diastolic}</td>
                            <td>${entry.heart_rate} bpm</td>
                            <td>${entry.feelings ? entry.feelings.substring(0, 30) + (entry.feelings.length > 30 ? '...' : '') : 'N/A'}</td>
                          </tr>
                        `).join('')}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Combined View Tab -->
              <div id="combined" class="tab-content">
                <div class="monitoring-dashboard">
                  <div class="dashboard-header">
                    <h3><i class="fas fa-chart-line"></i> Combined Metrics View</h3>
                    <div>
                      <select id="combinedTimeRange" class="modal-input" style="width: auto; padding: 5px 10px;">
                        <option value="7">Last 7 Days</option>
                        <option value="14">Last 14 Days</option>
                        <option value="30">Last 30 Days</option>
                      </select>
                    </div>
                  </div>

                  <!-- Combined Stats -->
                  <div class="stats-grid">
                    <div class="stat-card">
                      <h5><i class="fas fa-heartbeat"></i> Vital Compliance</h5>
                      <div class="stat-value avg-value">${vitalsData.compliance_rate ? vitalsData.compliance_rate + '%' : 'N/A'}</div>
                      <div class="stat-label">Recording rate</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-gamepad"></i> Game Activity</h5>
                      <div class="stat-value game-value">${gamesData.avg_daily_sessions ? gamesData.avg_daily_sessions + '/day' : 'N/A'}</div>
                      <div class="stat-label">Avg sessions</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-smile"></i> Positive Mood</h5>
                      <div class="stat-value avg-value">${moodData.positive_percentage ? moodData.positive_percentage + '%' : 'N/A'}</div>
                      <div class="stat-label">Happy/Neutral days</div>
                    </div>
                    <div class="stat-card">
                      <h5><i class="fas fa-chart-bar"></i> Overall Progress</h5>
                      <div class="stat-value avg-value">${calculateOverallProgress(vitalsData, gamesData, moodData)}</div>
                      <div class="stat-label">Engagement score</div>
                    </div>
                  </div>

                  <!-- Combined Chart -->
                  <div class="combined-chart-container">
                    <div class="chart-box" style="width: 100%;">
                      <h4>Integrated Health Metrics</h4>
                      <div class="chart-wrapper" style="height: 400px;">
                        <canvas id="combinedMetricsChart"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Correlation Analysis -->
                  <div class="chart-container">
                    <div class="chart-box">
                      <h4>Mood & Game Duration</h4>
                      <div class="chart-wrapper">
                        <canvas id="moodDurationChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>BP & Game Activity</h4>
                      <div class="chart-wrapper">
                        <canvas id="bpGameChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>HR & Mood Correlation</h4>
                      <div class="chart-wrapper">
                        <canvas id="hrMoodChart"></canvas>
                      </div>
                    </div>
                    <div class="chart-box">
                      <h4>Daily Activity Timeline</h4>
                      <div class="chart-wrapper">
                        <canvas id="dailyTimelineChart"></canvas>
                      </div>
                    </div>
                  </div>

                  <!-- Key Metrics Table -->
                  <div class="recent-data">
                    <h4><i class="fas fa-table"></i> Daily Key Metrics</h4>
                    <table class="data-table">
                      <thead>
                        <tr>
                          <th>Date</th>
                          <th>Mood</th>
                          <th>BP</th>
                          <th>Games</th>
                          <th>Duration</th>
                          <th>Notes</th>
                        </tr>
                      </thead>
                      <tbody id="combinedTableBody">
                        ${generateCombinedTableRows(vitalsData, gamesData, moodData)}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;

        // Add event listeners for tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            // Remove active class from all buttons and content
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
          });
        });

        // Initialize charts after DOM is updated
        setTimeout(() => {
          try {
            createVitalCharts(vitalsData);
            createGameCharts(gamesData);
            createMoodCharts(moodData);
            createCombinedCharts(vitalsData, gamesData, moodData);
          } catch (chartError) {
            console.error("Error creating charts:", chartError);
            modalContent.innerHTML += `<p class="error">Error loading charts. Please try again.</p>`;
          }
        }, 100);

      } catch (error) {
        console.error("Error fetching monitoring data:", error);
        modalContent.innerHTML = `
          <p class="error">Error loading monitoring data: ${error.message}</p>
          <button onclick="window.location.reload()">Try Again</button>
        `;
      }
    });
  });

  // Helper functions
  function getVitalStatusBadge(systolic, diastolic, hr) {
    if (systolic > 140 || diastolic > 90) {
      return '<span class="badge badge-danger">High BP</span>';
    } else if (systolic < 90 || diastolic < 60) {
      return '<span class="badge badge-warning">Low BP</span>';
    } else if (hr > 100) {
      return '<span class="badge badge-warning">High HR</span>';
    } else if (hr < 60) {
      return '<span class="badge badge-warning">Low HR</span>';
    } else {
      return '<span class="badge badge-success">Normal</span>';
    }
  }

  function calculateOverallProgress(vitals, games, mood) {
    // Simple scoring algorithm - can be enhanced
    const vitalScore = vitals.compliance_rate ? vitals.compliance_rate / 100 * 40 : 0;
    const gameScore = games.avg_daily_sessions ? Math.min(games.avg_daily_sessions / 3 * 30, 30) : 0;
    const moodScore = mood.positive_percentage ? mood.positive_percentage / 100 * 30 : 0;
    return Math.round(vitalScore + gameScore + moodScore) + '/100';
  }

  function generateCombinedTableRows(vitals, games, mood) {
    // This would need actual implementation combining data from all sources
    // For demo, we'll just show vitals with mood if available
    return vitals.slice(0, 10).map(vital => {
      const moodEntry = mood.recent_entries.find(m => m.date === vital.date);
      const gameEntry = games.recent_sessions.find(g => g.date === vital.date);
      
      return `
        <tr>
          <td>${vital.date}</td>
          <td class="mood-${moodEntry ? moodEntry.mood : 'Neutral'}">${moodEntry ? moodEntry.mood : 'N/A'}</td>
          <td>${vital.blood_pressure_systolic}/${vital.blood_pressure_diastolic}</td>
          <td>${gameEntry ? gameEntry.count : '0'}</td>
          <td>${gameEntry ? gameEntry.total_duration + ' mins' : 'N/A'}</td>
          <td>${moodEntry && moodEntry.feelings ? moodEntry.feelings.substring(0, 20) + '...' : ''}</td>
        </tr>
      `;
    }).join('');
  }

  function destroyCharts() {
    [vitalChart, gameChart, moodChart, combinedChart].forEach(chart => {
      if (chart) {
        try {
          chart.destroy();
        } catch (e) {
          console.error("Error destroying chart:", e);
        }
      }
    });
  }

  function createVitalCharts(vitalsData) {
    const dates = vitalsData.map(v => v.date);
    
    // BP Chart
    const bpCtx = document.getElementById('bpChart').getContext('2d');
    vitalChart = new Chart(bpCtx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [
          {
            label: 'Systolic BP',
            data: vitalsData.map(v => v.blood_pressure_systolic),
            borderColor: '#e63946',
            backgroundColor: 'rgba(230, 57, 70, 0.1)',
            tension: 0.3
          },
          {
            label: 'Diastolic BP',
            data: vitalsData.map(v => v.blood_pressure_diastolic),
            borderColor: '#9e2a2b',
            backgroundColor: 'rgba(158, 42, 43, 0.1)',
            tension: 0.3
          }
        ]
      },
      options: chartOptions('mmHg')
    });
    
    // BS Chart
    const bsCtx = document.getElementById('bsChart').getContext('2d');
    new Chart(bsCtx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [{
          label: 'Blood Sugar',
          data: vitalsData.map(v => v.blood_sugar),
          borderColor: '#457b9d',
          backgroundColor: 'rgba(69, 123, 157, 0.1)',
          tension: 0.3
        }]
      },
      options: chartOptions('mg/dL')
    });
    
    // HR Chart
    const hrCtx = document.getElementById('hrChart').getContext('2d');
    new Chart(hrCtx, {
      type: 'line',
      data: {
        labels: dates,
        datasets: [{
          label: 'Heart Rate',
          data: vitalsData.map(v => v.heart_rate),
          borderColor: '#2a9d8f',
          backgroundColor: 'rgba(42, 157, 143, 0.1)',
          tension: 0.3
        }]
      },
      options: chartOptions('bpm')
    });
    
    // Vital Overview Chart
    const vitalOverviewCtx = document.getElementById('vitalOverviewChart').getContext('2d');
    new Chart(vitalOverviewCtx, {
      type: 'bar',
      data: {
        labels: ['Systolic BP', 'Diastolic BP', 'Blood Sugar', 'Heart Rate'],
        datasets: [{
          label: 'Average Values',
          data: [
            calculateAverage(vitalsData.map(v => v.blood_pressure_systolic)),
            calculateAverage(vitalsData.map(v => v.blood_pressure_diastolic)),
            calculateAverage(vitalsData.map(v => v.blood_sugar)),
            calculateAverage(vitalsData.map(v => v.heart_rate))
          ],
          backgroundColor: [
            'rgba(230, 57, 70, 0.7)',
            'rgba(158, 42, 43, 0.7)',
            'rgba(69, 123, 157, 0.7)',
            'rgba(42, 157, 143, 0.7)'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: false
          }
        }
      }
    });
  }

  function createGameCharts(gamesData) {
    // Game Sessions Chart
    const gameSessionsCtx = document.getElementById('gameSessionsChart').getContext('2d');
    gameChart = new Chart(gameSessionsCtx, {
      type: 'bar',
      data: {
        labels: gamesData.daily_counts.map(g => g.date),
        datasets: [{
          label: 'Game Sessions',
          data: gamesData.daily_counts.map(g => g.count),
          backgroundColor: 'rgba(244, 162, 97, 0.7)'
        }]
      },
      options: chartOptions('Sessions')
    });
    
    // Game Duration Chart
    const gameDurationCtx = document.getElementById('gameDurationChart').getContext('2d');
    new Chart(gameDurationCtx, {
      type: 'line',
      data: {
        labels: gamesData.daily_duration.map(g => g.date),
        datasets: [{
          label: 'Avg Duration (mins)',
          data: gamesData.daily_duration.map(g => g.avg_duration),
          borderColor: '#6a4c93',
          backgroundColor: 'rgba(106, 76, 147, 0.1)',
          tension: 0.3
        }]
      },
      options: chartOptions('Minutes')
    });
    
    // Game Distribution Chart
    const gameDistCtx = document.getElementById('gameDistributionChart').getContext('2d');
    new Chart(gameDistCtx, {
      type: 'doughnut',
      data: {
        labels: gamesData.game_distribution.map(g => g.game_name),
        datasets: [{
          data: gamesData.game_distribution.map(g => g.count),
          backgroundColor: [
            '#e63946', '#457b9d', '#2a9d8f', '#f4a261', '#9c89b8', '#6a4c93'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
    
    // Game Times Chart
    const gameTimesCtx = document.getElementById('gameTimesChart').getContext('2d');
    new Chart(gameTimesCtx, {
      type: 'bar',
      data: {
        labels: ['Morning', 'Afternoon', 'Evening', 'Night'],
        datasets: [{
          label: 'Session Count',
          data: [
            gamesData.time_distribution.morning,
            gamesData.time_distribution.afternoon,
            gamesData.time_distribution.evening,
            gamesData.time_distribution.night
          ],
          backgroundColor: 'rgba(244, 162, 97, 0.7)'
        }]
      },
      options: chartOptions('Sessions')
    });
  }

  function createMoodCharts(moodData) {
    const moodMap = {'Happy':5,'Neutral':4,'Tired':3,'Anxious':2,'Sad':1,'Angry':0};
    
    // Mood Trend Chart
    const moodTrendCtx = document.getElementById('moodTrendChart').getContext('2d');
    moodChart = new Chart(moodTrendCtx, {
      type: 'line',
      data: {
        labels: moodData.mood_trend.map(m => m.date),
        datasets: [{
          label: 'Mood',
          data: moodData.mood_trend.map(m => moodMap[m.mood]),
          borderColor: '#9c89b8',
          backgroundColor: 'rgba(156, 137, 184, 0.1)',
          tension: 0.3,
          pointBackgroundColor: moodData.mood_trend.map(m => getMoodColor(m.mood))
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            min: 0,
            max: 5,
            ticks: {
              callback: function(value) {
                const moods = ['Angry','Sad','Anxious','Tired','Neutral','Happy'];
                return moods[value] || '';
              }
            }
          }
        }
      }
    });
    
    // Mood Distribution Chart
    const moodDistCtx = document.getElementById('moodDistributionChart').getContext('2d');
    new Chart(moodDistCtx, {
      type: 'pie',
      data: {
        labels: moodData.mood_counts.map(m => m.mood),
        datasets: [{
          data: moodData.mood_counts.map(m => m.count),
          backgroundColor: moodData.mood_counts.map(m => getMoodColor(m.mood))
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
    
    // Mood by Day Chart
    const moodDayCtx = document.getElementById('moodDayChart').getContext('2d');
    new Chart(moodDayCtx, {
      type: 'bar',
      data: {
        labels: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        datasets: moodData.mood_by_day.datasets
      },
      options: chartOptions('Mood Score')
    });
    
    // Mood & Game Correlation Chart
    const moodGameCtx = document.getElementById('moodGameChart').getContext('2d');
    new Chart(moodGameCtx, {
      type: 'scatter',
      data: {
        datasets: [{
          label: 'Mood vs Game Duration',
          data: moodData.mood_game_correlation,
          backgroundColor: '#9c89b8'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: {
            title: { display: true, text: 'Game Duration (mins)' }
          },
          y: {
            title: { display: true, text: 'Mood' },
            min: 0,
            max: 5,
            ticks: {
              callback: function(value) {
                const moods = ['Angry','Sad','Anxious','Tired','Neutral','Happy'];
                return moods[value] || '';
              }
            }
          }
        }
      }
    });
  }

  function createCombinedCharts(vitalsData, gamesData, moodData) {
    const dates = vitalsData.map(v => v.date);
    const moodMap = {'Happy':5,'Neutral':4,'Tired':3,'Anxious':2,'Sad':1,'Angry':0};
    
    // Combined Metrics Chart
    const combinedCtx = document.getElementById('combinedMetricsChart').getContext('2d');
    combinedChart = new Chart(combinedCtx, {
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
            label: 'Game Sessions',
            data: gamesData.daily_counts.map(g => g.count),
            borderColor: '#f4a261',
            backgroundColor: 'rgba(244, 162, 97, 0.1)',
            yAxisID: 'y2',
            tension: 0.3
          },
          {
            label: 'Mood',
            data: moodData.mood_trend.map(m => moodMap[m.mood] || 3),
            borderColor: '#9c89b8',
            backgroundColor: 'rgba(156, 137, 184, 0.1)',
            yAxisID: 'y3',
            tension: 0.3,
            pointBackgroundColor: moodData.mood_trend.map(m => getMoodColor(m.mood))
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
            title: { display: true, text: 'Game Sessions' },
            min: 0,
            grid: { drawOnChartArea: false }
          },
          y3: {
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
    
    // Other combined charts would be initialized here...
  }

  function chartOptions(unit) {
    return {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: unit !== 'mmHg' && unit !== 'bpm',
          title: { display: true, text: unit }
        }
      }
    };
  }

  function calculateAverage(arr) {
    const filtered = arr.filter(val => val !== null && val !== undefined);
    return filtered.length ? filtered.reduce((a, b) => a + b, 0) / filtered.length : 0;
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