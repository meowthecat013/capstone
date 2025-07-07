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

// Get patient statistics (using only existing columns)
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetchColumn();
$activePatients = $totalPatients; // All patients considered active since no status column
$urgentPatients = 0; // No urgency data available
$activeCaregivers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'caregiver'")->fetchColumn();

// Get patient list with latest mood
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
  <title>NeuroAid - Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
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

    .cards {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }

    .card {
      flex: 1;
      min-width: 200px;
      min-height: 220px;
      background: var(--white);
      padding: 30px 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
      border-left: 6px solid var(--primary);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
    }

    .card h1 {
      font-size: 36px;
      color: var(--primary);
      margin-bottom: 8px;
      transition: transform 0.3s ease;
    }

    .card:hover h1 {
      transform: scale(1.1);
    }

    .card p {
      font-size: 14px;
      color: #333;
    }

    .card.red { border-left-color: #dc3545; }
    .card.orange { border-left-color: #fd7e14; }
    .card.blue { border-left-color: #0dcaf0; }

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
      width: 60%;
      max-width: 800px;
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
  </style>
</head>
<body>
  <div class="header">
    <div class="logo">
      <img src="image/logo.png" alt="NeuroAid Logo">
      <span>Neuro<span>Aid</span> - Admin</span>
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
      <a href="admin_dashboard.php" class="menu-btn active"><i class="fas fa-home"></i>Dashboard</a>
      <a href="patient_management.php" class="menu-btn"><i class="fas fa-user"></i>Patient Management</a>
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
      <h2 style="margin-bottom: 20px; color: var(--primary); font-size: 24px;">Dashboard</h2>

      <div class="cards">
        <div class="card"><h1><?php echo $activePatients; ?></h1><p>Active Patients</p></div>
        <div class="card blue"><h1><?php echo $totalPatients; ?></h1><p>Registered Patients</p></div>
        <div class="card red"><h1><?php echo $urgentPatients; ?></h1><p>Urgent Need</p></div>
        <div class="card orange"><h1><?php echo $activeCaregivers; ?></h1><p>Active Care Givers</p></div>
      </div>

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
              <td><?php echo htmlspecialchars($patient['mood_status'] ?? 'N/A'); ?></td>
              <td><button class="view-btn" data-id="<?php echo $patient['id']; ?>">View</button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Patient Details Modal -->
  <div id="patientModal" class="modal">
    <div class="modal-content">
      <button class="modal-close">&times;</button>
      <h2 class="modal-title">Patient Details</h2>
      <div id="modalContent"></div>
      <div class="modal-footer">
        <button class="modal-close-btn">Close</button>
      </div>
    </div>
  </div>

<script>
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
        const response = await fetch(`get_patient_details.php?id=${patientId}`);

        const data = await response.json();
        
        if (response.ok) {
          // Populate modal with patient data
          modalContent.innerHTML = `
            <div class="modal-flex">
              <div class="modal-box" style="flex: 1 1 100%;">
                <label class="modal-label">Patient name</label>
                <input type="text" class="modal-input" value="${data.full_name}" readonly />
              </div>

              <div class="modal-box">
                <h4>Basic Information</h4>
                <p><strong>Patient ID:</strong> ${data.id}</p>
                <p><strong>Birthday:</strong> ${data.date_of_birth}</p>
                <p><strong>Address:</strong> ${data.address}</p>
                <p><strong>Age:</strong> ${data.age}</p>
              </div>

              <div class="modal-box">
                <h4>Patient Health Details</h4>
                <p><strong>Stroke Type:</strong> ${data.stroke_type}</p>
                <p><strong>Blood Pressure:</strong> ${data.blood_pressure || 'N/A'}</p>
                <p><strong>Heart rate:</strong> ${data.heart_rate || 'N/A'}</p>
                <p><strong>Blood Sugar:</strong> ${data.blood_sugar || 'N/A'}</p>
              </div>
            </div>
          `;
        } else {
          modalContent.innerHTML = `<p>Error: ${data.message || 'Failed to load patient data'}</p>`;
        }
      } catch (error) {
        modalContent.innerHTML = "<p>Error loading patient details. Please try again.</p>";
        console.error("Error fetching patient details:", error);
      }
    });
  });

  closeBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      modal.style.display = "none";
    });
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
  });
</script>

</body>
</html>