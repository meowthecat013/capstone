<?php
date_default_timezone_set('Asia/Manila');

define('INCLUDED_FROM_INDEX', true);

require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userData = getUserData($userId);

// Check if today's vitals already submitted
$today = date('Y-m-d');
$showVitalsModal = false;

$stmt = $pdo->prepare("SELECT * FROM daily_vitals WHERE user_id = ? AND date = ?");
$stmt->execute([$userId, $today]);
$todayVitals = $stmt->fetch();

// Get current hour and whether modal was shown today
$currentHour = date('G');
$currentTime = date('H:i:s');
$resetTime = '06:00:00';

// Check if we should show the modal:
// 1. No vitals submitted today AND (it's after 6 AM OR it's a new day)
// 2. Reset the modal display at 6 AM each day
if (!$todayVitals) {
    // If it's after 6 AM or a new day, show the modal
    if ($currentHour >= 6 || $currentTime >= $resetTime) {
        $showVitalsModal = true;
    }
}

// Store in session that modal was shown today
if ($showVitalsModal) {
    $_SESSION['vitals_shown_date'] = $today;
}

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

// Get music suggestions based on vitals (modified version)
$musicCategory = 'Neutral'; // Default category
$musicSuggestions = [];

if ($todayVitals) {
    $musicCategory = determineMusicCategory($todayVitals);
    $stmt = $pdo->prepare("SELECT * FROM music_library WHERE mood_suggestion = ? OR category = ? ORDER BY RAND() LIMIT 5");
    $stmt->execute([$musicCategory, $musicCategory]);
    $musicSuggestions = $stmt->fetchAll();
} else {
    // Fallback if no vitals submitted today
    $stmt = $pdo->prepare("SELECT * FROM music_library ORDER BY RAND() LIMIT 5");
    $stmt->execute();
    $musicSuggestions = $stmt->fetchAll();
}

