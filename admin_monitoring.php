<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "neuroaid";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine the monitor type based on GET parameter
$type = isset($_GET['monitorType']) ? $_GET['monitorType'] : 'vital';

// Define headers and data keys for each monitor type
$tableData = [];
switch ($type) {
    case 'exercise':
        $title = "Exercise Stats";
        $table = "exercise_stats";
        $tableData = [
            'headers' => ['Patients', 'Frequent Exercise', 'Set Finish', 'Duration', 'Frequency'],
            'keys' => ['patient_name', 'exercise_name', 'set_finish', 'duration', 'frequency']
        ];
        break;
    case 'entertainment':
        $title = "Entertainment Stats";
        $table = "entertainment_stats";
        $tableData = [
            'headers' => ['Patients', 'Most Played', 'Total Hrs', 'Satisfaction', 'Frequency'],
            'keys' => ['patient_name', 'game_name', 'total_hrs', 'satisfaction', 'frequency']
        ];
        break;
    case 'activities':
        $title = "Activity Stats";
        $table = "activities_stats";
        $tableData = [
            'headers' => ['Patients', 'Activity', 'Total Hrs', 'Satisfaction', 'Effectiveness'],
            'keys' => ['patient_name', 'activity_name', 'total_hrs', 'satisfaction', 'effectiveness']
        ];
        break;
    case 'emotions':
        $title = "Emotion Activities";
        $table = "emotions_stats";
        $tableData = [
            'headers' => ['Patients', 'Emotion', 'Reason', 'Duration', 'AQW'],
            'keys' => ['patient_name', 'emotion', 'reason', 'duration', 'aqw']
        ];
        break;
    default: // vital
        $title = "Vital Stats";
        $table = "vital_stats";
        $tableData = [
            'headers' => ['Patients', 'Type of Stroke', 'BP', 'HR', 'BS'],
            'keys' => ['patient_name', 'stroke_type', 'bp', 'hr', 'bs']
        ];
        break;
}

// Prepare and execute the SQL query
$sql = "SELECT * FROM $table ORDER BY date_recorded DESC";
$result = $conn->query($sql);

$rows = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}

