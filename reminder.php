<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userData = getUserData($userId);

// Handle reminder submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reminder'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $time = trim($_POST['time']);
    $days = isset($_POST['days']) ? implode(',', $_POST['days']) : '';
    
    if (!empty($title) && !empty($time)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reminders (user_id, title, description, reminder_time, days_of_week) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $title,
                $description,
                $time,
                $days
            ]);
            
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $error_message = "Error adding reminder: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill in all required fields.";
    }
}

// Handle reminder deletion
if (isset($_GET['delete'])) {
    $reminderId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminderId, $userId]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle reminder toggle
if (isset($_GET['toggle'])) {
    $reminderId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE reminders SET is_active = NOT is_active WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminderId, $userId]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Get all reminders
$stmt = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? ORDER BY reminder_time");
$stmt->execute([$userId]);
$reminders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Health - Reminders</title>
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

        /* Alert Styles */
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        /* Summary Content Styles */
        .summary-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .summary-section {
            margin-bottom: 24px;
        }

        .section-title {
            font-weight: bold;
            color: #2d5a4c;
            margin-bottom: 12px;
            font-size: 1.125rem; /* 18px */
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: #2d5a4c;
        }

        /* Time Display Styles */
        .time-display-container {
            background: linear-gradient(135deg, #2d5a4c, #16a34a);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            color: white;
            box-shadow: 0 4px 12px rgba(45, 90, 76, 0.3);
        }

        .current-time {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .current-date {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Reminders Container */
        .reminders-container {
            background: #f9fafb;
            border-radius: 12px;
            padding: 16px;
            max-height: 400px;
            overflow-y: auto;
        }

        .no-reminders {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .no-reminders i {
            font-size: 3rem; /* 48px */
            color: #d1d5db;
            margin-bottom: 16px;
        }

        .no-reminders p {
            font-size: 1rem; /* 16px */
            margin-bottom: 8px;
        }

        .no-reminders small {
            font-size: 0.875rem; /* 14px */
            color: #9ca3af;
        }

        /* Reminder Items */
        .reminder-item {
            display: flex;
            align-items: center;
            padding: 16px;
            background: white;
            border-radius: 8px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #2d5a4c;
            transition: all 0.3s ease;
        }

        .reminder-item.completed {
            opacity: 0.6;
            border-left-color: #9ca3af;
        }

        .reminder-item.completed .reminder-title {
            text-decoration: line-through;
        }

        .reminder-time {
            min-width: 80px;
            font-weight: 600;
            color: #2d5a4c;
            font-size: 0.875rem; /* 14px */
        }

        .reminder-details {
            flex: 1;
            padding: 0 16px;
        }

        .reminder-title {
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }

        .reminder-desc {
            font-size: 0.875rem; /* 14px */
            color: #6b7280;
            margin-bottom: 4px;
        }

        .reminder-days {
            font-size: 0.75rem; /* 12px */
            color: #9ca3af;
        }

        .reminder-actions {
            display: flex;
            gap: 8px;
        }

        .btn-done {
            background: #16a34a;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.75rem; /* 12px */
            display: flex;
            align-items: center;
            gap: 4px;
            transition: background 0.2s;
        }

        .btn-done:hover {
            background: #15803d;
            color: white;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
            padding: 6px 8px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.75rem; /* 12px */
            transition: background 0.2s;
        }

        .btn-delete:hover {
            background: #b91c1c;
            color: white;
        }

        /* Reminder Actions Footer */
        .reminder-actions-footer {
            display: flex;
            gap: 12px;
            justify-content: center;
            padding-top: 16px;
            margin-top: 16px;
            border-top: 1px solid #e5e7eb;
        }

        .btn-add-reminder {
            background: #2d5a4c;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-add-reminder:hover {
            background: #1e3a2e;
        }

        .btn-see-all {
            background: #6b7280;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-see-all:hover {
            background: #4b5563;
            color: white;
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

        /* Custom Modal Styles */
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .custom-modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .custom-modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-modal-header h5 {
            margin: 0;
            color: #2d5a4c;
            font-size: 1.25rem;
        }

        .custom-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            line-height: 1;
        }

        .custom-modal-body {
            padding: 20px;
        }

        .custom-modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #e5e7eb;
            text-align: right;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #2d5a4c;
            box-shadow: 0 0 0 2px rgba(45, 90, 76, 0.1);
        }

        .days-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .day-checkbox {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .day-checkbox input[type="checkbox"] {
            margin: 0;
        }

        .day-checkbox label {
            font-size: 0.875rem; /* 14px */
            cursor: pointer;
            margin: 0;
        }

        .btn-submit {
            background: #2d5a4c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-submit:hover {
            background: #1e3a2e;
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

        .dark-mode .time-display-container {
            background: linear-gradient(135deg, #1a1a1a, #2d2d2d) !important;
        }

        .dark-mode .reminders-container {
            background: #252525 !important;
        }

        .dark-mode .reminder-item {
            background: #333 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
        }

        .dark-mode .reminder-title {
            color: #e0e0e0 !important;
        }

        .dark-mode .reminder-desc {
            color: #b0b0b0 !important;
        }

        .dark-mode .custom-modal-content {
            background: #252525 !important;
            color: #e0e0e0 !important;
        }

        .dark-mode .custom-modal-header {
            border-bottom-color: #444 !important;
        }

        .dark-mode .custom-modal-footer {
            border-top-color: #444 !important;
        }

        .dark-mode .form-control {
            background: #333 !important;
            border-color: #444 !important;
            color: #e0e0e0 !important;
        }

        .dark-mode .form-control:focus {
            border-color: #2d5a4c !important;
        }

        .dark-mode .form-label {
            color: #e0e0e0 !important;
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

            .summary-content {
                grid-template-columns: 1fr;
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

            .reminder-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .reminder-actions {
                width: 100%;
                justify-content: flex-end;
            }

            .reminder-actions-footer {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .welcome-section {
                margin-right: 0;
                text-align: center;
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
                <a href="reminder.php" class="active">Reminder</a>
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
                <li class="has-dropdown active">
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
                <h2 class="welcome-title">Reminders & Schedule</h2>
                <p class="welcome-subtitle">Stay on track with your daily activities</p>
            </div>

            <!-- Error Message Display -->
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Reminders Content -->
            <div class="card">
                <div class="card-content">
                    <div class="summary-content">
                        <!-- Time & Date Section -->
                        <div class="summary-section">
                            <div class="section-title">
                                <i class="fas fa-clock"></i>
                                <span>Current Time & Date</span>
                            </div>
                            <div class="time-display-container">
                                <div class="time-display">
                                    <div class="current-time" id="current-time">--:--</div>
                                    <div class="current-date" id="current-date">Loading...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Reminders Section -->
                        <div class="summary-section">
                            <div class="section-title">
                                <i class="fas fa-bell"></i>
                                <span>Today's Reminders</span>
                            </div>
                            <div class="reminders-container">
                                <?php if (empty($reminders)): ?>
                                    <div class="no-reminders">
                                        <i class="fas fa-calendar-check"></i>
                                        <p>No reminders for today.</p>
                                        <small>Click the + button to add your first reminder</small>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($reminders as $reminder): ?>
                                        <div class="reminder-item <?php echo $reminder['is_active'] ? '' : 'completed'; ?>">
                                            <div class="reminder-time">
                                                <?php echo date('g:i A', strtotime($reminder['reminder_time'])); ?>
                                            </div>
                                            <div class="reminder-details">
                                                <div class="reminder-title"><?php echo htmlspecialchars($reminder['title']); ?></div>
                                                <?php if (!empty($reminder['description'])): ?>
                                                    <div class="reminder-desc"><?php echo htmlspecialchars($reminder['description']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($reminder['days_of_week'])): ?>
                                                    <div class="reminder-days">
                                                        <small>Repeats: <?php echo htmlspecialchars($reminder['days_of_week']); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="reminder-actions">
                                                <a href="?toggle=<?php echo $reminder['id']; ?>" class="btn-done">
                                                    <i class="fas fa-<?php echo $reminder['is_active'] ? 'check' : 'undo'; ?>"></i>
                                                    <?php echo $reminder['is_active'] ? 'Done' : 'Undo'; ?>
                                                </a>
                                                <a href="?delete=<?php echo $reminder['id']; ?>" class="btn-delete" onclick="return confirm('Delete this reminder?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <div class="reminder-actions-footer">
                                    <button class="btn-add-reminder">
                                        <i class="fas fa-plus"></i>
                                        Add New Reminder
                                    </button>
                                    <a href="see_all_reminders.php" class="btn-see-all">
                                        <i class="fas fa-list"></i>
                                        See All Reminders
                                    </a>
                                </div>
                            </div>
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

    <!-- Fixed Add Reminder Modal -->
    <div class="custom-modal" id="addReminderModal">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5>Add New Reminder</h5>
                <button type="button" class="custom-modal-close">&times;</button>
            </div>
            <form method="POST" id="reminderForm">
                <div class="custom-modal-body">
                    <div class="form-group">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <textarea id="description" name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="time" class="form-label">Time *</label>
                        <input type="time" id="time" name="time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Repeat on (Optional)</label>
                        <div class="days-container">
                            <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
                                <div class="day-checkbox">
                                    <input type="checkbox" name="days[]" value="<?php echo $day; ?>" id="day-<?php echo $day; ?>">
                                    <label for="day-<?php echo $day; ?>"><?php echo $day; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="submit" name="add_reminder" class="btn-submit">Add Reminder</button>
                </div>
            </form>
        </div>
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

            // Time Update Function
            const updateTime = () => {
                const now = new Date();
                document.getElementById("current-time").textContent = now.toLocaleTimeString([], {
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true
                });
                document.getElementById("current-date").textContent = now.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            };
            
            // Update time every second
            setInterval(updateTime, 1000);
            updateTime();

            // Custom Modal Functionality
            const modal = document.getElementById('addReminderModal');
            const modalBtn = document.querySelector('.btn-add-reminder');
            const closeBtn = document.querySelector('.custom-modal-close');
            const form = document.getElementById('reminderForm');
            
            // Open modal
            modalBtn.addEventListener('click', function() {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
            
            // Close modal
            function closeModal() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
                form.reset(); // Clear form when closing
            }
            
            closeBtn.addEventListener('click', closeModal);
            
            // Close when clicking outside modal
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
            
            // Handle form submission
            form.addEventListener('submit', function(e) {
                const title = document.getElementById('title').value.trim();
                const time = document.getElementById('time').value.trim();
                
                if (!title || !time) {
                    e.preventDefault();
                    alert('Please fill in all required fields (Title and Time).');
                    return;
                }
                
                // Form will submit normally if validation passes
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