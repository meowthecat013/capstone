<?php
// Database and session initialization
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stroke_patient_system');

// Create database connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create game sessions table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS game_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        game_name VARCHAR(50) NOT NULL,
        session_start DATETIME NOT NULL,
        session_end DATETIME,
        duration_seconds INT,
        level_reached INT NOT NULL,
        score INT NOT NULL,
        mode VARCHAR(20) NOT NULL,
        lives_left INT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Handle AJAX requests for saving game data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'error' => 'Not logged in']);
        exit;
    }
    
    try {
        switch ($_POST['action']) {
            case 'start_session':
                $stmt = $pdo->prepare("INSERT INTO game_sessions 
                    (user_id, game_name, session_start, level_reached, score, mode, lives_left) 
                    VALUES (?, ?, NOW(), ?, ?, ?, ?)");
                $stmt->execute([
                    getCurrentUserId(),
                    'Typing Game',
                    $_POST['level'],
                    $_POST['score'],
                    $_POST['mode'],
                    $_POST['lives']
                ]);
                $_SESSION['current_game_session'] = $pdo->lastInsertId();
                echo json_encode(['success' => true, 'session_id' => $_SESSION['current_game_session']]);
                break;
                
            case 'update_session':
                if (!isset($_SESSION['current_game_session'])) {
                    echo json_encode(['success' => false, 'error' => 'No active session']);
                    break;
                }
                
                $stmt = $pdo->prepare("UPDATE game_sessions SET 
                    level_reached = ?, 
                    score = ?,
                    lives_left = ?
                    WHERE id = ?");
                $stmt->execute([
                    $_POST['level'],
                    $_POST['score'],
                    $_POST['lives'],
                    $_SESSION['current_game_session']
                ]);
                echo json_encode(['success' => true]);
                break;
                
            case 'end_session':
                if (!isset($_SESSION['current_game_session'])) {
                    echo json_encode(['success' => false, 'error' => 'No active session']);
                    break;
                }
                
                $stmt = $pdo->prepare("UPDATE game_sessions SET 
                    session_end = NOW(),
                    duration_seconds = TIMESTAMPDIFF(SECOND, session_start, NOW()),
                    level_reached = ?,
                    score = ?,
                    lives_left = ?
                    WHERE id = ?");
                $stmt->execute([
                    $_POST['level'],
                    $_POST['score'],
                    $_POST['lives'],
                    $_SESSION['current_game_session']
                ]);
                unset($_SESSION['current_game_session']);
                echo json_encode(['success' => true]);
                break;
                
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Typing Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f0f0f0;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            text-align: center;
            color: #333;
        }
        
        .setup {
            text-align: center;
            margin-bottom: 30px;
        }
        
        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 5px;
            margin: 10px;
            width: 200px;
        }
        
        button {
            padding: 10px 20px;
            font-size: 16px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            color: white;
        }
        
        .btn-green { background-color: #28a745; }
        .btn-blue { background-color: #007bff; }
        .btn-red { background-color: #dc3545; }
        
        button:hover {
            opacity: 0.8;
        }
        
        .game-area {
            display: none;
            margin-top: 20px;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .text-to-type {
            background: #fff;
            border: 2px solid #007bff;
            padding: 20px;
            border-radius: 5px;
            font-size: 18px;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .typing-input {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            border: 2px solid #ddd;
            border-radius: 5px;
            resize: none;
        }
        
        .correct { background-color: lightgreen; }
        .incorrect { background-color: lightcoral; }
        .current { background-color: lightblue; }
        
        .hidden { display: none; }
        
        .results {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .login-notice {
            text-align: center;
            color: #dc3545;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 Simple Typing Game</h1>
        
        <?php if (!isLoggedIn()): ?>
            <div class="login-notice">
                Note: You're not logged in. Game data will not be saved.
            </div>
        <?php endif; ?>
        
        <!-- Setup Section -->
        <div id="setup" class="setup">
            <div>
                <input type="text" id="playerName" placeholder="Enter your name" />
            </div>
            <div>
                <button class="btn-green" onclick="startGame('word')">Words (Easy)</button>
                <button class="btn-blue" onclick="startGame('sentence')">Sentences (Medium)</button>
                <button class="btn-red" onclick="startGame('paragraph')">Paragraphs (Hard)</button>
            </div>
        </div>
        
        <!-- Game Section -->
        <div id="gameArea" class="game-area">
            <div class="stats">
                <div class="stat">
                    <div class="stat-number" id="timer">60</div>
                    <div>Time</div>
                </div>
                <div class="stat">
                    <div class="stat-number" id="lives">3</div>
                    <div>Lives</div>
                </div>
                <div class="stat">
                    <div class="stat-number" id="score">0</div>
                    <div>Score</div>
                </div>
                <div class="stat">
                    <div class="stat-number" id="level">1</div>
                    <div>Level</div>
                </div>
            </div>
            
            <div class="text-to-type" id="textDisplay">Text will appear here</div>
            
            <textarea id="typingInput" class="typing-input" placeholder="Start typing here..." rows="3"></textarea>
            
            <div style="text-align: center; margin-top: 15px;">
                <button class="btn-blue" onclick="newGame()">New Game</button>
                <button class="btn-red" onclick="pauseGame()" id="pauseBtn">Pause</button>
            </div>
        </div>
        
        <!-- Results Section -->
        <div id="results" class="results hidden">
            <h2 id="resultTitle">Game Over!</h2>
            <div id="resultStats"></div>
            <button class="btn-green" onclick="newGame()">Play Again</button>
        </div>
    </div>

    <script>
        // Simple game object
        let game = {
            player: '',
            mode: '',
            currentText: '',
            position: 0,
            time: 60,
            lives: 3,
            score: 0,
            level: 1,
            timer: null,
            paused: false,
            active: false,
            sessionId: null
        };

        // Sample texts
        const texts = {
            word: ['cat', 'dog', 'sun', 'tree', 'book', 'car', 'home', 'time', 'work', 'help'],
            sentence: [
                'The quick brown fox jumps.',
                'Practice makes perfect.',
                'Every day is a new chance.',
                'Typing improves coordination.',
                'Small steps lead to success.'
            ],
            paragraph: [
                'This is a simple paragraph for typing practice. It contains multiple sentences and helps improve your typing skills.',
                'Rehabilitation requires patience and consistent effort. Every small improvement counts toward your overall progress and recovery goals.'
            ]
        };

        // Function to save game data to server
        async function saveGameData(action, data = {}) {
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: action,
                        ...data
                    })
                });
                
                const result = await response.json();
                if (result.success && action === 'start_session') {
                    game.sessionId = result.session_id;
                }
                return result;
            } catch (error) {
                console.error('Error saving game data:', error);
                return {success: false, error: error.message};
            }
        }

        function startGame(mode) {
            console.log('Starting game with mode:', mode);
            
            const name = document.getElementById('playerName').value.trim();
            if (!name) {
                alert('Please enter your name first!');
                return;
            }

            // Set up game
            game.player = name;
            game.mode = mode;
            game.active = true;
            game.paused = false;
            game.position = 0;
            game.score = 0;
            game.level = 1;

            // Set lives and time based on mode
            if (mode === 'word') {
                game.lives = 3;
                game.time = 60;
            } else if (mode === 'sentence') {
                game.lives = 5;
                game.time = 90;
            } else {
                game.lives = 7;
                game.time = 120;
            }

            // Show game area
            document.getElementById('setup').style.display = 'none';
            document.getElementById('gameArea').style.display = 'block';
            document.getElementById('results').classList.add('hidden');

            // Load first text
            loadNewText();

            // Set up input
            const input = document.getElementById('typingInput');
            input.value = '';
            input.disabled = false;
            input.focus();

            // Start timer
            startTimer();

            // Update display
            updateDisplay();

            // Start game session in database if logged in
            <?php if (isLoggedIn()): ?>
                saveGameData('start_session', {
                    level: game.level,
                    score: game.score,
                    mode: mode,
                    lives: game.lives
                });
            <?php endif; ?>

            console.log('Game started successfully');
        }

        function loadNewText() {
            const textArray = texts[game.mode];
            game.currentText = textArray[Math.floor(Math.random() * textArray.length)];
            game.position = 0;
            
            console.log('New text loaded:', game.currentText);
            
            displayText();
            document.getElementById('typingInput').value = '';
        }

        function displayText() {
            const display = document.getElementById('textDisplay');
            const text = game.currentText;
            let html = '';

            for (let i = 0; i < text.length; i++) {
                let className = '';
                if (i < game.position) {
                    className = 'correct';
                } else if (i === game.position) {
                    className = 'current';
                }
                
                const char = text[i] === ' ' ? '&nbsp;' : text[i];
                html += `<span class="${className}">${char}</span>`;
            }

            display.innerHTML = html;
        }

        function startTimer() {
            game.timer = setInterval(() => {
                if (game.paused || !game.active) return;
                
                game.time--;
                updateDisplay();
                
                if (game.time <= 0) {
                    endGame();
                }
            }, 1000);
        }

        function updateDisplay() {
            document.getElementById('timer').textContent = game.time;
            document.getElementById('lives').textContent = game.lives;
            document.getElementById('score').textContent = game.score;
            document.getElementById('level').textContent = game.level;
            
            // Update session data in database if logged in
            <?php if (isLoggedIn()): ?>
                if (game.active && game.sessionId) {
                    saveGameData('update_session', {
                        level: game.level,
                        score: game.score,
                        lives: game.lives
                    });
                }
            <?php endif; ?>
        }

        function pauseGame() {
            game.paused = !game.paused;
            const btn = document.getElementById('pauseBtn');
            const input = document.getElementById('typingInput');
            
            if (game.paused) {
                btn.textContent = 'Resume';
                input.disabled = true;
            } else {
                btn.textContent = 'Pause';
                input.disabled = false;
                input.focus();
            }
        }

        function endGame() {
            game.active = false;
            clearInterval(game.timer);
            
            document.getElementById('gameArea').style.display = 'none';
            document.getElementById('results').classList.remove('hidden');
            
            const title = document.getElementById('resultTitle');
            const stats = document.getElementById('resultStats');
            
            if (game.lives > 0) {
                title.textContent = 'Time\'s Up!';
                title.style.color = 'orange';
            } else {
                title.textContent = 'Game Over!';
                title.style.color = 'red';
            }
            
            stats.innerHTML = `
                <p><strong>Player:</strong> ${game.player}</p>
                <p><strong>Mode:</strong> ${game.mode}</p>
                <p><strong>Final Score:</strong> ${game.score}</p>
                <p><strong>Level Reached:</strong> ${game.level}</p>
                <p><strong>Lives Left:</strong> ${game.lives}</p>
            `;
            
            // End game session in database if logged in
            <?php if (isLoggedIn()): ?>
                if (game.sessionId) {
                    saveGameData('end_session', {
                        level: game.level,
                        score: game.score,
                        lives: game.lives
                    });
                    game.sessionId = null;
                }
            <?php endif; ?>
        }

        function newGame() {
            // End current session if exists
            <?php if (isLoggedIn()): ?>
                if (game.sessionId && game.active) {
                    saveGameData('end_session', {
                        level: game.level,
                        score: game.score,
                        lives: game.lives
                    });
                }
            <?php endif; ?>
            
            // Reset everything
            if (game.timer) clearInterval(game.timer);
            
            game = {
                player: '',
                mode: '',
                currentText: '',
                position: 0,
                time: 60,
                lives: 3,
                score: 0,
                level: 1,
                timer: null,
                paused: false,
                active: false,
                sessionId: null
            };
            
            document.getElementById('setup').style.display = 'block';
            document.getElementById('gameArea').style.display = 'none';
            document.getElementById('results').classList.add('hidden');
            document.getElementById('playerName').focus();
        }

        // Handle typing
        document.getElementById('typingInput').addEventListener('input', function(e) {
            if (!game.active || game.paused) return;
            
            const typed = e.target.value;
            const expected = game.currentText.substring(0, typed.length);
            
            console.log('Typed:', typed, 'Expected:', expected);
            
            if (typed === expected) {
                game.position = typed.length;
                displayText();
                
                // Check if complete
                if (typed === game.currentText) {
                    console.log('Text completed!');
                    
                    // Add score
                    game.score += game.currentText.length * (game.mode === 'word' ? 10 : game.mode === 'sentence' ? 5 : 3);
                    game.level++;
                    
                    // Add bonus time
                    game.time += 5;
                    
                    // Load next text
                    setTimeout(() => {
                        loadNewText();
                        updateDisplay();
                    }, 500);
                }
            } else {
                console.log('Mistake detected');
                
                // Handle mistake
                if (game.mode === 'word') {
                    game.lives--;
                    if (game.lives <= 0) {
                        endGame();
                        return;
                    }
                    loadNewText();
                } else {
                    game.time = Math.max(0, game.time - 2);
                    if (game.time <= 0) {
                        endGame();
                        return;
                    }
                }
                
                // Reset input to correct portion
                e.target.value = game.currentText.substring(0, game.position);
                updateDisplay();
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, game ready');
            document.getElementById('playerName').focus();
        });

        // Allow Enter to start
        document.getElementById('playerName').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                startGame('word');
            }
        });
    </script>
</body>
</html>