// Close the database connection
$conn->close();
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

        .search-wrapper input {
            width: 100%;
            padding: 8px 12px 8px 34px;
            border: 1px solid var(--gray);
            border-radius: 6px;
        }

        .search-wrapper i {
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

        .main-content-box {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 6px var(--shadow);
        }

        .main-header {
            margin-bottom: 20px;
            color: var(--primary);
            font-size: 24px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            margin: 0;
            color: var(--primary);
        }

        .search-filter-group {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input-group input {
            padding: 8px 12px 8px 34px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            width: 240px;
        }

        .search-input-group i {
            position: absolute;
            left: 10px;
            color: #777;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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

        .info-grid {
            display: grid;
            grid-template-columns: 150px 1fr;
            row-gap: 10px;
        }

        .status-select {
            padding: 10px 14px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            font-size: 16px;
            color: var(--text-dark);
            background: white;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            cursor: pointer;
        }

        .primary-btn {
            padding: 10px 16px;
            background: var(--highlight);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .primary-btn:hover {
            background-color: #268a54;
        }

        .custom-dropdown {
            position: relative;
            width: 240px;
        }

        .dropdown-toggle {
            width: 100%;
            padding: 10px 14px;
            background: white;
            border: 1px solid var(--gray);
            border-radius: 6px;
            cursor: pointer;
            text-align: left;
            position: relative;
            color: var(--text-dark);
            font-weight: 500;
            transition: 0.3s ease;
        }

        .dropdown-toggle::after {
            content: '\f107';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dark);
        }

        .dropdown-menu {
            list-style: none;
            margin: 0;
            padding: 8px 0;
            background: white;
            border: 1px solid var(--gray);
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            display: none;
            z-index: 100;
        }

        .custom-dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu li {
            padding: 10px 16px;
            cursor: pointer;
            position: relative;
            transition: 0.3s ease;
            color: var(--text-dark);
        }

        .dropdown-menu li a {
            color: inherit;
            text-decoration: none;
            display: block;
            width: 100%;
            height: 100%;
        }

        .dropdown-menu li:hover {
            background-color: var(--hover-bg);
            color: var(--hover-text);
            font-weight: 600;
        }

        .dropdown-menu li:hover::before {
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
    </style>
</head>

<body>
<div class="header">
    <div class="logo">
        <img src="image/logo.png" alt="NeuroAid Logo">
        <span>Neuro<span>Aid</span> - Admin</span>
    </div>
    <div class="datetime" id="currentDateTime"></div>
</div>

<div class="layout">
    <div class="sidebar">
        <div class="search-wrapper">
            <input type="text" placeholder="Search..." />
            <i class="fas fa-search"></i>
        </div>

        <div class="menu-section">Admin Tools</div>
        <a href="admin_dashboard.php" class="menu-btn"><i class="fas fa-home"></i>Dashboard</a>
        <a href="patient.php" class="menu-btn"><i class="fas fa-user"></i>Patient Management</a>
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
        <h2 class="main-header">Patient Monitoring</h2>

        <div class="main-content-box">

            <div style="margin-bottom: 20px;">
                <div class="custom-dropdown">
                    <button class="dropdown-toggle"><?= $title ?></button>
                    <ul class="dropdown-menu">
                        <li><a href="monitoring.php?monitorType=vital">Vital Stats</a></li>
                        <li><a href="monitoring.php?monitorType=exercise">Exercise Stats</a></li>
                        <li><a href="monitoring.php?monitorType=entertainment">Entertainment Stats</a></li>
                        <li><a href="monitoring.php?monitorType=activities">Activities</a></li>
                        <li><a href="monitoring.php?monitorType=emotions">Emotion Activities</a></li>
                    </ul>
                </div>
            </div>

            <div class="table-header">
                <h3 class="table-title"><?= $title ?></h3>

                <div class="search-filter-group">
                    <div class="search-input-group">
                        <input type="text" placeholder="Search by user email">
                        <i class="fas fa-search"></i>
                    </div>

                    <select class="status-select">
                        <option>All Statuses</option>
                        <option>Active</option>
                        <option>Inactive</option>
                    </select>

                    <button class="primary-btn">Search</button>
                </div>
            </div>

            <table>
                <thead>
                <tr>
                    <?php foreach ($tableData['headers'] as $header): ?>
                        <th><?= htmlspecialchars($header) ?></th>
                    <?php endforeach; ?>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($rows)) : ?>
                    <?php foreach ($rows as $row) : ?>
                        <tr>
                            <?php foreach ($tableData['keys'] as $key): ?>
                                <td><?= htmlspecialchars($row[$key] ?? '') ?></td>
                            <?php endforeach; ?>
                            <td><button class="primary-btn view-<?= htmlspecialchars($type) ?>">View</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="<?= count($tableData['headers']) + 1 ?>">No data available.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="patientModal" class="modal">
            <div class="modal-content">
                <button class="modal-close">&times;</button>
                <h2 class="modal-title">Patients Details</h2>

                <div id="graphSection" style="margin: 20px 0;">
                </div>

                <div class="modal-box" style="flex: 1 1 100%; margin-bottom: 24px;">
                    <label class="modal-label">Patient Name</label>
                    <input type="text" class="modal-input" value="Juan Pinoy" readonly />
                </div>

                <div class="modal-flex">
                    <div class="modal-box">
                        <h4>Basic Information</h4>
                        <div class="info-grid">
                            <span><strong>Patient ID:</strong></span><span>10100</span>
                            <span><strong>Birthday:</strong></span><span>01/01/1800</span>
                            <span><strong>Address:</strong></span><span>123 di matagpuan city</span>
                            <span><strong>Age:</strong></span><span>68</span>
                        </div>
                    </div>

                    <div class="modal-box">
                        <h4>Patient Health Details</h4>
                        <div class="info-grid">
                            <span><strong>Stroke Type:</strong></span><span>Ischemic Stroke</span>
                            <span><strong>Blood Pressure:</strong></span><span>120</span>
                            <span><strong>Heart Rate:</strong></span><span>150</span>
                            <span><strong>Blood Sugar:</strong></span><span>100</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="modal-close-btn">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById("patientModal");
    const closeBtns = document.querySelectorAll(".modal-close, .modal-close-btn");

    // Close modal on button click
    closeBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    });

    // Close modal when clicking outside
    window.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
    });

    document.addEventListener("DOMContentLoaded", () => {
        // Dynamic Date and Time update
        function updateDateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            document.getElementById('currentDateTime').textContent = now.toLocaleString('en-US', options);
        }

        updateDateTime(); // Initial call
        setInterval(updateDateTime, 1000); 

        // Event delegation for view buttons
        const tableBody = document.querySelector('table tbody');

        if (tableBody) {
            tableBody.addEventListener('click', (e) => {
                // Check if the clicked element is a button with the 'primary-btn' class
                if (e.target.classList.contains('primary-btn')) {
                    modal.style.display = "block";

                    const graphSection = document.getElementById('graphSection');
                    if (!graphSection) return;

                    // Determine which graph content to display based on the button's class (e.g., view-vital, view-exercise)
                    const buttonClasses = Array.from(e.target.classList);
                    let graphType = '';
                    if (buttonClasses.includes('view-vital')) {
                        graphType = 'Vitals';
                    } else if (buttonClasses.includes('view-exercise')) {
                        graphType = 'Exercise';
                    } else if (buttonClasses.includes('view-entertainment')) {
                        graphType = 'Entertainment';
                    } else if (buttonClasses.includes('view-activities')) {
                        graphType = 'Activities';
                    } else if (buttonClasses.includes('view-emotions')) {
                        graphType = 'Emotion';
                    }

                    if (graphType) {
                        graphSection.innerHTML = `<h4>${graphType} Graph</h4><p>${graphType} graph content goes here.</p>`;
                    }
                }
            });
        }
    });
</script>
</body>
</html>