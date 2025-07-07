<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userData = getUserData($userId);

// Handle reminder update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_reminder'])) {
    $reminderId = (int)$_POST['reminder_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $time = trim($_POST['time']);
    $days = isset($_POST['days']) ? implode(',', $_POST['days']) : '';
    
    if (!empty($title) && !empty($time)) {
        $stmt = $pdo->prepare("UPDATE reminders SET 
                             title = ?, 
                             description = ?, 
                             reminder_time = ?, 
                             days_of_week = ? 
                             WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $description, $time, $days, $reminderId, $userId]);
        
        header("Location: see_all_reminders.php");
        exit;
    }
}

// Get reminder data to edit
$reminderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$reminder = null;

if ($reminderId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminderId, $userId]);
    $reminder = $stmt->fetch();
}

if (!$reminder) {
    header("Location: see_all_reminders.php");
    exit;
}

// Parse days of week
$selectedDays = $reminder['days_of_week'] ? explode(',', $reminder['days_of_week']) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeuroAid Health - Edit Reminder</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .edit-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .edit-title {
            color: #2d5a4c;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .btn-primary {
            background-color: #2d5a4c;
            border-color: #2d5a4c;
        }
        .btn-primary:hover {
            background-color: #1f3d34;
            border-color: #1f3d34;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .days-checkboxes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .day-checkbox {
            flex: 0 0 calc(14.28% - 10px);
        }
        @media (max-width: 768px) {
            .edit-container {
                padding: 20px;
            }
            .day-checkbox {
                flex: 0 0 calc(33.33% - 10px);
            }
        }
    </style>
</head>
<body>
    <div class="edit-container">
        <div class="edit-header">
            <h2 class="edit-title"><i class="fas fa-edit me-2"></i>Edit Reminder</h2>
        </div>
        
        <form method="POST" action="edit_reminders.php">
            <input type="hidden" name="reminder_id" value="<?php echo $reminderId; ?>">
            
            <div class="mb-4">
                <label for="title" class="form-label">Title *</label>
                <input type="text" class="form-control" id="title" name="title" 
                       value="<?php echo htmlspecialchars($reminder['title']); ?>" required>
            </div>
            
            <div class="mb-4">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea class="form-control" id="description" name="description" 
                          rows="3"><?php echo htmlspecialchars($reminder['description']); ?></textarea>
            </div>
            
            <div class="mb-4">
                <label for="time" class="form-label">Time *</label>
                <input type="time" class="form-control" id="time" name="time" 
                       value="<?php echo date('H:i', strtotime($reminder['reminder_time'])); ?>" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Repeat on (Select days)</label>
                <div class="days-checkboxes">
                    <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
                        <div class="form-check day-checkbox">
                            <input class="form-check-input" type="checkbox" name="days[]" 
                                   value="<?php echo $day; ?>" id="day-<?php echo $day; ?>"
                                   <?php echo in_array($day, $selectedDays) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="day-<?php echo $day; ?>">
                                <?php echo $day; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="see_all_reminders.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
                <button type="submit" name="update_reminder" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>