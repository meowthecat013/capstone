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

// DB connection (using constants from config.php)
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle journal submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $imgPath = '';
    
    // Handle file upload
    if (!empty($_FILES['image']['tmp_name'])) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Validate image file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);
        
        if (in_array($fileType, $allowedTypes) && move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imgPath = $targetPath;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO journals (user_id, title, mood, activity, medication, sleep, symptoms, mobility, speech, emotional, challenge, supports, thoughts, image_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            htmlspecialchars($_POST['title']),
            htmlspecialchars($_POST['mood']),
            isset($_POST['activity']) ? implode(", ", (array)$_POST['activity']) : '',
            isset($_POST['medication']) ? implode(", ", (array)$_POST['medication']) : '',
            isset($_POST['sleep']) ? implode(", ", (array)$_POST['sleep']) : '',
            htmlspecialchars($_POST['symptoms'] ?? ''),
            htmlspecialchars($_POST['mobility'] ?? ''),
            htmlspecialchars($_POST['speech'] ?? ''),
            htmlspecialchars($_POST['emotional'] ?? ''),
            htmlspecialchars($_POST['challenge'] ?? ''),
            htmlspecialchars($_POST['supports'] ?? ''),
            htmlspecialchars($_POST['thoughts'] ?? ''),
            $imgPath
        ]);
        
        // Redirect to prevent form resubmission
        header("Location: journal.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Failed to save journal entry: " . $e->getMessage();
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $entryId = (int)$_GET['delete'];
    if ($entryId > 0) {
        try {
            // First get the image path if it exists
            $stmt = $pdo->prepare("SELECT image_path FROM journals WHERE id = ? AND user_id = ?");
            $stmt->execute([$entryId, $_SESSION['user_id']]);
            $entry = $stmt->fetch();
            
            if ($entry) {
                // Delete the entry
                $stmt = $pdo->prepare("DELETE FROM journals WHERE id = ? AND user_id = ?");
                $stmt->execute([$entryId, $_SESSION['user_id']]);
                
                // Delete the associated image file if it exists
                if ($entry['image_path'] && file_exists($entry['image_path'])) {
                    unlink($entry['image_path']);
                }
                
                header("Location: journal.php?deleted=1");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Failed to delete entry: " . $e->getMessage();
        }
    }
}

// Fetch entries
$entries = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM journals WHERE user_id = ? ORDER BY date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $entries = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to fetch entries: " . $e->getMessage();
}

