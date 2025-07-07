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
        $stmt = $pdo->prepare("INSERT INTO reminders (user_id, title, description, reminder_time, days_of_week) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $title, $description, $time, $days]);
    }
}

// Handle reminder deletion
if (isset($_GET['delete'])) {
    $reminderId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminderId, $userId]);
    header("Location: see_all_reminders.php");
    exit;
}

// Handle reminder toggle
if (isset($_GET['toggle'])) {
    $reminderId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE reminders SET is_active = NOT is_active WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminderId, $userId]);
    header("Location: see_all_reminders.php");
    exit;
}

// Get filter parameters
$typeFilter = isset($_GET['type_filter']) ? $_GET['type_filter'] : '';
$statusFilter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

// Build query with filters
$query = "SELECT * FROM reminders WHERE user_id = ?";
$params = [$userId];

if (!empty($typeFilter)) {
    $query .= " AND type = ?";
    $params[] = $typeFilter;
}

if (!empty($statusFilter)) {
    if ($statusFilter === 'Pending') {
        $query .= " AND is_active = 1";
    } elseif ($statusFilter === 'Completed') {
        $query .= " AND is_active = 0";
    }
}

$query .= " ORDER BY reminder_time";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reminders = $stmt->fetchAll();

// Function to determine reminder status
function getReminderStatus($reminder) {
    if (!$reminder['is_active']) {
        return 'Completed';
    }
    
    $reminderDateTime = date('Y-m-d') . ' ' . $reminder['reminder_time'];
    $currentDateTime = date('Y-m-d H:i:s');
    
    if ($reminderDateTime < $currentDateTime) {
        return 'Missed';
    } elseif (date('Y-m-d H:i', strtotime($reminderDateTime)) === date('Y-m-d H:i')) {
        return 'Upcoming';
    } else {
        return 'Pending';
    }
}

