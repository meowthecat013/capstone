<?php
// admin_ai_settings.php
require_once 'config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['ai_mode'] === 'api' ? 'api' : 'offline';
    $apiKey = trim($_POST['api_key']);
    
    // Update config file
    $configContent = file_get_contents('config.php');
    $configContent = preg_replace(
        "/define\('AI_MODE', '.*?'\);/",
        "define('AI_MODE', '$mode');",
        $configContent
    );
    $configContent = preg_replace(
        "/define\('AI_API_KEY', '.*?'\);/",
        "define('AI_API_KEY', '$apiKey');",
        $configContent
    );
    file_put_contents('config.php', $configContent);
    
    $_SESSION['success'] = "AI settings updated successfully";
    header("Location: admin_ai_settings.php");
    exit;
}

$currentMode = defined('AI_MODE') ? AI_MODE : 'offline';
$currentApiKey = defined('AI_API_KEY') ? AI_API_KEY : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Settings</title>
</head>
<body>
    <h1>AI Chat Configuration</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div style="color: green;"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>
                <input type="radio" name="ai_mode" value="offline" <?= $currentMode === 'offline' ? 'checked' : '' ?>>
                Offline Mode
            </label>
            <label>
                <input type="radio" name="ai_mode" value="api" <?= $currentMode === 'api' ? 'checked' : '' ?>>
                API Mode
            </label>
        </div>
        
        <div>
            <label>API Key:</label>
            <input type="password" name="api_key" value="<?= htmlspecialchars($currentApiKey) ?>" placeholder="Leave empty to disable API">
        </div>
        
        <button type="submit">Save Settings</button>
    </form>
    
    <div style="margin-top: 20px;">
        <h3>Current Status:</h3>
        <p><strong>Mode:</strong> <?= strtoupper($currentMode) ?></p>
        <p><strong>API Key:</strong> <?= $currentApiKey ? 'Configured' : 'Not Configured' ?></p>
    </div>
</body>
</html>