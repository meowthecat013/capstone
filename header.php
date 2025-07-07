<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userData = getUserData($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Health - Care Summary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background: linear-gradient(to right, #2d5a4c 0%, #2d5a4c 35%, white 35.05%, white 100%);
            min-height: 100vh;
            margin: 0;
        }

        /* Header Styles */
        .header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 16px 24px;
            position: relative;
            z-index: 10;
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
            font-size: 18px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #2d5a4c;
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
            font-size: 14px;
            color: #2d5a4c;
        }

        .phone-subtitle {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Navigation Styles */
        .navigation {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 24px;
            position: relative;
            z-index: 10;
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
            font-size: 16px;
            padding: 16px 0;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #2d5a4c;
            border-bottom-color: #2d5a4c;
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
                <a href="user_dashboard.php">Home</a>
                <a href="health.php" class="active">Health</a>
                <a href="chat1.php">Chat</a>
                <a href="accessibility.php">Accessibility</a>
                <a href="reminder.php">Reminder</a>
                <a href="menu.php">Menu</a>
            </div>
        </div>
    </nav>