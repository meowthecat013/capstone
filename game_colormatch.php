<?php
session_start();
require_once 'auth.php'; // Include your authentication functions

// Database configuration
$host = 'localhost';
$dbname = 'stroke_patient_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle AJAX requests
if (isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'check_color':
                    $response = checkColorSelection();
                    break;
                case 'reset_game':
                    endGameSession(false); // Mark current session as incomplete
             
                    session_start();
                    initializeGame();
                    $response = getGameState();
                    break;
                case 'get_new_color':
                    $response = getNewColor();
                    break;
                case 'initialize_game':
                    initializeGame();
                    $response = getGameState();
                    break;
                case 'update_session':
                    // For periodic session updates
                    $response = ['status' => 'success'];
                    break;
                default:
                    $response = ['status' => 'error', 'message' => 'Invalid action'];
            }
        }
    } catch(Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }
    
    echo json_encode($response);
    exit;
}

function checkColorSelection() {
    global $pdo;
    
    if (!isset($_SESSION['game_started'])) {
        initializeGame();
    }
    
    $selected = $_POST['selected_color'] ?? '';
    $current_color = $_SESSION['current_color'] ?? '';
    
    if (empty($current_color)) {
        $current_color = getRandomColor();
        $_SESSION['current_color'] = $current_color;
    }
    
    if ($selected === $current_color) {
        $_SESSION['score'] += 10;
        $_SESSION['level']++;
        
        // Update session periodically
        if ($_SESSION['level'] % 5 === 0) {
            updateGameSession();
        }
        
        return [
            'status' => 'correct',
            'message' => 'Correct! Well done!',
            'score' => $_SESSION['score'],
            'level' => $_SESSION['level'],
            'lives' => $_SESSION['lives']
        ];
    } else {
        $_SESSION['lives']--;
        
        if ($_SESSION['lives'] <= 0) {
            // Game over
            $final_data = [
                'score' => $_SESSION['score'],
                'level' => $_SESSION['level']
            ];
            
            endGameSession(false); // false = game over, not completed
    
            
            return [
                'status' => 'game_over',
                'message' => 'Game Over!',
                'final_score' => $final_data['score'],
                'final_level' => $final_data['level']
            ];
        }
        
        return [
            'status' => 'wrong',
            'message' => 'Wrong! The correct answer was ' . ucfirst($current_color),
            'correct_color' => $current_color,
            'score' => $_SESSION['score'],
            'level' => $_SESSION['level'],
            'lives' => $_SESSION['lives']
        ];
    }
}

function initializeGame() {
    $_SESSION['lives'] = 5;
    $_SESSION['score'] = 0;
    $_SESSION['level'] = 1;
    $_SESSION['game_started'] = true;
    $_SESSION['start_time'] = time();
    $_SESSION['current_color'] = getRandomColor();
    
    // Start a new game session in database
    startGameSession();
}