// Function to determine reminder type based on title/description
function getReminderType($reminder) {
    $title = strtolower($reminder['title']);
    $description = strtolower($reminder['description']);
    
    if (strpos($title, 'medication') !== false || strpos($description, 'medication') !== false || 
        strpos($title, 'medicine') !== false || strpos($description, 'medicine') !== false) {
        return 'Medication';
    } elseif (strpos($title, 'exercise') !== false || strpos($description, 'exercise') !== false ||
              strpos($title, 'stretch') !== false || strpos($description, 'stretch') !== false) {
        return 'Exercise';
    } elseif (strpos($title, 'game') !== false || strpos($description, 'game') !== false ||
              strpos($title, 'cognitive') !== false || strpos($description, 'cognitive') !== false) {
        return 'Cognitive';
    } elseif (strpos($title, 'journal') !== false || strpos($description, 'journal') !== false ||
              strpos($title, 'write') !== false || strpos($description, 'write') !== false) {
        return 'Journal';
    } elseif (strpos($title, 'appointment') !== false || strpos($description, 'appointment') !== false ||
              strpos($title, 'apt') !== false || strpos($description, 'apt') !== false) {
        return 'Apt.';
    } else {
        return 'General';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Health - All Reminders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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

        /* Main Layout */
        .main-container {
            display: flex;
            max-width: 1800px;
            margin: 0 auto;
            min-height: calc(100vh - 140px);
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 32px;
            position: relative;
        }

        /* Welcome Section */
        .welcome-section {
            text-align: left;
            margin-left: 100px;
            padding: 24px 0;
        }

        .welcome-title {
            font-size: 32px;
            color: rgb(255, 255, 255);
            font-weight: 300;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Card Styles */
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
            margin-bottom: 24px;
        }

        .card-header {
            padding: 20px 20px 0;
            margin-bottom: 16px;
        }

        .card-title {
            font-size: 16px;
            font-weight: 600;
            color: #4b5563;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-content {
            margin-top: 20px;
            padding: 0 20px 20px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: #2d5a4c;
            color: white;
            position: relative;
            z-index: 5;
            border-radius: 16px;
            margin: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .sidebar-header {
            margin-top: 20px;
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(45, 90, 76, 0.1);
            margin-bottom: 24px;
        }

        .profile-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #2d5a4c, #16a34a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            box-shadow: 0 4px 16px rgba(45, 90, 76, 0.2);
        }

        .profile-avatar i {
            color: white;
            font-size: 24px;
        }

        .welcome-text {
            color: rgb(239, 239, 239);
            font-weight: 500;
            font-size: 14px;
        }

        .user-name {
            color: rgb(255, 255, 255);
            font-weight: 600;
            font-size: 16px;
            margin-top: 4px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 4px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 400;
            font-size: 14px;
        }

        .sidebar-menu a:hover {
            background: rgba(45, 90, 76, 0.1);
            color: rgb(255, 255, 255);
            border: 1px solid rgb(255, 255, 255);
            border-radius: 4px;
            padding: 4px 8px;
        }

        .sidebar-menu li.active a {
            background: linear-gradient(135deg, #2d5a4c, #16a34a);
            color: white;
            box-shadow: 0 4px 12px rgba(45, 90, 76, 0.3);
        }

        .sidebar-menu i {
            width: 18px;
            text-align: center;
            font-size: 16px;
        }

        .logout-section {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid rgba(45, 90, 76, 0.1);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgb(250, 250, 250);
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
            width: 100%;
            font-weight: 400;
            font-size: 14px;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        /* All Reminders Specific Styles */
        .reminders-container {
            background: #6d8f88;
            border-radius: 16px;
            padding: 24px;
            color: white;
        }

        .reminders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .reminders-title {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        .filter-controls {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .filter-dropdown {
            position: relative;
            display: inline-block;
        }

        .filter-btn {
            background: #2d5a4c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover {
            background: #1f3d34;
        }

        .add-reminder-btn {
            background: #2d5a4c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .add-reminder-btn:hover {
            background: #1f3d34;
        }

        .reminders-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .reminders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .reminders-table thead {
            background: #f8f9fa;
        }

        .reminders-table th {
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }

        .reminders-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }

        .reminders-table tr:last-child td {
            border-bottom: none;
        }

        .reminders-table tr:hover {
            background-color: #f9fafb;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-missed {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-upcoming {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 500;
            background: #e5e7eb;
            color: #374151;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-done {
            background: #2d5a4c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-done:hover {
            background: #1f3d34;
            color: white;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-edit:hover {
            background: #2563eb;
            color: white;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-delete:hover {
            background: #dc2626;
            color: white;
        }

        .no-reminders {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .accessibility-note {
            margin-top: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
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
            font-size: 24px;
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
            font-size: 14px;
        }

        @media (max-width: 1024px) {
            .main-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                margin: 16px 0;
            }
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 16px;
            }

            .sidebar {
                margin: 8px;
                padding: 16px;
            }

            .filter-controls {
                flex-direction: column;
                gap: 8px;
            }

            .reminders-table {
                font-size: 14px;
            }

            .reminders-table th,
            .reminders-table td {
                padding: 8px 6px;
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
                <a href="index.php">Home</a>
                <a href="health.php">Health</a>
                <a href="chat1.php">Chat</a>
                <a href="accessibility.php">Accessibility</a>
                <a href="reminder.php" class="active">Reminder</a>
                <a href="menu.php">Menu</a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Content Area -->
        <main class="content-area">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2 class="welcome-title">SEE ALL REMINDERS</h2>
            </div>

            <!-- All Reminders Content -->
            <div class="reminders-container">
                <div class="reminders-header">
                    <h3 class="reminders-title">All Reminders</h3>
                    <div class="filter-controls">
                        <div class="filter-dropdown">
                            <select class="filter-btn" onchange="filterReminders('type', this.value)">
                                <option value="">Filter Type</option>
                                <option value="Medication" <?php echo $typeFilter === 'Medication' ? 'selected' : ''; ?>>Medication</option>
                                <option value="Exercise" <?php echo $typeFilter === 'Exercise' ? 'selected' : ''; ?>>Exercise</option>
                                <option value="Cognitive" <?php echo $typeFilter === 'Cognitive' ? 'selected' : ''; ?>>Cognitive</option>
                                <option value="Journal" <?php echo $typeFilter === 'Journal' ? 'selected' : ''; ?>>Journal</option>
                                <option value="Apt." <?php echo $typeFilter === 'Apt.' ? 'selected' : ''; ?>>Appointment</option>
                                <option value="General" <?php echo $typeFilter === 'General' ? 'selected' : ''; ?>>General</option>
                            </select>
                        </div>
                        <div class="filter-dropdown">
                            <select class="filter-btn" onchange="filterReminders('status', this.value)">
                                <option value="">Filter Status</option>
                                <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Completed" <?php echo $statusFilter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Missed" <?php echo $statusFilter === 'Missed' ? 'selected' : ''; ?>>Missed</option>
                                <option value="Upcoming" <?php echo $statusFilter === 'Upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            </select>
                        </div>
                        <button class="add-reminder-btn" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                            <i class="fas fa-plus"></i> Add Reminders
                        </button>
                    </div>
                </div>

                <div class="reminders-table-container">
                    <?php if (empty($reminders)): ?>
                        <div class="no-reminders">
                            <i class="fas fa-bell-slash" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                            <p>No reminders found matching your criteria.</p>
                        </div>
                    <?php else: ?>
                        <table class="reminders-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Activities</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reminders as $reminder): ?>
                                    <?php 
                                        $status = getReminderStatus($reminder);
                                        $type = getReminderType($reminder);
                                    ?>
                                    <tr>
                                        <td><strong><?php echo date('g:i a', strtotime($reminder['reminder_time'])); ?></strong></td>
                                        <td><?php echo htmlspecialchars($reminder['title']); ?></td>
                                        <td><span class="type-badge"><?php echo $type; ?></span></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($status); ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($reminder['is_active']): ?>
                                                    <a href="?toggle=<?php echo $reminder['id']; ?>" class="btn-done">Done</a>
                                                <?php endif; ?>
                                                <a href="edit_reminders.php?id=<?php echo $reminder['id']; ?>" class="btn-edit">Edit</a>
                                                <a href="?delete=<?php echo $reminder['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this reminder?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <div class="accessibility-note">
                    <i class="fas fa-universal-access"></i>
                    <span>Accessibility</span>
                </div>
            </div>
        </main>

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="welcome-text">Welcome, <?php echo htmlspecialchars($userData['username']); ?></div>
                <div class="user-name"><?php echo htmlspecialchars($userData['full_name']); ?></div>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="health.php">
                        <i class="fas fa-heartbeat"></i>
                        <span>Health Management</span>
                    </a>
                </li>
                <li class="active">
                    <a href="reminder.php">
                        <i class="fas fa-bell"></i>
                        <span>Reminders & Schedule</span>
                    </a>
                </li>
                <li>
                    <a href="training.php">
                        <i class="fas fa-brain"></i>
                        <span>Cognitive Training</span>
                    </a>
                </li>
                <li>
                    <a href="journal.php">
                        <i class="fas fa-book"></i>
                        <span>Personal Journal</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>My Profile</span>
                    </a>
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
                    <span>Logout</span>
                </a>
            </div>
        </aside>
    </div>

    <!-- Add Reminder Modal -->
    <div class="modal fade" id="addReminderModal" tabindex="-1" aria-labelledby="addReminderModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="POST" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addReminderModalLabel">Add New Reminder</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="title" class="form-label">Title *</label>
              <input type="text" id="title" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description (Optional)</label>
              <textarea id="description" name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
              <label for="time" class="form-label">Time *</label>
              <input type="time" id="time" name="time" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Repeat on</label>
              <div class="d-flex flex-wrap gap-2">
                <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="days[]" value="<?php echo $day; ?>" id="day-<?php echo $day; ?>">
                        <label class="form-check-label" for="day-<?php echo $day; ?>"><?php echo $day; ?></label>
                    </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="add_reminder" class="btn btn-primary">Add Reminder</button>
          </div>
        </form>
      </div>
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

    <!-- Edit Reminder Modal -->
<div class="modal fade" id="editReminderModal" tabindex="-1" aria-labelledby="editReminderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editReminderModalLabel">Edit Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="edit_reminders.php">
                <input type="hidden" name="reminder_id" id="edit_reminder_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_time" class="form-label">Time *</label>
                        <input type="time" class="form-control" id="edit_time" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Repeat on</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
                                <div class="form-check">
                                    <input class="form-check-input day-checkbox" type="checkbox" 
                                           name="days[]" value="<?php echo $day; ?>" id="edit_day_<?php echo $day; ?>">
                                    <label class="form-check-label" for="edit_day_<?php echo $day; ?>">
                                        <?php echo $day; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_reminder" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Function to populate edit modal with reminder data
function populateEditModal(reminderId) {
    // In a real implementation, you would fetch the reminder data via AJAX
    // For this example, we'll assume the data is available in a JavaScript object
    
    // This would be replaced with actual AJAX call in production
    fetch('get_reminder.php?id=' + reminderId)
        .then(response => response.json())
        .then(reminder => {
            document.getElementById('edit_reminder_id').value = reminder.id;
            document.getElementById('edit_title').value = reminder.title;
            document.getElementById('edit_description').value = reminder.description;
            document.getElementById('edit_time').value = reminder.reminder_time.substring(0, 5);
            
            // Clear all checkboxes first
            document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Check the appropriate days
            if (reminder.days_of_week) {
                const days = reminder.days_of_week.split(',');
                days.forEach(day => {
                    const checkbox = document.getElementById('edit_day_' + day);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
            
            // Show the modal
            var editModal = new bootstrap.Modal(document.getElementById('editReminderModal'));
            editModal.show();
        })
        .catch(error => {
            console.error('Error fetching reminder:', error);
            alert('Failed to load reminder data. Please try again.');
        });
}

// Update your edit links to call this function
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const reminderId = this.getAttribute('href').split('=')[1];
            populateEditModal(reminderId);
        });
    });
});
</script>