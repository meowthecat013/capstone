<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Music System Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Music System Diagnostic</h1>
    
    <?php
    echo "<div class='section'>";
    echo "<h2>1. Database Check</h2>";
    
    try {
        // Check if music_library table exists and has data
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM music_library");
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            echo "<p class='success'>✓ Database connection working</p>";
            echo "<p class='success'>✓ Found {$result['count']} songs in music_library</p>";
            
            // Show first few songs
            $stmt = $pdo->query("SELECT * FROM music_library LIMIT 5");
            $songs = $stmt->fetchAll();
            
            echo "<h3>Sample Songs:</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Title</th><th>Artist</th><th>File Path</th><th>Category</th></tr>";
            foreach ($songs as $song) {
                echo "<tr>";
                echo "<td>{$song['id']}</td>";
                echo "<td>" . htmlspecialchars($song['title']) . "</td>";
                echo "<td>" . htmlspecialchars($song['artist']) . "</td>";
                echo "<td>" . htmlspecialchars($song['file_path']) . "</td>";
                echo "<td>" . htmlspecialchars($song['category']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>⚠ No songs found in music_library table</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Database error: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>2. File System Check</h2>";
    
    $musicDir = __DIR__ . '/music/';
    echo "<p>Music directory path: <code>$musicDir</code></p>";
    
    if (is_dir($musicDir)) {
        echo "<p class='success'>✓ Music directory exists</p>";
        
        if (is_readable($musicDir)) {
            echo "<p class='success'>✓ Music directory is readable</p>";
            
            $files = scandir($musicDir);
            $audioFiles = array_filter($files, function($file) use ($musicDir) {
                if (in_array($file, ['.', '..'])) return false;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                return in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac']);
            });
            
            if (count($audioFiles) > 0) {
                echo "<p class='success'>✓ Found " . count($audioFiles) . " audio files</p>";
                echo "<h3>Audio Files Found:</h3>";
                echo "<ul>";
                foreach ($audioFiles as $file) {
                    $fullPath = $musicDir . $file;
                    $size = filesize($fullPath);
                    $readable = is_readable($fullPath) ? '✓' : '✗';
                    echo "<li>$readable $file (" . round($size/1024, 2) . " KB)</li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='error'>✗ No audio files found in music directory</p>";
            }
        } else {
            echo "<p class='error'>✗ Music directory is not readable</p>";
        }
    } else {
        echo "<p class='error'>✗ Music directory does not exist</p>";
        echo "<p>You need to create the directory: <code>$musicDir</code></p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>3. Web Path Check</h2>";
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $webMusicPath = $protocol . '://' . $host . '/music/';
    
    echo "<p>Web music path: <code>$webMusicPath</code></p>";
    
    // Test if web path is accessible
    if (is_dir($musicDir)) {
        $testFiles = array_slice(scandir($musicDir), 0, 3);
        foreach ($testFiles as $file) {
            if (!in_array($file, ['.', '..'])) {
                $testUrl = $webMusicPath . $file;
                echo "<p>Test URL: <a href='$testUrl' target='_blank'>$testUrl</a></p>";
                break;
            }
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>4. Database vs File System Comparison</h2>";
    
    try {
        $stmt = $pdo->query("SELECT * FROM music_library");
        $dbSongs = $stmt->fetchAll();
        
        $missingFiles = [];
        $foundFiles = [];
        
        foreach ($dbSongs as $song) {
            $fileName = basename($song['file_path']);
            $fullPath = $musicDir . $fileName;
            
            if (file_exists($fullPath)) {
                $foundFiles[] = [
                    'song' => $song,
                    'file' => $fileName,
                    'size' => filesize($fullPath)
                ];
            } else {
                $missingFiles[] = [
                    'song' => $song,
                    'expected_file' => $fileName
                ];
            }
        }
        
        if (count($foundFiles) > 0) {
            echo "<h3 class='success'>Files Found (" . count($foundFiles) . "):</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Title</th><th>File</th><th>Size</th></tr>";
            foreach ($foundFiles as $item) {
                echo "<tr>";
                echo "<td>{$item['song']['id']}</td>";
                echo "<td>" . htmlspecialchars($item['song']['title']) . "</td>";
                echo "<td>{$item['file']}</td>";
                echo "<td>" . round($item['size']/1024, 2) . " KB</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
        if (count($missingFiles) > 0) {
            echo "<h3 class='error'>Missing Files (" . count($missingFiles) . "):</h3>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Title</th><th>Expected File</th></tr>";
            foreach ($missingFiles as $item) {
                echo "<tr>";
                echo "<td>{$item['song']['id']}</td>";
                echo "<td>" . htmlspecialchars($item['song']['title']) . "</td>";
                echo "<td>{$item['expected_file']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>Error comparing database and files: " . $e->getMessage() . "</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>5. Server Configuration</h2>";
    
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
    echo "<p><strong>Current Script:</strong> " . __FILE__ . "</p>";
    echo "<p><strong>Music Directory:</strong> " . $musicDir . "</p>";
    
    // Check important PHP functions
    $functions = ['mime_content_type', 'filesize', 'is_readable', 'pathinfo'];
    echo "<h3>PHP Functions:</h3>";
    foreach ($functions as $func) {
        $available = function_exists($func) ? '✓' : '✗';
        echo "<p>$available $func()</p>";
    }
    
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>6. Quick Test</h2>";
    
    if (count($foundFiles) > 0) {
        $testSong = $foundFiles[0]['song'];
        echo "<p>Testing with song: " . htmlspecialchars($testSong['title']) . "</p>";
        echo "<p><a href='music.php?song_id={$testSong['id']}' target='_blank'>Test music.php for song ID {$testSong['id']}</a></p>";
        
        echo "<h3>Sample Audio Player:</h3>";
        $testFile = 'music/' . basename($testSong['file_path']);
        echo "<audio controls>";
        echo "<source src='$testFile' type='audio/mpeg'>";
        echo "Your browser does not support the audio element.";
        echo "</audio>";
    } else {
        echo "<p class='warning'>Cannot perform test - no matching files found</p>";
    }
    
    echo "</div>";
    ?>
    
    <div class='section'>
        <h2>7. Recommendations</h2>
        <?php
        $recommendations = [];
        
        if (!is_dir($musicDir)) {
            $recommendations[] = "Create the music directory: mkdir " . $musicDir;
        }
        
        if (count($missingFiles) > 0) {
            $recommendations[] = "Upload missing music files to the music directory";
            $recommendations[] = "Or update the file_path in the database to match actual filenames";
        }
        
        if (count($foundFiles) == 0) {
            $recommendations[] = "Add some music files to test with";
            $recommendations[] = "Insert corresponding records in the music_library table";
        }
        
        if (count($recommendations) > 0) {
            echo "<ul>";
            foreach ($recommendations as $rec) {
                echo "<li>$rec</li>";
            }
            echo "</ul>";
        } else {
            echo "<p class='success'>✓ Everything looks good! Your music system should be working.</p>";
        }
        ?>
    </div>
</body>
</html>