function startGameSession() {
    global $pdo;
    
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    $stmt = $pdo->prepare("INSERT INTO colormatch_game_sessions 
        (user_id, game_name, session_start, level_reached, final_score) 
        VALUES (?, ?, NOW(), ?, ?)");
    
    $stmt->execute([
        $userId,
        'Color Match',
        1, // Starting level
        0  // Starting score
    ]);
    
    $_SESSION['session_id'] = $pdo->lastInsertId();
}

function updateGameSession() {
    global $pdo;
    
    if (!isset($_SESSION['session_id'])) return;
    
    $stmt = $pdo->prepare("UPDATE colormatch_game_sessions 
        SET level_reached = ?, final_score = ?
        WHERE id = ?");
    
    $stmt->execute([
        $_SESSION['level'],
        $_SESSION['score'],
        $_SESSION['session_id']
    ]);
}

function endGameSession($completed) {
    global $pdo;
    
    if (!isset($_SESSION['session_id']) || !isset($_SESSION['start_time'])) return;
    
    $duration = time() - $_SESSION['start_time'];
    
    $stmt = $pdo->prepare("UPDATE colormatch_game_sessions 
        SET session_end = NOW(),
            session_duration = ?,
            level_reached = ?,
            final_score = ?,
            completed = ?
        WHERE id = ?");
    
    $stmt->execute([
        $duration,
        $_SESSION['level'] ?? 1,
        $_SESSION['score'] ?? 0,
        $completed ? 1 : 0,
        $_SESSION['session_id']
    ]);
}

function getRandomColor() {
    $colors = ['red', 'blue', 'green', 'yellow'];
    return $colors[array_rand($colors)];
}

function getNewColor() {
    if (!isset($_SESSION['game_started'])) {
        initializeGame();
    }
    
    $new_color = getRandomColor();
    $_SESSION['current_color'] = $new_color;
    
    return getGameState();
}

function getGameState() {
    return [
        'status' => 'success',
        'new_color' => $_SESSION['current_color'],
        'score' => $_SESSION['score'],
        'level' => $_SESSION['level'],
        'lives' => $_SESSION['lives']
    ];
}

// Initialize game if not started
if (!isset($_SESSION['game_started'])) {
    initializeGame();
}

$current_color = $_SESSION['current_color'] ?? 'red';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Identification Game</title>
    <style>
        /* Main Layout - Copied from page 1 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #2d5a4c 0%, #2d5a4c 35%, white 35.05%, white 100%);
            min-height: 100vh;
            color: #374151; /* Text color from page 1 */
        }

        /* Content Area - Copied from page 1 */
        .content-area {
            flex: 1;
            padding: 10px;
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Welcome Section - Copied from page 1 */
        .welcome-section {
            text-align: right;
            margin-right: 100px;
            margin-bottom: 40px;
            padding: 24px 0;
        }

        .welcome-title {
            font-size: 32px;
            color: #2d5a4c; /* Primary green color from page 1 */
            font-weight: 300;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Card Styles - Copied from page 1 */
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
            margin-bottom: 24px;
        }

        .card-content {
            padding: 5px;
        }

        .section-title {
            font-weight: bold;
            color: #2d5a4c; /* Primary green color from page 1 */
            margin-bottom: 12px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Game specific styles - modified to match page 1 colors */
        .game-container {
            background: white;
            border-radius: 16px;
            padding: 5px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 90%;
            margin: 40px auto;
        }

        h1 {
            font-size: 2em;
            margin-bottom: 5px;
            color: #2d5a4c; /* Primary green color from page 1 */
            font-weight: 300;
        }

        .game-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 5px;
            font-size: 1.2em;
        }

        .stat {
            background: rgba(229, 231, 235, 0.5); /* Light gray from page 1 */
            padding: 10px 20px;
            border-radius: 10px;
        }

        .instruction {
            font-size: 1em;
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(229, 231, 235, 0.5); /* Light gray from page 1 */
            border-radius: 15px;
        }

        .color-label {
            font-size: 3em;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 30px;
            color: #2d5a4c; /* Primary green color from page 1 */
            animation: pulse 2s infinite;
            transition: all 0.3s ease;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .color-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 10px;
        }

        .color-box {
            width: 120px;
            height: 120px;
            border-radius: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 0 auto;
            position: relative;
        }

        .color-box:hover:not(:disabled) {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
        }

        .color-box:active:not(:disabled) {
            transform: scale(0.95);
        }

        .color-box:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .red { background-color: #e63946; } /* Matching red from page 1 chart */
        .blue { background-color: #457b9d; } /* Matching blue from page 1 chart */
        .green { background-color: #2a9d8f; } /* Matching green from page 1 chart */
        .yellow { background-color: #f4a261; } /* Matching orange from page 1 chart */

        .lives-display {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #374151; /* Text color from page 1 */
        }

        .heart {
            color: #e63946; /* Matching red from page 1 */
            font-size: 1.2em;
        }

        .reset-btn {
            background: #2d5a4c; /* Primary green color from page 1 */
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .reset-btn:hover {
            background: #1e3c30; /* Darker green */
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Modal Styles - updated to match page 1 colors */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 15% auto;
            padding: 40px;
            border-radius: 16px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            color: #374151;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal h2 {
            margin-bottom: 20px;
            font-size: 2em;
            color: #2d5a4c; /* Primary green color from page 1 */
        }

        .modal p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .loading-spinner {
            border: 4px solid rgba(45, 90, 76, 0.3); /* Primary green color from page 1 */
            border-radius: 50%;
            border-top: 4px solid #2d5a4c;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .modal-buttons {
            margin-top: 20px;
        }

        .modal-btn {
            background: #2d5a4c; /* Primary green color from page 1 */
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1em;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .modal-btn:hover {
            background: #1e3c30; /* Darker green */
        }

        .correct-modal {
            border-top: 8px solid #2a9d8f; /* Matching green from page 1 */
        }

        .wrong-modal {
            border-top: 8px solid #e63946; /* Matching red from page 1 */
        }

        .game-over-modal {
            border-top: 8px solid #2d5a4c; /* Primary green color from page 1 */
        }

        .disabled-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            display: none;
        }

        .loading-game {
            pointer-events: none;
            opacity: 0.7;
        }

        .error-message {
            background: rgba(230, 57, 70, 0.9); /* Matching red from page 1 */
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <!-- Main Container - Copied structure from page 1 -->
    <div class="main-container">
        <!-- Content Area -->
        <main class="content-area">
            <!-- Welcome Section -->
        

            <!-- Game Card - using card styles from page 1 -->
            <div class="card">
                <div class="card-content">
                    <div class="game-container" id="gameContainer">
                        <h1>🎯 Color Match Challenge</h1>
                        
                        <div class="error-message" id="errorMessage"></div>
                        
                        <div class="game-stats">
                            <div class="stat">
                                <strong>Score:</strong> <span id="score"><?php echo $_SESSION['score']; ?></span>
                            </div>
                            <div class="stat">
                                <strong>Level:</strong> <span id="level"><?php echo $_SESSION['level']; ?></span>
                            </div>
                        </div>

                        <div class="lives-display" id="livesDisplay">
                            <!-- Lives will be updated by JavaScript -->
                        </div>

                        <div class="instruction">
                            Click the color that matches the label below:
                        </div>

                        <div class="color-label" id="colorLabel">
                            <?php echo strtoupper($current_color); ?>
                        </div>

                        <div class="color-grid" id="colorGrid">
                            <button class="color-box red" data-color="red" onclick="selectColor('red')">
                                <div class="disabled-overlay"></div>
                            </button>
                            <button class="color-box blue" data-color="blue" onclick="selectColor('blue')">
                                <div class="disabled-overlay"></div>
                            </button>
                            <button class="color-box green" data-color="green" onclick="selectColor('green')">
                                <div class="disabled-overlay"></div>
                            </button>
                            <button class="color-box yellow" data-color="yellow" onclick="selectColor('yellow')">
                                <div class="disabled-overlay"></div>
                            </button>
                        </div>

                        <button onclick="resetGame()" class="reset-btn">🔄 Reset Game</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal -->
    <div id="gameModal" class="modal">
        <div class="modal-content" id="modalContent">
            <h2 id="modalTitle"></h2>
            <p id="modalMessage"></p>
            <div class="loading-spinner" id="loadingSpinner" style="display: none;"></div>
            <div class="modal-buttons" id="modalButtons"></div>
        </div>
    </div>

    <script>
        let gameActive = true;

        // Initialize game on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Double-check game initialization
            initializeGameIfNeeded();
            updateLivesDisplay(<?php echo $_SESSION['lives']; ?>);
        });

        function initializeGameIfNeeded() {
            // Check if we have a valid color displayed
            const colorLabel = document.getElementById('colorLabel').textContent;
            if (!colorLabel || colorLabel.trim() === '') {
                // Force initialization via AJAX
                const formData = new FormData();
                formData.append('ajax', 'true');
                formData.append('action', 'initialize_game');

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('colorLabel').textContent = data.new_color.toUpperCase();
                        document.getElementById('score').textContent = data.score;
                        document.getElementById('level').textContent = data.level;
                        updateLivesDisplay(data.lives);
                    }
                })
                .catch(error => {
                    console.error('Error initializing game:', error);
                    showError('Failed to initialize game. Please refresh the page.');
                });
            }
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }

        function updateLivesDisplay(lives) {
            const livesDisplay = document.getElementById('livesDisplay');
            let heartsHtml = '<strong>Lives:</strong> ';
            
            for (let i = 0; i < lives; i++) {
                heartsHtml += '<span class="heart">❤️</span>';
            }
            for (let i = lives; i < 5; i++) {
                heartsHtml += '<span style="opacity: 0.3;">🖤</span>';
            }
            
            livesDisplay.innerHTML = heartsHtml;
        }

        function selectColor(color) {
            if (!gameActive) return;
            
            // Disable all buttons and show loading state
            setGameLoading(true);
            
            // Add click animation
            const clickedButton = document.querySelector(`[data-color="${color}"]`);
            clickedButton.style.transform = 'scale(0.9)';
            setTimeout(() => {
                clickedButton.style.transform = 'scale(1)';
            }, 150);

            // Send AJAX request
            const formData = new FormData();
            formData.append('ajax', 'true');
            formData.append('action', 'check_color');
            formData.append('selected_color', color);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                handleGameResponse(data);
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Network error. Please try again.');
                setGameLoading(false);
            });
        }

        function handleGameResponse(data) {
            // Update game stats
            if (data.score !== undefined) {
                document.getElementById('score').textContent = data.score;
            }
            if (data.level !== undefined) {
                document.getElementById('level').textContent = data.level;
            }
            if (data.lives !== undefined) {
                updateLivesDisplay(data.lives);
            }

            if (data.status === 'correct') {
                showModal('Correct! 🎉', data.message, 'correct-modal', () => {
                    loadNextLevel();
                });
            } else if (data.status === 'wrong') {
                showModal('Wrong! ❌', data.message, 'wrong-modal', () => {
                    setGameLoading(false);
                });
            } else if (data.status === 'game_over') {
                showGameOverModal(data.final_score, data.final_level);
            }
        }

        function showModal(title, message, className, callback) {
            const modal = document.getElementById('gameModal');
            const modalContent = document.getElementById('modalContent');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalButtons = document.getElementById('modalButtons');
            const spinner = document.getElementById('loadingSpinner');

            modalTitle.textContent = title;
            modalMessage.textContent = message;
            modalContent.className = `modal-content ${className}`;
            spinner.style.display = 'none';
            modalButtons.innerHTML = '';

            modal.style.display = 'block';

            // Auto close after 2 seconds
            setTimeout(() => {
                modal.style.display = 'none';
                if (callback) callback();
            }, 2000);
        }

        function showGameOverModal(finalScore, finalLevel) {
            const modal = document.getElementById('gameModal');
            const modalContent = document.getElementById('modalContent');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');
            const modalButtons = document.getElementById('modalButtons');
            const spinner = document.getElementById('loadingSpinner');

            modalTitle.textContent = '🎮 Game Over!';
            modalMessage.innerHTML = `
                <strong>Final Score:</strong> ${finalScore} points<br>
                <strong>Level Reached:</strong> ${finalLevel}<br>
                Your progress has been saved!
            `;
            modalContent.className = 'modal-content game-over-modal';
            spinner.style.display = 'none';
            
            modalButtons.innerHTML = `
                <button class="modal-btn" onclick="playAgain()">🔄 Play Again</button>
            `;

            modal.style.display = 'block';
            gameActive = false;
        }

        function loadNextLevel() {
            const modalMessage = document.getElementById('modalMessage');
            const spinner = document.getElementById('loadingSpinner');
            
            modalMessage.textContent = 'Loading next level...';
            spinner.style.display = 'block';

            // Get new color
            const formData = new FormData();
            formData.append('ajax', 'true');
            formData.append('action', 'get_new_color');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('colorLabel').textContent = data.new_color.toUpperCase();
                    
                    // Hide modal and enable game
                    setTimeout(() => {
                        document.getElementById('gameModal').style.display = 'none';
                        setGameLoading(false);
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to load next level. Please try again.');
                setGameLoading(false);
            });
        }

        function setGameLoading(loading) {
            const colorBoxes = document.querySelectorAll('.color-box');
            const gameContainer = document.getElementById('gameContainer');
            
            if (loading) {
                gameActive = false;
                gameContainer.classList.add('loading-game');
                colorBoxes.forEach(box => {
                    box.disabled = true;
                    box.querySelector('.disabled-overlay').style.display = 'block';
                });
            } else {
                gameActive = true;
                gameContainer.classList.remove('loading-game');
                colorBoxes.forEach(box => {
                    box.disabled = false;
                    box.querySelector('.disabled-overlay').style.display = 'none';
                });
            }
        }

        function resetGame() {
            setGameLoading(true);
            
            const formData = new FormData();
            formData.append('ajax', 'true');
            formData.append('action', 'reset_game');

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update UI with new game data
                    document.getElementById('colorLabel').textContent = data.new_color.toUpperCase();
                    document.getElementById('score').textContent = data.score;
                    document.getElementById('level').textContent = data.level;
                    updateLivesDisplay(data.lives);
                    setGameLoading(false);
                    
                    // Hide modal if open
                    document.getElementById('gameModal').style.display = 'none';
                } else {
                    location.reload(); // Fallback
                }
            })
            .catch(error => {
                console.error('Error:', error);
                location.reload(); // Fallback
            });
        }

        function playAgain() {
            resetGame();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('gameModal');
            if (event.target === modal) {
                modal.style.display = 'none';
                setGameLoading(false);
            }
        }
    </script>
</body>
</html>