<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userData = getUserData($userId);

// Get music suggestions
$stmt = $pdo->prepare("SELECT * FROM music_library ORDER BY RAND() LIMIT 3");
$stmt->execute();
$musicSuggestions = $stmt->fetchAll();

// Quotes array
$quotes = [
    ["text" => "Write it on your heart that every day is the best in the year", "author" => "Ralph Waldo Emerson"],
    ["text" => "Every day may not be good, but there's something good in every day.", "author" => "Alice Morse Earle"],
    ["text" => "Small steps every day lead to big results.", "author" => "Anonymous"],
    ["text" => "Recovery is not a race. You don't have to feel guilty if it takes you longer than you thought it would.", "author" => "Anonymous"],
    ["text" => "You're braver than you believe, stronger than you seem, and smarter than you think.", "author" => "A.A. Milne"],
    ["text" => "Healing is an art. It takes time, it takes practice, it takes love.", "author" => "Anonymous"]
];
$randomQuote = $quotes[array_rand($quotes)];

// Get recent vitals for trends
$stmt = $pdo->prepare("SELECT * FROM daily_vitals WHERE user_id = ? ORDER BY date DESC LIMIT 5");
$stmt->execute([$userId]);
$recentVitals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Health - Game Statistics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            font-size: 16px; /* Base font size */
        }

        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background: white;
            min-height: 100vh;
            margin: 0;
            transition: all 0.3s ease;
            padding-top: 140px; /* Account for fixed header */
        }

        /* Header Styles */
        .header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 24px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            width: 40px;
            height: 40px;
            background: #2d5a4c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.125rem; /* 18px */
        }

        .header h1 {
            font-size: 1.5rem; /* 24px */
            font-weight: bold;
            color: #2d5a4c;
            transition: all 0.3s ease;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
            color: #6b7280;
        }

        .phone-info {
            text-align: right;
        }

        .phone-number {
            font-weight: 500;
            font-size: 0.875rem; /* 14px */
            color: #2d5a4c;
        }

        .phone-subtitle {
            font-size: 0.75rem; /* 12px */
            color: #9ca3af;
        }

        /* Navigation Styles */
        .navigation {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 24px;
            position: fixed;
            top: 72px; /* Header height */
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
        }

        .nav-links {
            display: flex;
            gap: 40px;
        }

        .nav-links a {
            color: #374151;
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem; /* 16px */
            padding: 16px 0;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #2d5a4c;
            border-bottom-color: #2d5a4c;
        }

        .main-container {
            display: flex;
            flex-direction: row;
            max-width: 1800px;
            margin: 0 auto;
            min-height: calc(100vh - 140px);
            position: relative;
        }

        .content-area {
            flex: 1;
            padding: 32px;
            position: relative;
            margin-left: 240px; /* Sidebar width */
            transition: margin-left 0.3s ease;
        }

        .content-area.full-width {
            margin-left: 0;
            width: 100%;
        }

        /* Welcome Section */
        .welcome-section {
            text-align: right;
            margin-right: 100px;
            margin-bottom: 40px;
            padding: 24px 0;
        }

        .welcome-title {
            font-size: 2rem; /* 32px */
            color: #2d5a4c;
            font-weight: 300;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .welcome-subtitle {
            font-size: 1rem; /* 16px */
            color: #6b7280;
        }

        /* Card Styles */
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            margin-bottom: 24px;
        }

        .card-header {
            padding: 20px 20px 0;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 1rem; /* 16px */
            font-weight: 600;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .card-content {
            padding: 0 20px 20px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: rgba(45, 90, 76, 0.05);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(45, 90, 76, 0.1);
        }

        .stat-icon {
            font-size: 1.5rem; /* 24px */
            color: #2d5a4c;
            margin-bottom: 12px;
        }

        .stat-title {
            font-size: 0.875rem; /* 14px */
            color: #2d5a4c;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.5rem; /* 24px */
            font-weight: bold;
            color: #2d5a4c;
        }

        /* Charts */
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .chart-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }

        .chart-card h3 {
            font-size: 1rem; /* 16px */
            color: #2d5a4c;
            margin: 0 0 16px 0;
            text-align: center;
        }

        .chart-wrapper {
            position: relative;
            height: 250px;
            width: 100%;
        }

        /* Recent Games */
        .recent-games {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
        }

        .recent-games h3 {
            font-size: 1rem; /* 16px */
            color: #2d5a4c;
            margin: 0 0 16px 0;
        }

        .game-session {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            margin-bottom: 8px;
            background: rgba(45, 90, 76, 0.03);
            border-radius: 8px;
            font-size: 0.875rem; /* 14px */
            transition: all 0.3s ease;
        }

        .game-session:hover {
            background: rgba(45, 90, 76, 0.08);
        }

        .game-name {
            font-weight: 600;
            color: #2d5a4c;
        }

        .game-time {
            color: #6b7280;
            font-size: 0.75rem; /* 12px */
        }

        .game-duration {
            background: #2d5a4c;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem; /* 12px */
        }

        /* Loading and Error States */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 20px;
            color: #2d5a4c;
            font-size: 1rem; /* 16px */
        }

        .error-message {
            display: none;
            background: rgba(255, 0, 0, 0.05);
            color: #d32f2f;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.875rem; /* 14px */
            align-items: center;
            gap: 10px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 240px;
            background: #2d5a4c;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 140px; /* Header + navigation height */
            left: 0;
            bottom: 0;
            transition: all 0.3s ease;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
            opacity: 0;
        }

        .sidebar-header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-avatar {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }

        .profile-avatar i {
            color: white;
            font-size: 1.5rem; /* 24px */
        }

        .welcome-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem; /* 14px */
        }

        .user-name {
            color: white;
            font-weight: 600;
            font-size: 1rem; /* 16px */
            margin-top: 4px;
        }

        .sidebar-menu {
            list-style: none;
            margin-bottom: 20px;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 0.875rem; /* 14px */
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu li.active a {
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
            font-size: 0.875rem; /* 14px */
        }

        .logout-section {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 15px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 0.875rem; /* 14px */
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ff6b6b;
        }

        /* Floating Chat Button */
        .floating-chat-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2d5a4c, #16a34a);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem; /* 24px */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .floating-chat-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
        }

        /* Chat Modal */
        .chat-modal {
            display: none;
            position: fixed;
            bottom: 80px;
            right: 30px;
            width: 850px;
            height: 600px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            overflow: hidden;
            flex-direction: column;
            border: 2px solid #2d5a4c;
        }

        .chat-modal iframe {
            border: none;
            width: 100%;
            height: 100%;
        }

        .close-chat {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1002;
            font-size: 0.875rem; /* 14px */
        }

        /* Dropdown Menu Styles */
        .sidebar-menu .has-dropdown {
            position: relative;
        }

        .sidebar-menu .dropdown-menu {
            display: none;
            list-style: none;
            padding-left: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-left: 2px solid rgba(255, 255, 255, 0.2);
            margin-top: 4px;
        }

        .sidebar-menu .has-dropdown:hover .dropdown-menu {
            display: block;
        }

        .sidebar-menu .dropdown-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-menu .dropdown-arrow {
            font-size: 0.75rem; /* 12px */
            transition: transform 0.3s ease;
        }

        .sidebar-menu .has-dropdown:hover .dropdown-arrow {
            transform: rotate(180deg);
        }

        .sidebar-menu .dropdown-menu a {
            padding: 10px 16px;
            font-size: 0.8125rem; /* 13px */
        }

        .sidebar-menu .dropdown-menu a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Floating Accessibility Button */
        .floating-accessibility-btn {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem; /* 24px */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .floating-accessibility-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
        }

        .accessibility-menu {
            position: fixed;
            bottom: 170px;
            right: 30px;
            width: 200px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 10px;
            z-index: 1001;
        }

        .accessibility-menu.hidden {
            display: none;
        }

        .accessibility-option {
            display: block;
            width: 100%;
            padding: 8px 10px;
            margin: 5px 0;
            background: #f8f9fa;
            border: none;
            border-radius: 5px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s;
        }

        .accessibility-option:hover {
            background: #e9ecef;
        }

        .accessibility-option i {
            margin-right: 8px;
            width: 20px;
            text-align: center;
        }

        /* Dark Mode Styles */
        .dark-mode {
            background: #121212;
            color: #e0e0e0;
        }

        .dark-mode .header,
        .dark-mode .navigation,
        .dark-mode .card,
        .dark-mode .sidebar {
            background: #1e1e1e !important;
            color: #e0e0e0 !important;
            border-color: #333 !important;
        }

        .dark-mode .header h1,
        .dark-mode .nav-links a,
        .dark-mode .card-title,
        .dark-mode .welcome-title,
        .dark-mode .welcome-subtitle {
            color: #e0e0e0 !important;
        }

        .dark-mode .sidebar {
            background: #1a1a1a !important;
        }

        .dark-mode .stat-card {
            background: rgba(45, 90, 76, 0.1) !important;
        }

        .dark-mode .game-session {
            background: rgba(45, 90, 76, 0.1) !important;
        }

        .dark-mode .chart-card,
        .dark-mode .recent-games {
            background: #252525 !important;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2) !important;
        }

        /* For WebKit browsers (Chrome, Safari) */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(45, 90, 76, 0.3);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(45, 90, 76, 0.5);
        }

        /* For Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(45, 90, 76, 0.3) rgba(0, 0, 0, 0.05);
        }

        .hide-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;  /* Chrome, Safari, Opera */
        }

        #menuToggle i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            #menuToggle {
                padding: 12px;
                background: rgba(45, 90, 76, 0.1);
                border-radius: 50%;
                width: 44px;
                height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            #menuToggle:hover {
                background: rgba(45, 90, 76, 0.2);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            body {
                padding-top: 120px;
            }
            
            .navigation {
                top: 60px;
            }
            
            .sidebar {
                top: 120px;
                width: 100%;
                margin: 0;
                border-radius: 0;
                transform: translateX(-100%);
                opacity: 0;
            }
            
            .sidebar.show {
                transform: translateX(0);
                opacity: 1;
            }
            
            .content-area {
                margin-left: 0;
                padding: 16px;
            }

            .floating-accessibility-btn {
                bottom: 170px;
            }

            .accessibility-menu {
                bottom: 240px;
            }
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 16px;
            }

            .sidebar {
                top: 120px;
            }

            .chat-modal {
                width: 100%;
                height: 80vh;
                bottom: 0;
                right: 0;
                border-radius: 16px 16px 0 0;
            }

            .floating-accessibility-btn {
                bottom: 170px;
            }

            .accessibility-menu {
                bottom: 240px;
                right: 10px;
                width: 180px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .charts-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="header-left">
                <div class="logo">N</div>
                <h1>NeuroAid</h1>
            </div>
            <div class="header-right">
                <div class="phone-info">
                    <i class="fas fa-phone"></i>
                    <span class="phone-number">+1 (800) 777-NEUR (6387)</span>
                    <small>Call us for any question</small>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="navigation">
        <div class="nav-content">
            <div class="nav-links">
                <a href="menu.php" id="menuToggle">
                    <i class="fas fa-bars"></i> Menu
                </a>
                <a href="user_dashboard.php">Home</a>
                <a href="health.php">Health</a>
                <a href="chat1.php">Chat</a>
                <a href="accessibility.php">Accessibility</a>
                <a href="reminder.php">Reminder</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="welcome-text">Welcome back,</div>
                <div class="user-name"><?php echo htmlspecialchars($userData['full_name']); ?></div>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="user_dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="health.php">
                        <i class="fas fa-heartbeat"></i>
                        <span>Health Management</span>
                    </a>
                </li>
                <li class="has-dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-bell"></i>
                        <span>Reminders & Schedule</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="reminder.php"><i class="fas fa-bell"></i> Reminders</a></li>
                        <li><a href="schedule_list.php"><i class="fas fa-calendar"></i> Schedule</a></li>
                        <li><a href="alarm.php"><i class="fas fa-clock"></i> Alarm</a></li>
                    </ul>
                </li>
                <li class="has-dropdown active">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-brain"></i>
                        <span>Cognitive Training</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="game_dashboard.php"><i class="fas fa-tachometer-alt"></i> Games Overview</a></li>
                        <li><a href="games.php"><i class="fas fa-gamepad"></i> Games</a></li>
                    </ul>
                </li>
                <li>
                    <a href="journal.php">
                        <i class="fas fa-book"></i>
                        <span>Personal Journal</span>
                    </a>
                </li>
                <li class="has-dropdown">
                    <a href="#" class="dropdown-toggle">
                        <i class="fas fa-user-cog"></i>
                        <span>Profile Account</span>
                        <i class="fas fa-chevron-down dropdown-arrow"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php"><i class="fas fa-bell"></i> Profile</a></li>
                        <li><a href="user_details.php"><i class="fas fa-calendar"></i> Patients</a></li>
                        <li><a href="caregiver_details.php"><i class="fas fa-clock"></i> Caregiver</a></li>
                    </ul>
                </li>
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
            
            <div class="logout-section">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log Out</span>
                </a>
            </div>
        </aside>

        <!-- Content Area -->
        <main class="content-area">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2 class="welcome-title">My Game Statistics</h2>
                <p class="welcome-subtitle">View your cognitive rehabilitation progress</p>
            </div>

            <!-- Stats Dashboard -->
            <div class="card">
                <div class="card-content">
                    <div id="loadingIndicator" class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Loading your statistics...</span>
                    </div>

                    <div id="errorContainer" class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="errorText"></span>
                    </div>

                    <div id="dashboardContent">
                        <div class="stats-grid">
                            <!-- Stat cards will be populated by JavaScript -->
                        </div>

                        <div class="charts-container">
                            <div class="chart-card">
                                <h3>Most Played Games</h3>
                                <div class="chart-wrapper">
                                    <canvas id="mostPlayedChart"></canvas>
                                </div>
                            </div>
                            <div class="chart-card">
                                <h3>Session Duration</h3>
                                <div class="chart-wrapper">
                                    <canvas id="durationChart"></canvas>
                                </div>
                            </div>
                            <div class="chart-card">
                                <h3>Activity Timeline</h3>
                                <div class="chart-wrapper">
                                    <canvas id="timelineChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="recent-games">
                            <h3>Recent Sessions</h3>
                            <div id="recentGamesList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Floating Chat Button -->
    <div class="floating-chat-btn" id="chatButton">
        <i class="fas fa-comment-dots"></i>
    </div>

    <!-- Chat Modal -->
    <div class="chat-modal" id="chatModal">
        <button class="close-chat" id="closeChat"><i class="fas fa-times"></i></button>
        <iframe src="chat.php" frameborder="0"></iframe>
    </div>

    <!-- Floating Accessibility Button -->
    <div class="floating-accessibility-btn" id="accessibilityButton">
        <i class="fas fa-universal-access"></i>
    </div>

    <!-- Accessibility Menu -->
    <div class="accessibility-menu hidden" id="accessibilityMenu">
        <button class="accessibility-option" data-action="increase-font">
            <i class="fas fa-text-height"></i> Increase Font
        </button>
        <button class="accessibility-option" data-action="decrease-font">
            <i class="fas fa-text-width"></i> Decrease Font
        </button>
        <button class="accessibility-option" data-action="increase-gui">
            <i class="fas fa-expand"></i> Larger UI
        </button>
        <button class="accessibility-option" data-action="decrease-gui">
            <i class="fas fa-compress"></i> Smaller UI
        </button>
        <button class="accessibility-option" data-action="toggle-dark">
            <i class="fas fa-moon"></i> Dark Mode
        </button>
        <button class="accessibility-option" data-action="reset-default">
            <i class="fas fa-undo"></i> Reset Default
        </button>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Menu toggle functionality
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.querySelector('.sidebar');
            const contentArea = document.querySelector('.content-area');
            
            if (menuToggle && sidebar && contentArea) {
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('hidden');
                    contentArea.classList.toggle('full-width');
                    
                    // Save sidebar state to localStorage
                    const isHidden = sidebar.classList.contains('hidden');
                    localStorage.setItem('sidebarHidden', isHidden);
                });
                
                // Check saved state on load
                const sidebarHidden = localStorage.getItem('sidebarHidden') === 'true';
                if (sidebarHidden) {
                    sidebar.classList.add('hidden');
                    contentArea.classList.add('full-width');
                }
            }

            // Accessibility controls
            const accessibilityBtn = document.getElementById('accessibilityButton');
            const accessibilityMenu = document.getElementById('accessibilityMenu');
            
            // Toggle accessibility menu
            accessibilityBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                accessibilityMenu.classList.toggle('hidden');
            });

            // Close menu when clicking elsewhere
            document.addEventListener('click', function(e) {
                if (!accessibilityBtn.contains(e.target)) {
                    accessibilityMenu.classList.add('hidden');
                }
            });

            // Handle accessibility options
            document.addEventListener('click', function(e) {
                if (e.target.closest('.accessibility-option')) {
                    const action = e.target.closest('.accessibility-option').dataset.action;
                    handleAccessibilityAction(action);
                    accessibilityMenu.classList.add('hidden');
                }
            });

            function handleAccessibilityAction(action) {
                const root = document.documentElement;
                let currentSize = parseFloat(getComputedStyle(root).fontSize) || 16;
                let currentUIScale = parseFloat(document.body.dataset.uiScale) || 1;

                switch(action) {
                    case 'increase-font':
                        currentSize = Math.min(currentSize + 1, 22);
                        root.style.fontSize = currentSize + 'px';
                        localStorage.setItem('fontSize', currentSize);
                        break;
                    case 'decrease-font':
                        currentSize = Math.max(currentSize - 1, 12);
                        root.style.fontSize = currentSize + 'px';
                        localStorage.setItem('fontSize', currentSize);
                        break;
                    case 'increase-gui':
                        currentUIScale = Math.min(currentUIScale + 0.1, 1.5);
                        document.body.dataset.uiScale = currentUIScale;
                        document.body.style.transform = `scale(${currentUIScale})`;
                        document.body.style.transformOrigin = 'center';
                        localStorage.setItem('uiScale', currentUIScale);
                        break;
                    case 'decrease-gui':
                        currentUIScale = Math.max(currentUIScale - 0.1, 0.8);
                        document.body.dataset.uiScale = currentUIScale;
                        document.body.style.transform = `scale(${currentUIScale})`;
                        document.body.style.transformOrigin = 'center';
                        localStorage.setItem('uiScale', currentUIScale);
                        break;
                    case 'toggle-dark':
                        document.body.classList.toggle('dark-mode');
                        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
                        break;
                    case 'reset-default':
                        // Reset all accessibility settings
                        root.style.fontSize = '16px';
                        document.body.style.transform = 'none';
                        document.body.dataset.uiScale = '1';
                        document.body.classList.remove('dark-mode');
                        
                        // Clear saved preferences
                        localStorage.removeItem('fontSize');
                        localStorage.removeItem('uiScale');
                        localStorage.removeItem('darkMode');
                        
                        // Ensure fixed elements stay fixed
                        document.querySelector('.header').style.position = 'fixed';
                        document.querySelector('.navigation').style.position = 'fixed';
                        document.querySelector('.sidebar').style.position = 'fixed';
                        document.getElementById('chatButton').style.position = 'fixed';
                        document.getElementById('accessibilityButton').style.position = 'fixed';
                        break;
                }
            }

            // Check for saved preferences on load
            function loadPreferences() {
                // Dark mode
                if (localStorage.getItem('darkMode') === 'true') {
                    document.body.classList.add('dark-mode');
                }
                
                // Font size
                const savedFontSize = localStorage.getItem('fontSize');
                if (savedFontSize) {
                    document.documentElement.style.fontSize = savedFontSize + 'px';
                }
                
                // UI scale
                const savedUIScale = localStorage.getItem('uiScale');
                if (savedUIScale) {
                    document.body.dataset.uiScale = savedUIScale;
                    document.body.style.transform = `scale(${savedUIScale})`;
                    document.body.style.transformOrigin = 'center';
                }
            }
            
            loadPreferences();

            // Chat Button Functionality
            const chatButton = document.getElementById('chatButton');
            const chatModal = document.getElementById('chatModal');
            const closeChat = document.getElementById('closeChat');
            
            chatButton.addEventListener('click', function() {
                chatModal.style.display = 'flex';
                // Refresh the iframe when opened to ensure fresh content
                chatModal.querySelector('iframe').src = chatModal.querySelector('iframe').src;
            });
            
            closeChat.addEventListener('click', function() {
                chatModal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === chatModal) {
                    chatModal.style.display = 'none';
                }
            });

            // Dashboard functionality
            let gameData = [];
            let charts = {};
            const currentUserId = <?php echo $userId; ?>;
            const statCards = [
                { id: 'totalSessions', icon: 'gamepad', title: 'Total Sessions' },
                { id: 'totalPlaytime', icon: 'clock', title: 'Total Playtime' },
                { id: 'avgSession', icon: 'stopwatch', title: 'Avg Session' },
                { id: 'mostPlayed', icon: 'star', title: 'Most Played' },
                { id: 'activeSessions', icon: 'play-circle', title: 'Active' },
                { id: 'todaySessions', icon: 'calendar-day', title: 'Today' }
            ];

            // Initialize stat cards
            const statsGrid = document.querySelector('.stats-grid');
            statsGrid.innerHTML = statCards.map(card => `
                <div class="stat-card" id="${card.id}-card">
                    <div class="stat-icon"><i class="fas fa-${card.icon}"></i></div>
                    <div class="stat-title">${card.title}</div>
                    <div class="stat-value" id="${card.id}">0</div>
                </div>
            `).join('');

            async function fetchGameData() {
                try {
                    const response = await fetch(`get_game_stats.php?user_id=${currentUserId}`);
                    if (!response.ok) throw new Error(`Server error: ${response.status}`);
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);
                    return data;
                } catch (error) {
                    console.error('Fetch error:', error);
                    throw error;
                }
            }

            function showError(message) {
                const errorContainer = document.getElementById('errorContainer');
                document.getElementById('errorText').textContent = message;
                errorContainer.style.display = 'flex';
            }

            function hideError() {
                document.getElementById('errorContainer').style.display = 'none';
            }

            function showLoading() {
                document.getElementById('loadingIndicator').style.display = 'flex';
                document.getElementById('dashboardContent').style.opacity = '0.5';
            }

            function hideLoading() {
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('dashboardContent').style.opacity = '1';
            }

            function calculateStats() {
                const completedSessions = gameData.filter(s => s.duration > 0);
                const activeSessions = gameData.filter(s => !s.duration);
                const totalPlaytime = completedSessions.reduce((sum, s) => sum + parseInt(s.duration), 0);
                const avgSession = completedSessions.length ? Math.round(totalPlaytime / completedSessions.length) : 0;
                
                const gameCount = {};
                gameData.forEach(s => {
                    gameCount[s.game_name] = (gameCount[s.game_name] || 0) + 1;
                });
                
                const mostPlayed = Object.keys(gameCount).reduce((a, b) => gameCount[a] > gameCount[b] ? a : b, '-');
                const today = new Date().toISOString().split('T')[0];
                const todaySessions = gameData.filter(s => s.start_time && s.start_time.startsWith(today)).length;

                return {
                    totalSessions: gameData.length,
                    totalPlaytime: Math.round(totalPlaytime / 60),
                    avgSession,
                    mostPlayed,
                    activeSessions: activeSessions.length,
                    todaySessions,
                    gameCount,
                    completedSessions
                };
            }

            function updateStats() {
                const stats = calculateStats();
                document.getElementById('totalSessions').textContent = stats.totalSessions;
                document.getElementById('totalPlaytime').textContent = `${stats.totalPlaytime}m`;
                document.getElementById('avgSession').textContent = `${stats.avgSession}s`;
                document.getElementById('mostPlayed').textContent = stats.mostPlayed;
                document.getElementById('activeSessions').textContent = stats.activeSessions;
                document.getElementById('todaySessions').textContent = stats.todaySessions;
            }

            function createChart(chartId, type, data, options = {}) {
                if (charts[chartId]) charts[chartId].destroy();
                
                const ctx = document.getElementById(chartId).getContext('2d');
                const defaults = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                padding: 10,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                };
                
                charts[chartId] = new Chart(ctx, {
                    type,
                    data,
                    options: {...defaults, ...options}
                });
            }

            function updateCharts() {
                const stats = calculateStats();
                const games = Object.keys(stats.gameCount);
                const counts = Object.values(stats.gameCount);
                
                // Most Played Chart
                createChart('mostPlayedChart', 'doughnut', {
                    labels: games,
                    datasets: [{
                        data: counts,
                        backgroundColor: ['#2d5a4c', '#4facfe', '#f093fb', '#f5576c', '#6a11cb', '#2575fc']
                    }]
                });
                
                // Duration Chart
                const durations = games.map(game => {
                    const sessions = stats.completedSessions.filter(s => s.game_name === game);
                    return sessions.length ? Math.round(sessions.reduce((sum, s) => sum + parseInt(s.duration), 0) / sessions.length) : 0;
                });
                
                createChart('durationChart', 'bar', {
                    labels: games,
                    datasets: [{
                        label: 'Seconds',
                        data: durations,
                        backgroundColor: '#2d5a4c'
                    }]
                });
                
                // Timeline Chart
                const hours = Array(24).fill(0);
                gameData.forEach(s => {
                    if (s.start_time) hours[new Date(s.start_time).getHours()]++;
                });
                
                createChart('timelineChart', 'line', {
                    labels: hours.map((_, i) => i),
                    datasets: [{
                        label: 'Sessions',
                        data: hours,
                        borderColor: '#2d5a4c',
                        backgroundColor: 'rgba(45, 90, 76, 0.1)',
                        fill: true,
                        tension: 0.3
                    }]
                });
            }

            function updateRecentGames() {
                const recent = [...gameData]
                    .sort((a, b) => new Date(b.start_time) - new Date(a.start_time))
                    .slice(0, 5);
                
                document.getElementById('recentGamesList').innerHTML = recent.length ? 
                    recent.map(s => `
                        <div class="game-session ${!s.duration ? 'active-session' : ''}">
                            <div>
                                <div class="game-name">${s.game_name}</div>
                                <div class="game-time">${new Date(s.start_time).toLocaleString()}</div>
                            </div>
                            <div class="game-duration">${s.duration ? `${s.duration}s` : 'Active'}</div>
                        </div>
                    `).join('') : '<div style="text-align:center;padding:20px;color:#666;">No sessions found</div>';
            }

            async function loadData() {
                showLoading();
                hideError();
                
                try {
                    gameData = await fetchGameData();
                    updateStats();
                    updateCharts();
                    updateRecentGames();
                } catch (error) {
                    showError(error.message);
                } finally {
                    hideLoading();
                }
            }

            // Initialize
            loadData();
            setInterval(loadData, 30000);
            
            // Handle window resize
            window.addEventListener('resize', function() {
                Object.values(charts).forEach(chart => chart && chart.resize());
            });

            // Handle sidebar dropdowns
            const dropdownToggles = document.querySelectorAll('.sidebar-menu .dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) { // Only prevent default on mobile
                        e.preventDefault();
                    }
                    const parent = this.parentElement;
                    const dropdown = this.nextElementSibling;
                    
                    // Close all other dropdowns first
                    document.querySelectorAll('.sidebar-menu .dropdown-menu').forEach(menu => {
                        if (menu !== dropdown) {
                            menu.style.display = 'none';
                        }
                    });
                    
                    // Toggle current dropdown
                    if (dropdown.style.display === 'block') {
                        dropdown.style.display = 'none';
                    } else {
                        dropdown.style.display = 'block';
                    }
                });
            });
            
            // Close dropdowns when clicking elsewhere
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.has-dropdown')) {
                    document.querySelectorAll('.sidebar-menu .dropdown-menu').forEach(menu => {
                        menu.style.display = 'none';
                    });
                }
            });
        });
    </script>
</body>
</html>