// Enhanced determineMusicCategory function
function determineMusicCategory($vitals) {
    $mood = $vitals['mood'];
    $bpSystolic = $vitals['blood_pressure_systolic'];
    $bpDiastolic = $vitals['blood_pressure_diastolic'];
    $heartRate = $vitals['heart_rate'];
    
    // High blood pressure or heart rate
    if ($bpSystolic > 140 || $bpDiastolic > 90 || $heartRate > 100) {
        if ($mood === 'Anxious' || $mood === 'Angry') {
            return 'Calming';
        }
        return 'Relaxing';
    } 
    // Low blood pressure or heart rate
    elseif ($bpSystolic < 100 || $bpDiastolic < 60 || $heartRate < 60) {
        if ($mood === 'Tired' || $mood === 'Sad') {
            return 'Uplifting';
        }
        return 'Neutral';   
    } 
    // Normal vitals - mood-based selection
    else {
        switch($mood) {
            case 'Happy': return 'Upbeat';
            case 'Sad': return 'Comforting';
            case 'Anxious': return 'Grounding';
            case 'Angry': return 'Soothing';
            case 'Tired': return 'Energizing';
            default: return 'Neutral';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Health - Dashboard</title>
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

        /* Main Content Grid */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr 300px;
            grid-template-rows: auto auto;
            gap: 24px;
            margin-bottom: 32px;
        }

        .music-player {
            grid-column: 1;
            grid-row: 1;
        }

        .quote-card {
            grid-column: 2;
            grid-row: 1;
        }

        .vital-status {
            grid-column: 3;
            grid-row: 1 / 3;
        }

        /* Expanded vital status when sidebar is hidden */
        .content-area.full-width .main-content {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .content-area.full-width .vital-status {
            grid-column: 3;
            grid-row: 1 / 3;
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

        /* Music Player Styles */
        .music-player {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .music-status {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .play-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2d5a4c, #1e3d34);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem; /* 20px */
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(45, 90, 76, 0.3);
        }

        .play-icon:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(45, 90, 76, 0.4);
        }

        .music-info {
            flex: 1;
        }

        .music-info h3 {
            font-size: 1.125rem; /* 18px */
            font-weight: 600;
            color: #2d5a4c;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }

        .music-info p {
            font-size: 0.875rem; /* 14px */
            color: #6b7280;
            transition: all 0.3s ease;
        }

        .progress-container {
            margin: 20px 0;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-fill {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #2d5a4c, #16a34a);
            border-radius: 4px;
            transition: width 0.1s linear;
        }

        .time-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem; /* 12px */
            color: #6b7280;
        }

        .player-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 20px;
        }

        .control-btn {
            background: none;
            border: none;
            color: #2d5a4c;
            font-size: 1.125rem; /* 18px */
            cursor: pointer;
            transition: all 0.2s;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .control-btn:hover {
            background: rgba(45, 90, 76, 0.1);
            color: #1e3d34;
        }

        #volume-control {
            width: 100px;
            accent-color: #2d5a4c;
        }

        /* Quote Card */
        .quote-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 10px#2d5a4c;
            backdrop-filter: blur(10px);
        }

        .quote-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2d5a4c, #16a34a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem; /* 20px */
            margin-bottom: 16px;
        }

        .quote-text {
            font-style: italic;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 12px;
            font-size: 1rem; /* 16px */
            transition: all 0.3s ease;
        }

        .quote-author {
            font-weight: 500;
            color: #2d5a4c;
            font-size: 0.875rem; /* 14px */
            transition: all 0.3s ease;
        }

        /* Vital Status */
        .vital-status {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .vital-status h3 {
            font-size: 1.125rem; /* 18px */
            font-weight: 600;
            color: #2d5a4c;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .vital-items {
            space-y: 12px;
        }

        .vital-item {
            padding: 12px 0;
            border-bottom: 1px solid rgba(243, 244, 246, 0.5);
        }

        .vital-item:last-child {
            border-bottom: none;
        }

        .vital-item p {
            color: #6b7280;
            font-size: 0.875rem; /* 14px */
            line-height: 1.5;
            transition: all 0.3s ease;
        }

        .vital-link {
            color: #2d5a4c;
            cursor: pointer;
            text-decoration: underline;
            font-weight: 500;
        }

        .vital-link:hover {
            color: #1e3d34;
        }

        /* Quick Access - Full Width Bottom Section */
        .quick-access {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            width: 100%;
        }

        .quick-access h3 {
            font-size: 1.125rem; /* 18px */
            font-weight: 600;
            color: #2d5a4c;
            margin-bottom: 20px;
        }
        .quick-color{
          height: 120px;
          background: #2d5a4c;  
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
            background: #2d5a4c;
        }

        .quick-btn {
            margin: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 20px 12px;
            background: rgba(248, 249, 250, 0.8);
            border: 1px solid rgba(229, 231, 235, 0.5);
            border-radius: 12px;
            text-decoration: none;
            color: #4b5563;
            font-size: 0.8125rem; /* 13px */
            font-weight: 500;
            transition: all 0.3s ease;
            min-height: 80px;
        }

        .quick-btn:hover {
            border-color: #2d5a4c;
            background: rgba(45, 90, 76, 0.1);
            color:rgb(255, 255, 255);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 90, 76, 0.2);
        }

        .quick-icon {
            font-size: 1.5rem; /* 24px */
            color: #6b7280;
        }

        .quick-btn:hover .quick-icon {
            color: #2d5a4c;
        }

        .accessibility-btn {
            width: 100%;
            background: linear-gradient(135deg, #2d5a4c, #16a34a);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem; /* 14px */
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .accessibility-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 90, 76, 0.3);
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

        /* Error message styling */
        .player-error {
            color: #e74c3c;
            font-size: 0.875rem; /* 14px */
            margin-top: 10px;
            text-align: center;
            display: none;
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

        /* Chat Modal - Updated */
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

        /* Accessibility Button Styles */
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
        .dark-mode .music-player,
        .dark-mode .quote-card,
        .dark-mode .vital-status,
        .dark-mode .quick-access {
            background: #1e1e1e !important;
            color: #e0e0e0 !important;
            border-color: #333 !important;
        }

        .dark-mode .header h1,
        .dark-mode .nav-links a,
        .dark-mode .card-title,
        .dark-mode .welcome-title,
        .dark-mode .quote-text,
        .dark-mode .vital-status h3,
        .dark-mode .quick-access h3 {
            color: #e0e0e0 !important;
        }

        .dark-mode .sidebar {
            background: #1a1a1a !important;
        }

        .dark-mode .quick-btn {
            background: #2a2a2a !important;
            color: #e0e0e0 !important;
            border-color: #333 !important;
        }

        .dark-mode .quick-btn:hover {
            background: #333 !important;
        }

        .dark-mode .quick-icon {
            color: #e0e0e0 !important;
        }

        .dark-mode .vital-item p,
        .dark-mode .music-info p,
        .dark-mode .time-info {
            color: #b0b0b0 !important;
        }

        .dark-mode .vital-link {
            color: #4d9f8a !important;
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

            .main-content {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto;
            }
            
            .music-player {
                grid-column: 1;
                grid-row: 1;
            }

            .quote-card {
                grid-column: 1;
                grid-row: 2;
            }

            .vital-status {
                grid-column: 1;
                grid-row: 3;
            }

            .quick-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .floating-accessibility-btn {
                bottom: 170px;
            }

            .accessibility-menu {
                bottom: 240px;
            }
        }

        @media (max-width: 768px) {
            .quick-grid {
                grid-template-columns: 1fr;
            }
            
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
                <a href="user_dashboard.php" class="active">Home</a>
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
                <li class="active">
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
                        <i class="fas fa-user-cog"></i>
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
                <h2 class="welcome-title">Welcome, what do you want to do?</h2>
            </div>

            <!-- Main Content Grid -->
            <div class="main-content">
                <!-- Music Player Card -->
                <div class="music-player">
                    <div class="music-status">
                        <div class="play-icon" id="play-pause">
                            <i class="fas fa-play" id="play-icon"></i>
                        </div>
                        <div class="music-info">
                            <h3 id="current-song-title">Music Playing...</h3>
                            <p id="current-song-artist">Melodies of Recovery</p>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progress-bar"></div>
                        </div>
                        <div class="time-info">
                            <span id="current-time">0:00</span>
                            <span id="duration">0:00</span>
                        </div>
                    </div>
                    
                    <div class="player-controls">
                        <button class="control-btn" id="prev-song" title="Previous">
                            <i class="fas fa-step-backward"></i>
                        </button>
                        <button class="control-btn" id="play-pause-btn" title="Play/Pause">
                            <i class="fas fa-play" id="main-play-icon"></i>
                        </button>
                        <button class="control-btn" id="next-song" title="Next">
                            <i class="fas fa-step-forward"></i>
                        </button>
                        <input type="range" id="volume-control" min="0" max="1" step="0.01" value="0.7" title="Volume">
                    </div>
                    
                    <div class="player-error" id="player-error"></div>
                </div>

                <!-- Quote Card -->
                <div class="quote-card">
                    <div class="quote-icon">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <h3 style="font-size: 1rem; font-weight: 600; color: #2d5a4c; margin-bottom: 12px;">Quotes of the day</h3>
                    <blockquote class="quote-text" id="daily-quote">
                        "Write it on your heart that every day is the best in the year"
                    </blockquote>
                    <div class="quote-author" id="quote-author">
                        Ralph Waldo Emerson
                    </div>
                </div>

                <div class="vital-status">
                    <h3>Vital Status</h3>
                    <div class="vital-items">
                        <?php if ($todayVitals): ?>
                            <div class="vital-item">
                                <p>Your blood pressure is <?php echo $todayVitals['blood_pressure_systolic']; ?>/<?php echo $todayVitals['blood_pressure_diastolic']; ?> mmHg today.</p>
                            </div>
                            <div class="vital-item">
                                <p>Yesterday, You played memory games.. <span class="vital-link">Click here</span> to continue.</p>
                            </div>
                            <div class="vital-item">
                                <p>You seems so happy today! Want to hear a new song? <span class="vital-link" onclick="playRandomSong()">Tap here</span> to play</p>
                            </div>
                            <div class="vital-item">
                                <p>Your health becomes more better based on your status</p>
                            </div>
                        <?php else: ?>
                            <div class="vital-item">
                                <p>Time for your Daily Vital Check up</p>
                            </div>
                            <div class="vital-item">
                                <p>Yesterday, You played memory games.. <span class="vital-link">Click here</span> to continue.</p>
                            </div>
                            <div class="vital-item">
                                <p>You seems so happy today! Want to hear a new song? <span class="vital-link" onclick="playRandomSong()">Tap here</span> to play</p>
                            </div>
                            <div class="vital-item">
                                <p>Your health becomes more better based on your status</p>
                            </div>
                        <?php endif; ?>
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

    <!-- Include vitals modal if should show -->
    <?php if ($showVitalsModal): ?>
        <?php include 'vitals.php'; ?>
        
        <script>
        // Automatically show the modal when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('vitalsModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
        </script>
    <?php endif; ?>

    <script>
// Combined DOMContentLoaded listener
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
    if (chatButton) {
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
    }

    // Music Player State
    let audioPlayer = null;
    let currentPlaylist = [];
    let currentSongIndex = 0;
    let isPlaying = false;
    let progressInterval = null;

    function showPlayerError(message) {
        const errorElement = document.getElementById('player-error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        console.error('Music Player Error:', message);
    }

    function clearPlayerError() {
        const errorElement = document.getElementById('player-error');
        if (errorElement) {
            errorElement.style.display = 'none';
            errorElement.textContent = '';
        }
    }

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    function updateProgress() {
        if (audioPlayer && !isNaN(audioPlayer.duration)) {
            const progress = (audioPlayer.currentTime / audioPlayer.duration) * 100;
            document.getElementById('progress-bar').style.width = progress + '%';
            document.getElementById('current-time').textContent = formatTime(audioPlayer.currentTime);
            document.getElementById('duration').textContent = formatTime(audioPlayer.duration);
        }
    }

    function startProgressTracking() {
        stopProgressTracking();
        progressInterval = setInterval(updateProgress, 500);
    }

    function stopProgressTracking() {
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }
    }

    function updateNowPlaying(song) {
        if (!song || !song.title) {
            console.error('Invalid song object:', song);
            return;
        }
        
        document.getElementById('current-song-title').textContent = song.title;
        document.getElementById('current-song-artist').textContent = song.artist || 'Unknown Artist';
    }

    function initializeMusicPlayer() {
        const suggestions = <?php echo json_encode($musicSuggestions); ?>;
        
        console.log('Initializing player with:', suggestions);
        
        if (suggestions && suggestions.length > 0) {
            currentPlaylist = suggestions.map(song => {
                return {
                    id: song.id,
                    title: song.title,
                    artist: song.artist || 'Unknown Artist',
                    file_path: song.file_path,
                    category: song.category || song.mood_suggestion || 'General'
                };
            });
            
            currentSongIndex = 0;
            updateNowPlaying(currentPlaylist[currentSongIndex]);
            
            console.log('Player initialized with playlist:', currentPlaylist);
        } else {
            showPlayerError('No music available. Please try again later.');
            console.error('No music suggestions available');
        }
    }

    function loadAndPlaySong(song) {
        return new Promise((resolve, reject) => {
            console.log('Attempting to load song:', song);
            
            if (!song.id || !song.title) {
                const error = new Error('Invalid song data');
                console.error('Invalid song data:', song);
                reject(error);
                return;
            }
            
            clearPlayerError();
            
            // Update UI immediately
            updateNowPlaying(song);
            
            // Clear previous audio source
            audioPlayer.src = '';
            
            // Set new source
            audioPlayer.src = song.file_path;
            
            // Attempt to play
            const playPromise = audioPlayer.play();
            
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    console.log('Playback started successfully');
                    isPlaying = true;
                    document.getElementById('play-icon').className = 'fas fa-pause';
                    document.getElementById('main-play-icon').className = 'fas fa-pause';
                    startProgressTracking();
                    resolve();
                }).catch(error => {
                    console.error('Playback failed:', error);
                    showPlayerError('Playback failed: ' + error.message);
                    isPlaying = false;
                    document.getElementById('play-icon').className = 'fas fa-play';
                    document.getElementById('main-play-icon').className = 'fas fa-play';
                    reject(error);
                });
            }
        });
    }

    function togglePlayPause() {
        if (currentPlaylist.length === 0) {
            showPlayerError('No songs available');
            return;
        }
        
        if (isPlaying) {
            audioPlayer.pause();
            document.getElementById('play-icon').className = 'fas fa-play';
            document.getElementById('main-play-icon').className = 'fas fa-play';
            isPlaying = false;
            stopProgressTracking();
        } else {
            if (!audioPlayer.src || audioPlayer.src === '') {
                loadAndPlaySong(currentPlaylist[currentSongIndex])
                    .catch(error => {
                        console.error('Error loading song:', error);
                    });
            } else {
                audioPlayer.play()
                    .then(() => {
                        document.getElementById('play-icon').className = 'fas fa-pause';
                        document.getElementById('main-play-icon').className = 'fas fa-pause';
                        isPlaying = true;
                        startProgressTracking();
                    })
                    .catch(error => {
                        console.error('Playback failed:', error);
                        showPlayerError('Playback failed: ' + error.message);
                    });
            }
        }
    }

    function playNextSong() {
        if (currentPlaylist.length === 0) return;
        
        currentSongIndex = (currentSongIndex + 1) % currentPlaylist.length;
        loadAndPlaySong(currentPlaylist[currentSongIndex])
            .catch(error => {
                console.error('Error playing next song:', error);
            });
    }

    function playPreviousSong() {
        if (currentPlaylist.length === 0) return;
        
        currentSongIndex = (currentSongIndex - 1 + currentPlaylist.length) % currentPlaylist.length;
        loadAndPlaySong(currentPlaylist[currentSongIndex])
            .catch(error => {
                console.error('Error playing previous song:', error);
            });
    }

    function playRandomSong() {
        if (currentPlaylist.length === 0) return;
        
        currentSongIndex = Math.floor(Math.random() * currentPlaylist.length);
        loadAndPlaySong(currentPlaylist[currentSongIndex])
            .catch(error => {
                console.error('Error playing random song:', error);
            });
    }

    // Initialize music player
    audioPlayer = new Audio();
    
    // Setup event listeners
    audioPlayer.addEventListener('error', function(e) {
        console.error('Audio error event:', e);
        const error = audioPlayer.error;
        let errorMessage = 'Unknown audio error';
        
        if (error) {
            switch(error.code) {
                case error.MEDIA_ERR_ABORTED:
                    errorMessage = 'Audio playback aborted';
                    break;
                case error.MEDIA_ERR_NETWORK:
                    errorMessage = 'Network error occurred';
                    break;
                case error.MEDIA_ERR_DECODE:
                    errorMessage = 'Audio decode error';
                    break;
                case error.MEDIA_ERR_SRC_NOT_SUPPORTED:
                    errorMessage = 'Audio format not supported';
                    break;
            }
        }
        showPlayerError(errorMessage);
    });
    
    audioPlayer.addEventListener('ended', function() {
        console.log('Song ended, playing next...');
        playNextSong();
    });
    
    // Setup UI controls
    document.getElementById('play-pause').addEventListener('click', togglePlayPause);
    document.getElementById('play-pause-btn').addEventListener('click', togglePlayPause);
    document.getElementById('next-song').addEventListener('click', playNextSong);
    document.getElementById('prev-song').addEventListener('click', playPreviousSong);
    
    // Volume control
    document.getElementById('volume-control').addEventListener('input', function() {
        audioPlayer.volume = this.value;
    });
    
    // Initialize the player
    initializeMusicPlayer();

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