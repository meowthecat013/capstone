<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stroke_patient_system');
// AI Configuration
define('AI_MODE', 'api'); // 'offline' or 'api'
define('AI_API_KEY', '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'); // Leave empty if not using API
define('AI_API_URL', 'https://api.openai.com/v1/chat/completions');
define('AI_MODEL', 'gpt-3.5-turbo'); // Only used in API mode
// Start session
session_start();

// Create database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Set timezone
date_default_timezone_set('UTC');

// Include other helper files
require_once 'auth.php';


?>