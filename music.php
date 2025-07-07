<?php
require_once 'config.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['song_id'])) {
    echo json_encode(['error' => 'Song ID required']);
    exit;
}

$songId = (int)$_GET['song_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM music_library WHERE id = ?");
    $stmt->execute([$songId]);
    $song = $stmt->fetch();
    
    if (!$song) {
        echo json_encode(['error' => 'Song not found in database']);
        exit;
    }
    
    // Debug: Log what we found
    error_log("Found song: " . print_r($song, true));
    
    // Determine the correct music directory path
    $musicDir = __DIR__ . '/music/';
    
    // Check if directory exists
    if (!is_dir($musicDir)) {
        error_log("Music directory does not exist: " . $musicDir);
        echo json_encode(['error' => 'Music directory not found', 'path' => $musicDir]);
        exit;
    }
    
    // Get the filename from the database
    $fileName = basename($song['file_path']);
    $fullPath = $musicDir . $fileName;
    
    // Debug info
    $debugInfo = [
        'song_from_db' => $song,
        'music_dir' => $musicDir,
        'file_name' => $fileName,
        'full_path' => $fullPath,
        'directory_exists' => is_dir($musicDir),
        'file_exists' => file_exists($fullPath),
        'is_readable' => file_exists($fullPath) ? is_readable($fullPath) : false
    ];
    
    // Check if file exists
    if (!file_exists($fullPath)) {
        error_log("Music file does not exist: " . $fullPath);
        
        // Try to list files in the directory for debugging
        $files = scandir($musicDir);
        $audioFiles = array_filter($files, function($file) use ($musicDir) {
            if (in_array($file, ['.', '..'])) return false;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac']);
        });
        
        echo json_encode([
            'error' => 'Music file not found',
            'debug_info' => $debugInfo,
            'available_audio_files' => array_values($audioFiles),
            'suggestion' => 'Check if the file_path in database matches actual filenames'
        ]);
        exit;
    }
    
    // Check if file is readable
    if (!is_readable($fullPath)) {
        error_log("Music file is not readable: " . $fullPath);
        echo json_encode([
            'error' => 'Music file is not readable',
            'debug_info' => $debugInfo,
            'file_permissions' => substr(sprintf('%o', fileperms($fullPath)), -4)
        ]);
        exit;
    }
    
    // Get file info for additional validation
    $fileInfo = pathinfo($fullPath);
    $allowedExtensions = ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'];
    
    if (!in_array(strtolower($fileInfo['extension']), $allowedExtensions)) {
        echo json_encode([
            'error' => 'Unsupported file format: ' . $fileInfo['extension'],
            'supported_formats' => $allowedExtensions
        ]);
        exit;
    }
    
    // Create the web-accessible URL
    // Make sure this matches your web server configuration
    $webPath = 'music/' . $fileName;
    
    // Check if the web path is accessible by making a test request
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $fullWebUrl = $protocol . '://' . $host . '/' . $webPath;
    
    // Add CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Get MIME type
    $mimeType = 'audio/mpeg'; // default
    if (function_exists('mime_content_type')) {
        $detectedMime = mime_content_type($fullPath);
        if ($detectedMime) {
            $mimeType = $detectedMime;
        }
    } else {
        // Fallback MIME type detection
        $ext = strtolower($fileInfo['extension']);
        $mimeTypes = [
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac'
        ];
        $mimeType = $mimeTypes[$ext] ?? 'audio/mpeg';
    }
    
    // Return success response
    $response = [
        'success' => true,
        'id' => $song['id'],
        'title' => $song['title'],
        'artist' => $song['artist'],
        'file_path' => $song['file_path'],
        'playable_url' => $webPath,
        'full_web_url' => $fullWebUrl,
        'file_size' => filesize($fullPath),
        'file_extension' => $fileInfo['extension'],
        'mime_type' => $mimeType,
        'debug_info' => $debugInfo
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>