// Get single entry for view modal
$viewEntry = null;
if (isset($_GET['view'])) {
    $entryId = (int)$_GET['view'];
    if ($entryId > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM journals WHERE id = ? AND user_id = ?");
            $stmt->execute([$entryId, $_SESSION['user_id']]);
            $viewEntry = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Failed to fetch entry: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Health - Personal Journal</title>
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

        /* Journal Page Styles */
        .journal-container {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .journal-header {
            margin-bottom: 32px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 16px;
        }

        .journal-header h2 {
            color: #2d5a4c;
            font-size: 1.5rem; /* 24px */
        }

        .journal-header p {
            color: #6b7280;
            margin-top: 8px;
        }

        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .filter-btn {
            background: #e5e7eb;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.875rem; /* 14px */
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: #2d5a4c;
            color: white;
        }

        .search-container {
            position: relative;
            width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px;
            padding-right: 40px;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            font-size: 0.875rem; /* 14px */
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
        }

        .entries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .entry-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .entry-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .entry-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .entry-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(45, 90, 76, 0.9), transparent);
            color: white;
            padding: 16px;
        }

        .entry-title {
            font-weight: 600;
            font-size: 1rem; /* 16px */
            margin-bottom: 4px;
        }

        .entry-date {
            font-size: 0.875rem; /* 14px */
            opacity: 0.9;
            margin-bottom: 12px;
        }

        .entry-actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem; /* 12px */
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem; /* 20px */
            font-weight: 600;
            color: #2d5a4c;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem; /* 24px */
            cursor: pointer;
            color: #6b7280;
        }

        .modal-body {
            padding: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 16px;
            align-items: center;
        }

        .form-label {
            font-weight: 500;
            color: #4b5563;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem; /* 14px */
        }

        .form-input:focus {
            outline: none;
            border-color: #2d5a4c;
        }

        .radio-grid, .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .radio-item, .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .radio-item input, .checkbox-item input {
            width: 16px;
            height: 16px;
        }

        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
        }

        .form-textarea:focus {
            outline: none;
            border-color: #2d5a4c;
        }

        .upload-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .upload-btn {
            background: #2d5a4c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.875rem; /* 14px */
        }

        .form-actions {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #4b5563;
            border: none;
        }

        .btn-primary {
            background: #2d5a4c;
            color: white;
            border: none;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* View Entry Styles */
        .view-content {
            margin-bottom: 16px;
        }

        .view-label {
            font-weight: 500;
            color: #4b5563;
            margin-bottom: 4px;
        }

        .view-value {
            background: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .checkbox-tag {
            display: inline-block;
            background: #e5e7eb;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem; /* 14px */
            margin-right: 8px;
            margin-bottom: 8px;
        }

        /* Alert messages */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
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

        /* Floating Add Button */
        .floating-add-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #2d5a4c;
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

        .floating-add-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.3);
        }

        /* Floating Chat Button */
        .floating-chat-btn {
            position: fixed;
            bottom: 100px;
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
            bottom: 170px;
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
            bottom: 240px;
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

        .dark-mode .journal-container {
            background: #252525 !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2) !important;
        }

        .dark-mode .entry-card {
            background: #333 !important;
        }

        .dark-mode .form-input,
        .dark-mode .form-textarea,
        .dark-mode .view-value {
            background: #333 !important;
            color: #e0e0e0 !important;
            border-color: #444 !important;
        }

        .dark-mode .checkbox-tag {
            background: #444 !important;
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

            .entries-grid {
                grid-template-columns: 1fr;
            }

            .radio-grid, .checkbox-grid {
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
                <li class="active">
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
                <h2 class="welcome-title">Personal Journal</h2>
                <p class="welcome-subtitle">Record your daily experiences and track your progress</p>
            </div>

            <div class="journal-container">
                <!-- Display success/error messages -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        Journal entry saved successfully!
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['deleted'])): ?>
                    <div class="alert alert-info">
                        Journal entry deleted successfully.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="journal-header">
                    <h2>My Journal - Daily Logs</h2>
                    <p>Track your recovery journey and daily experiences</p>
                </div>

                <div class="controls">
                    <div class="filter-group">
                        <button class="filter-btn active">All</button>
                    </div>
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Search by date">
                        <button class="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                </div>

                <div class="entries-grid">
                    <?php foreach ($entries as $entry): ?>
                        <div class="entry-card">
                            <div class="entry-content">
                                <?php if ($entry['image_path']): ?>
                                    <img src="<?= htmlspecialchars($entry['image_path']) ?>" alt="Entry Image" class="entry-image">
                                <?php else: ?>
                                    <div class="entry-image" style="background: linear-gradient(135deg, #2d5a4c, #1e3a21); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                        No Image
                                    </div>
                                <?php endif; ?>
                                <div class="entry-overlay">
                                    <div class="entry-title"><?= htmlspecialchars($entry['title']) ?></div>
                                    <div class="entry-date"><?= htmlspecialchars(date('m/d/Y', strtotime($entry['date']))) ?></div>
                                    <div class="entry-actions">
                                        <a href="?view=<?= $entry['id'] ?>" class="action-btn">View</a>
                                        <a href="?delete=<?= $entry['id'] ?>" class="action-btn" onclick="return confirm('Are you sure you want to delete this entry?')">Delete</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Floating Add Button -->
    <div class="floating-add-btn" id="addButton">
        <i class="fas fa-plus"></i>
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

    <!-- Create Entry Modal -->
    <div class="modal" id="entryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create a Journal Entry</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-row">
                        <label class="form-label">Title:</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Mood Check:</label>
                        <div class="radio-grid">
                            <div class="radio-item">
                                <input type="radio" name="mood" value="Happy" id="mood_happy" required>
                                <label for="mood_happy">Happy</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="mood" value="Okay/Neutral" id="mood_neutral">
                                <label for="mood_neutral">Okay/Neutral</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" name="mood" value="Sad" id="mood_sad">
                                <label for="mood_sad">Sad</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Daily's Activity done:</label>
                        <div class="checkbox-grid">
                            <div class="checkbox-item">
                                <input type="checkbox" name="activity[]" value="Exercise" id="activity_exercise">
                                <label for="activity_exercise">Exercise</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="activity[]" value="Recreational" id="activity_rec">
                                <label for="activity_rec">Recreational</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="activity[]" value="Games" id="activity_games">
                                <label for="activity_games">Games</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Medication Tracker:</label>
                        <div class="checkbox-grid">
                            <div class="checkbox-item">
                                <input type="checkbox" name="medication[]" value="Morning" id="med_morning">
                                <label for="med_morning">Morning</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="medication[]" value="Afternoon" id="med_afternoon">
                                <label for="med_afternoon">Afternoon</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="medication[]" value="Evening" id="med_evening">
                                <label for="med_evening">Evening</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Sleep Quality:</label>
                        <div class="checkbox-grid">
                            <div class="checkbox-item">
                                <input type="checkbox" name="sleep[]" value="Good" id="sleep_good">
                                <label for="sleep_good">Good</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="sleep[]" value="Fair" id="sleep_fair">
                                <label for="sleep_fair">Fair</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" name="sleep[]" value="Poor" id="sleep_poor">
                                <label for="sleep_poor">Poor</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Symptoms today:</label>
                        <textarea name="symptoms" class="form-textarea" placeholder="e.g. 'Numbness in left arm,' 'Blurred vision,' 'Headache'"></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Mobility Check:</label>
                        <textarea name="mobility" class="form-textarea" placeholder="Were you able to walk/move independently today?"></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Speech Progress:</label>
                        <textarea name="speech" class="form-textarea" placeholder="'Practiced 5 words without stuttering'"></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Emotional Status:</label>
                        <textarea name="emotional" class="form-textarea" placeholder="'Felt proud after walking 10 steps'"></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Challenge Experience:</label>
                        <textarea name="challenge" class="form-textarea" placeholder="'Still weak grip in left hand'"></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Supports Received:</label>
                        <textarea name="supports" class="form-textarea" placeholder="e.g. 'My daughter helped me write today.'"></textarea>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Upload Image:</label>
                        <div class="upload-section">
                            <input type="file" name="image" id="imageUpload" style="display: none;" accept="image/*">
                            <button type="button" class="upload-btn" onclick="document.getElementById('imageUpload').click()">Choose File</button>
                            <span id="uploadStatus">No file chosen</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Other Thoughts:</label>
                        <textarea name="thoughts" class="form-textarea"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="save" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['view'])): ?>
    <div class="modal show" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Journal Entry Details</h3>
                <button class="close-btn" onclick="window.location.href='journal.php'">&times;</button>
            </div>
            
            <div class="modal-body">
                <?php if ($viewEntry): ?>
                    <div class="view-content">
                        <div class="view-label">Title:</div>
                        <div class="view-value"><?= htmlspecialchars($viewEntry['title']) ?></div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Date:</div>
                        <div class="view-value"><?= htmlspecialchars(date('F j, Y', strtotime($viewEntry['date']))) ?></div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Mood:</div>
                        <div class="view-value"><?= htmlspecialchars($viewEntry['mood']) ?></div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Activities:</div>
                        <div class="view-value">
                            <?php foreach (explode(', ', $viewEntry['activity']) as $activity): ?>
                                <?php if (!empty($activity)): ?>
                                    <span class="checkbox-tag"><?= htmlspecialchars($activity) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Medication Taken:</div>
                        <div class="view-value">
                            <?php foreach (explode(', ', $viewEntry['medication']) as $med): ?>
                                <?php if (!empty($med)): ?>
                                    <span class="checkbox-tag"><?= htmlspecialchars($med) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Sleep Quality:</div>
                        <div class="view-value">
                            <?php foreach (explode(', ', $viewEntry['sleep']) as $sleep): ?>
                                <?php if (!empty($sleep)): ?>
                                    <span class="checkbox-tag"><?= htmlspecialchars($sleep) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Symptoms:</div>
                        <div class="view-value"><?= nl2br(htmlspecialchars($viewEntry['symptoms'])) ?></div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Mobility:</div>
                        <div class="view-value"><?= nl2br(htmlspecialchars($viewEntry['mobility'])) ?></div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Speech Progress:</div>
                        <div class="view-value"><?= nl2br(htmlspecialchars($viewEntry['speech'])) ?></div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Emotional Status:</div>
                        <div class="view-value"><?= nl2br(htmlspecialchars($viewEntry['emotional'])) ?></div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Challenges:</div>
                        <div class="view-value"><?= nl2br(htmlspecialchars($viewEntry['challenge'])) ?></div>
                    </div>

                    <div class="view-content">
                        <div class="view-label">Supports Received:</div>
                        <div class="view-value"><?= nl2br(htmlspecialchars($viewEntry['supports'])) ?></div>
                    </div>

                    <?php if ($viewEntry['image_path']): ?>
                        <div class="view-content">
                            <div class="view-label">Image:</div>
                            <div class="view-value">
                                <img src="<?= htmlspecialchars($viewEntry['image_path']) ?>" style="max-width: 100%; border-radius: 6px;">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="view-content">
                        <div class="view-label">Thoughts:</div>
                        <div class="view-value"><?= nl2br(htmlspecialchars($viewEntry['thoughts'])) ?></div>
                    </div>
                <?php else: ?>
                    <div class="view-value">Entry not found</div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='journal.php'">Close</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

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

            // Add Button Functionality
            const addButton = document.getElementById('addButton');
            const entryModal = document.getElementById('entryModal');
            
            addButton.addEventListener('click', function() {
                entryModal.style.display = 'flex';
            });
            
            function closeModal() {
                entryModal.style.display = 'none';
            }
            
            window.addEventListener('click', function(event) {
                if (event.target === entryModal) {
                    entryModal.style.display = 'none';
                }
            });

            // Update file upload status
            document.getElementById('imageUpload').addEventListener('change', function() {
                const statusElement = document.getElementById('uploadStatus');
                if (this.files && this.files[0]) {
                    statusElement.textContent = this.files[0].name;
                } else {
                    statusElement.textContent = 'No file chosen';
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    entryModal.style.display = 'none';
                }
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