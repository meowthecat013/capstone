<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Get current user data
$userId = $_SESSION['user_id'];
$userData = getUserData($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Health - Accessibility Settings</title>
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

        /* Accessibility Page Styles */
        .accessibility-container {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: 0 auto;
        }

        .accessibility-header {
            text-align: center;
            margin-bottom: 32px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 16px;
        }

        .accessibility-header h2 {
            font-size: 1.75rem;
            color: #2d5a4c;
            margin-bottom: 8px;
        }

        .accessibility-header p {
            color: #6b7280;
        }

        .accessibility-section {
            margin-bottom: 40px;
        }

        .accessibility-section h3 {
            font-size: 1.25rem;
            color: #2d5a4c;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .accessibility-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .accessibility-option {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .accessibility-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .option-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .option-icon {
            width: 40px;
            height: 40px;
            background: #2d5a4c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 1rem;
        }

        .option-title {
            font-weight: 600;
            color: #374151;
        }

        .option-description {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 16px;
        }

        .option-controls {
            display: flex;
            gap: 10px;
        }

        .option-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            background: #2d5a4c;
            color: white;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .option-btn:hover {
            background: #1e3d34;
        }

        .option-btn.secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .option-btn.secondary:hover {
            background: #d1d5db;
        }

        .preset-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .preset-btn {
            padding: 8px 16px;
            border-radius: 6px;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .preset-btn:hover {
            background: #e5e7eb;
        }

        .preset-btn.active {
            background: #2d5a4c;
            color: white;
            border-color: #2d5a4c;
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

        .dark-mode .accessibility-container {
            background: #252525 !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2) !important;
        }

        .dark-mode .accessibility-option {
            background: #333 !important;
            border-color: #444 !important;
        }

        .dark-mode .option-title {
            color: #e0e0e0 !important;
        }

        .dark-mode .option-description {
            color: #b0b0b0 !important;
        }

        .dark-mode .preset-btn {
            background: #444 !important;
            border-color: #555 !important;
            color: #e0e0e0 !important;
        }

        .dark-mode .preset-btn:hover {
            background: #555 !important;
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

        /* High Contrast Mode */
        .high-contrast {
            background: black !important;
            color: white !important;
        }

        .high-contrast .header,
        .high-contrast .navigation,
        .high-contrast .sidebar,
        .high-contrast .card {
            background: black !important;
            color: white !important;
            border-color: yellow !important;
        }

        .high-contrast a,
        .high-contrast .nav-links a,
        .high-contrast .card-title,
        .high-contrast .welcome-title {
            color: yellow !important;
        }

        .high-contrast .accessibility-container {
            background: black !important;
            border: 2px solid yellow !important;
        }

        .high-contrast .accessibility-option {
            background: #222 !important;
            border: 1px solid yellow !important;
            color: white !important;
        }
        .sidebar-menu .has-dropdown .dropdown-menu {
    max-height: 0;
    overflow: hidden;
    padding-left: 20px;
    transition: max-height 0.3s ease;
    list-style: none;
}

.sidebar-menu .has-dropdown.active .dropdown-menu {
    max-height: 500px; /* Adjust based on your content */
}

.sidebar-menu .dropdown-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.sidebar-menu .dropdown-arrow {
    transition: transform 0.3s ease;
    font-size: 0.75rem;
}

.sidebar-menu .has-dropdown.active .dropdown-arrow {
    transform: rotate(180deg);
}

/* Hide scrollbar but keep functionality */
.sidebar {
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.sidebar::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
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
            
            .accessibility-container {
                padding: 24px;
            }
            
            .accessibility-options {
                grid-template-columns: 1fr;
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
                <a href="accessibility.php" class="active">Accessibility</a>
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
                <li class="has-dropdown">
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
                <h2 class="welcome-title">Accessibility Settings</h2>
                <p class="welcome-subtitle">Customize your experience to suit your needs</p>
            </div>

            <div class="accessibility-container">
                <div class="accessibility-header">
                    <h2>Make NeuroAid Work for You</h2>
                    <p>Adjust these settings to improve your experience based on your individual needs</p>
                </div>

                <div class="accessibility-section">
                    <h3>Visual Adjustments</h3>
                    <div class="accessibility-options">
                        <div class="accessibility-option">
                            <div class="option-header">
                                <div class="option-icon">
                                    <i class="fas fa-text-height"></i>
                                </div>
                                <div class="option-title">Text Size</div>
                            </div>
                            <div class="option-description">
                                Increase or decrease the size of text throughout the application
                            </div>
                            <div class="option-controls">
                                <button class="option-btn" onclick="handleAccessibilityAction('increase-font')">
                                    <i class="fas fa-plus"></i> Increase
                                </button>
                                <button class="option-btn secondary" onclick="handleAccessibilityAction('decrease-font')">
                                    <i class="fas fa-minus"></i> Decrease
                                </button>
                            </div>
                        </div>

                        <div class="accessibility-option">
                            <div class="option-header">
                                <div class="option-icon">
                                    <i class="fas fa-desktop"></i>
                                </div>
                                <div class="option-title">Interface Size</div>
                            </div>
                            <div class="option-description">
                                Make all interface elements larger or smaller
                            </div>
                            <div class="option-controls">
                                <button class="option-btn" onclick="handleAccessibilityAction('increase-gui')">
                                    <i class="fas fa-plus"></i> Larger
                                </button>
                                <button class="option-btn secondary" onclick="handleAccessibilityAction('decrease-gui')">
                                    <i class="fas fa-minus"></i> Smaller
                                </button>
                            </div>
                        </div>

                        <div class="accessibility-option">
                            <div class="option-header">
                                <div class="option-icon">
                                    <i class="fas fa-moon"></i>
                                </div>
                                <div class="option-title">Color Theme</div>
                            </div>
                            <div class="option-description">
                                Change between light and dark color schemes
                            </div>
                            <div class="option-controls">
                                <button class="option-btn" onclick="handleAccessibilityAction('toggle-dark')">
                                    <i class="fas fa-adjust"></i> Toggle Dark Mode
                                </button>
                            </div>
                        </div>

                       
                    </div>
                </div>

                <div class="accessibility-section">
                    <h3>Quick Presets</h3>
                    <p>Apply these preset configurations for common needs:</p>
                    <div class="preset-buttons">
                        <button class="preset-btn" onclick="applyPreset('low-vision')">Low Vision</button>
                        <button class="preset-btn" onclick="applyPreset('motor-control')">Motor Control</button>
                        <button class="preset-btn" onclick="applyPreset('cognitive')">Cognitive Support</button>
                        <button class="preset-btn" onclick="applyPreset('default')">Default Settings</button>
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

            // Load saved preferences
            loadPreferences();
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



        function applyPreset(preset) {
            const root = document.documentElement;
            
            switch(preset) {
                case 'low-vision':
                    root.style.fontSize = '20px';
                    document.body.dataset.uiScale = '1.2';
                    document.body.style.transform = 'scale(1.2)';
                    document.body.style.transformOrigin = 'center';
           
                    document.body.classList.remove('dark-mode');
                    
                    // Save preferences
                    localStorage.setItem('fontSize', '20');
                    localStorage.setItem('uiScale', '1.2');
        
                    localStorage.removeItem('darkMode');
                    break;
                    
                case 'motor-control':
                    root.style.fontSize = '18px';
                    document.body.dataset.uiScale = '1.3';
                    document.body.style.transform = 'scale(1.3)';
                    document.body.style.transformOrigin = 'center';
            
                    document.body.classList.remove('dark-mode');
                    
                    // Save preferences
                    localStorage.setItem('fontSize', '18');
                    localStorage.setItem('uiScale', '1.3');
             
                    localStorage.removeItem('darkMode');
                    break;
                    
                case 'cognitive':
                    root.style.fontSize = '18px';
                    document.body.dataset.uiScale = '1.1';
                    document.body.style.transform = 'scale(1.1)';
                    document.body.style.transformOrigin = 'center';
                    document.body.classList.add('dark-mode');
            
                    
                    // Save preferences
                    localStorage.setItem('fontSize', '18');
                    localStorage.setItem('uiScale', '1.1');
                    localStorage.setItem('darkMode', 'true');
       
                    break;
                    
                case 'default':
                    // Reset all accessibility settings
                    root.style.fontSize = '16px';
                    document.body.style.transform = 'none';
                    document.body.dataset.uiScale = '1';
                    document.body.classList.remove('dark-mode');
                
                    
                    // Clear saved preferences
                    localStorage.removeItem('fontSize');
                    localStorage.removeItem('uiScale');
                    localStorage.removeItem('darkMode');
          
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
        // Add this to your existing JavaScript inside the DOMContentLoaded event listener

// Sidebar dropdown functionality
document.querySelectorAll('.has-dropdown .dropdown-toggle').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const parent = this.closest('.has-dropdown');
        parent.classList.toggle('active');
        
        // Close other open dropdowns
        document.querySelectorAll('.has-dropdown').forEach(dropdown => {
            if (dropdown !== parent) {
                dropdown.classList.remove('active');
            }
        });
    });
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.sidebar-menu')) {
        document.querySelectorAll('.has-dropdown').forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    }
});
    </script>
</body>
</html>