<?php
session_start();

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

// Handle game session save
if (($_POST['action'] ?? '') === 'save_session') {
    $player_id = $_POST['player_id'] ?? 'guest';
    $level_reached = (int)($_POST['level_reached'] ?? 1);
    $final_score = (int)($_POST['final_score'] ?? 0);
    $session_time = $_POST['session_time'] ?? '00:00';
    $images_completed = (int)($_POST['images_completed'] ?? 0);
    $accuracy = (float)($_POST['accuracy'] ?? 0);
    $highest_streak = (int)($_POST['highest_streak'] ?? 0);
    $completed_levels = (int)($_POST['completed_levels'] ?? 0);
    
    // Calculate session duration in seconds
    $timeParts = explode(':', $session_time);
    $session_seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + ($timeParts[2] ?? 0);
    
    // Prepare session data for JSON storage
    $session_data = [
        'level_progression' => $level_reached,
        'final_score' => $final_score,
        'accuracy' => $accuracy,
        'session_time' => $session_time,
        'images_completed' => $images_completed,
        'highest_streak' => $highest_streak,
        'completed_levels' => $completed_levels,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Get user ID if logged in
    $user_id = null;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    $stmt = $pdo->prepare("INSERT INTO image_recognition_game_sessions 
        (user_id, session_end, session_duration, level_reached, final_score, 
         images_completed, accuracy, highest_streak, completed_levels, session_data) 
        VALUES (?, NOW(), SEC_TO_TIME(?), ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $user_id,
        $session_seconds,
        $level_reached,
        $final_score,
        $images_completed,
        $accuracy,
        $highest_streak,
        $completed_levels,
        json_encode($session_data)
    ]);
    
    echo json_encode(['success' => true, 'session_id' => $pdo->lastInsertId()]);
    exit;
}

// Get user info if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Recognition Game - Stroke Rehabilitation</title>
    <style>
        /* Main Layout - Copied from page 1 */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
             background: linear-gradient(to right, #2d5a4c 0%, #2d5a4c 35%, white 35.05%, white 100%);
            min-height: 100vh;
            color: #374151; /* Text color from page 1 */
        }

        /* Main Container - Copied from page 1 */
        .main-container {
            display: flex;
            max-width: 1800px;
            margin: 0 auto;
            min-height: calc(100vh - 140px);
        }

        /* Content Area - Copied from page 1 */
        .content-area {
            flex: 1;
            padding: 32px;
            position: relative;
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
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 800px;
            width: 90%;
            margin: 0 auto;
        }

        .title {
            font-size: 1.5em;
            color: #2d5a4c; /* Primary green color from page 1 */
            margin-bottom: 5px;
            font-weight: 300;
        }

        .user-info {
            background: rgba(229, 231, 235, 0.5); /* Light gray from page 1 */
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
        }

        .game-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 5px;
            flex-wrap: wrap;
        }

        .stat {
            background: rgba(229, 231, 235, 0.5); /* Light gray from page 1 */
            padding: 15px;
            border-radius: 10px;
            margin: 5px;
            flex: 1;
            min-width: 60px;
        }

        .stat-value {
            font-size: 1em;
            font-weight: bold;
            color: #2d5a4c; /* Primary green color from page 1 */
        }

        .stat-label {
            font-size: 0.9em;
            color: #6b7280; /* Gray text from page 1 */
            margin-top: 5px;
        }

        .image-display {
            margin: 30px 0;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(229, 231, 235, 0.5); /* Light gray from page 1 */
            border-radius: 15px;
            border: 1px solid rgba(229, 231, 235, 0.5);
            position: relative;
            overflow: hidden;
        }

        .image-display img {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
        }

        .image-placeholder {
            font-size: 1.2em;
            color: #6b7280; /* Gray text from page 1 */
            padding: 20px;
        }

        .input-container {
            margin: 30px 0;
        }

        .game-input {
            font-size: 1.2em;
            padding: 15px;
            border: 1px solid rgba(229, 231, 235, 0.5);
            border-radius: 10px;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
            background: white;
        }

        .game-input:focus {
            outline: none;
            border-color: #2d5a4c; /* Primary green color from page 1 */
            box-shadow: 0 0 10px rgba(45, 90, 76, 0.1);
        }

        .game-input.correct {
            border-color: #2a9d8f; /* Matching green from page 1 */
            background-color: rgba(42, 157, 143, 0.1);
        }

        .game-input.incorrect {
            border-color: #e63946; /* Matching red from page 1 */
            background-color: rgba(230, 57, 70, 0.1);
        }

        .controls {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 20px 0;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            min-width: 120px;
        }

        .btn-primary {
            background: #2d5a4c; /* Primary green color from page 1 */
            color: white;
        }

        .btn-secondary {
            background: #6b7280; /* Gray from page 1 */
            color: white;
        }

        .btn-success {
            background: #2a9d8f; /* Matching green from page 1 */
            color: white;
        }

        .btn-warning {
            background: #f4a261; /* Matching orange from page 1 */
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .progress-container {
            margin: 20px 0;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: rgba(229, 231, 235, 0.5);
            border-radius: 5px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #2d5a4c; /* Primary green color from page 1 */
            transition: width 0.3s ease;
        }

        .timer {
            font-size: 1.1em;
            margin: 10px 0;
            color: #e63946; /* Matching red from page 1 */
            font-weight: 500;
        }

        .feedback {
            margin: 20px 0;
            padding: 15px;
            border-radius: 10px;
            font-weight: 500;
        }

        .feedback.success {
            background: rgba(42, 157, 143, 0.1);
            color: #155724;
            border: 1px solid rgba(42, 157, 143, 0.3);
        }

        .feedback.error {
            background: rgba(230, 57, 70, 0.1);
            color: #721c24;
            border: 1px solid rgba(230, 57, 70, 0.3);
        }

        .mic-indicator {
            display: inline-block;
            width: 15px;
            height: 15px;
            background: #e63946; /* Matching red from page 1 */
            border-radius: 50%;
            margin-left: 10px;
            animation: pulse 1s infinite;
        }

        .mic-indicator.active {
            background: #2a9d8f; /* Matching green from page 1 */
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .game-over-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .game-over-content {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
        }

        .game-over-content h2 {
            color: #2d5a4c; /* Primary green color from page 1 */
            font-size: 2em;
            margin-bottom: 20px;
            font-weight: 300;
        }

        .game-over-content p {
            font-size: 1em;
            margin: 10px 0;
            color: #374151; /* Text color from page 1 */
        }

        .final-stats {
            background: rgba(229, 231, 235, 0.5);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #2d5a4c; /* Primary green color from page 1 */
        }

        .stat-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-weight: 500;
        }

        .modal-buttons {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 16px;
            }
            
            .game-container {
                padding: 20px;
            }
            
            .title {
                font-size: 1.8em;
            }
            
            .image-display {
                min-height: 200px;
            }
            
            .game-input {
                font-size: 1em;
            }
            
            .controls {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 200px;
            }
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
                    <div class="game-container">
                        <h1 class="title">🖼️ Image Recognition</h1>
                        <?php if ($user): ?>
                        <div class="user-info">
                            <strong>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</strong>
                            <br>
                            <small>Rehabilitation Progress Tracking Active</small>
                        </div>
                        <?php endif; ?>

                        <div class="game-stats">
                            <div class="stat">
                                <div class="stat-value" id="level">1</div>
                                <div class="stat-label">Level</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value" id="score">0</div>
                                <div class="stat-label">Score</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value" id="streak">0</div>
                                <div class="stat-label">Streak</div>
                            </div>
                            <div class="stat">
                                <div class="stat-value" id="accuracy">100%</div>
                                <div class="stat-label">Accuracy</div>
                            </div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill" id="levelProgress" style="width: 0%"></div>
                            </div>
                            <div class="timer" id="timer">Time: 10s</div>
                        </div>

                        <div class="image-display" id="imageDisplay">
                            <div class="image-placeholder">Click Start to Begin!</div>
                        </div>

                        <div class="input-container">
                            <input type="text" class="game-input" id="gameInput" placeholder="Say what you see..." disabled>
                        </div>

                        <div class="controls">
                            <button class="btn btn-primary" id="startBtn" onclick="startGame()">Start Game</button>
                            <button class="btn btn-secondary" id="hintBtn" onclick="giveHint()" disabled>💡 Get Hint</button>
                            <button class="btn btn-success" id="voiceBtn" onclick="toggleVoiceRecognition()" disabled>
                                🎤 Voice Mode <span class="mic-indicator" id="micIndicator"></span>
                            </button>
                            <button class="btn btn-warning" id="skipBtn" onclick="skipImage()" disabled>Skip Image</button>
                        </div>

                        <div class="feedback hidden" id="feedback"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Game Over Modal -->
    <div class="game-over-modal hidden" id="gameOverModal">
        <div class="game-over-content">
            <h2>🎮 Game Over!</h2>
            <div class="final-stats">
                <div class="stat-row">
                    <span>Final Score:</span>
                    <span id="finalScore">0</span>
                </div>
                <div class="stat-row">
                    <span>Level Reached:</span>
                    <span id="finalLevel">1</span>
                </div>
                <div class="stat-row">
                    <span>Accuracy:</span>
                    <span id="finalAccuracy">100%</span>
                </div>
                <div class="stat-row">
                    <span>Time Played:</span>
                    <span id="timePlayed">00:00</span>
                </div>
                <div class="stat-row">
                    <span>Images Recognized:</span>
                    <span id="imagesCompleted">0</span>
                </div>
            </div>
            <p style="color: #6b7280; margin: 15px 0;">Great effort! Keep practicing to improve your skills.</p>
            <div class="modal-buttons">
                <button class="btn btn-primary" onclick="startNewGame()">🎯 Play Again</button>
                <button class="btn btn-secondary" onclick="closeGameOverModal()">❌ Close</button>
            </div>
        </div>
    </div>

    <script>
        // Game state
        let gameState = {
            isPlaying: false,
            currentImage: null,
            currentAnswer: '',
            level: 1,
            score: 0,
            streak: 0,
            correctAnswers: 0,
            totalAttempts: 0,
            timeLeft: 10,
            imagesPerLevel: 5,
            currentLevelProgress: 0,
            startTime: null,
            isVoiceMode: false,
            recognition: null
        };

        // Image data by difficulty level
        const imageData = {
            1: [
                { image: 'https://cdn.pixabay.com/photo/2016/12/13/05/15/puppy-1903313_640.jpg', answer: 'dog', hint: 'A common pet that barks' },
                { image: 'https://cdn.pixabay.com/photo/2017/02/20/18/03/cat-2083492_640.jpg', answer: 'cat', hint: 'A common pet that meows' },
                { image: 'https://cdn.pixabay.com/photo/2013/07/13/11/34/apple-158419_640.png', answer: 'apple', hint: 'A red or green fruit' },
                { image: 'https://cdn.pixabay.com/photo/2014/04/03/00/41/house-309113_640.png', answer: 'house', hint: 'A place where people live' },
                { image: 'https://cdn.pixabay.com/photo/2013/07/13/11/44/car-158548_640.png', answer: 'car', hint: 'A vehicle with wheels' }
            ],
            2: [
                { image: 'https://cdn.pixabay.com/photo/2016/01/02/01/59/bananas-1117790_640.jpg', answer: 'bananas', hint: 'Yellow curved fruits' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/03/36/celebrating-1867616_640.jpg', answer: 'balloons', hint: 'Colorful floating objects filled with gas' },
                { image: 'https://cdn.pixabay.com/photo/2014/12/22/00/04/tree-576847_640.png', answer: 'tree', hint: 'Has leaves and branches' },
                { image: 'https://cdn.pixabay.com/photo/2013/07/12/14/53/television-148328_640.png', answer: 'television', hint: 'Shows programs and movies' },
                { image: 'https://cdn.pixabay.com/photo/2013/07/12/18/39/smartphone-153650_640.png', answer: 'phone', hint: 'Used for calling and texting' }
            ],
            3: [
                { image: 'https://cdn.pixabay.com/photo/2016/11/22/19/15/hamburger-1850215_640.jpg', answer: 'hamburger', hint: 'Fast food with buns and patty' },
                { image: 'https://cdn.pixabay.com/photo/2017/01/10/03/06/elephant-1968012_640.jpg', answer: 'elephant', hint: 'Large animal with a trunk' },
                { image: 'https://cdn.pixabay.com/photo/2013/07/12/12/56/ambulance-147728_640.png', answer: 'ambulance', hint: 'Emergency medical vehicle' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/23/13/48/bicycle-1853241_640.jpg', answer: 'bicycle', hint: 'Two-wheeled human-powered vehicle' },
                { image: 'https://cdn.pixabay.com/photo/2016/03/05/19/02/hamburger-1238246_640.jpg', answer: 'sandwich', hint: 'Food with fillings between bread' }
            ],
            4: [
                { image: 'https://cdn.pixabay.com/photo/2016/03/05/20/00/airport-1239127_640.jpg', answer: 'airport', hint: 'Place where airplanes take off and land' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/08/41/apple-1868496_640.jpg', answer: 'fruit basket', hint: 'Container holding various fruits' },
                { image: 'https://cdn.pixabay.com/photo/2017/01/16/19/54/ireland-1985088_640.jpg', answer: 'rainbow', hint: 'Colorful arc in the sky after rain' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/08/36/beach-1868515_640.jpg', answer: 'beach', hint: 'Sandy shore by the ocean' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/08/36/city-1868514_640.jpg', answer: 'city', hint: 'Urban area with many buildings' }
            ],
            5: [
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/08/36/architecture-1868513_640.jpg', answer: 'skyscraper', hint: 'Very tall building' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/08/36/audience-1868512_640.jpg', answer: 'concert', hint: 'Musical performance event' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/08/36/breakfast-1868511_640.jpg', answer: 'breakfast', hint: 'First meal of the day' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/08/36/coffee-1868510_640.jpg', answer: 'coffee shop', hint: 'Place that serves coffee drinks' },
                { image: 'https://cdn.pixabay.com/photo/2016/11/29/08/36/doctor-1868509_640.jpg', answer: 'hospital', hint: 'Medical treatment facility' }
            ]
        };

        let currentTimer;
        let gameTimer;

        // Initialize speech synthesis and recognition
        const synth = window.speechSynthesis;
        let voices = [];

        function loadVoices() {
            voices = synth.getVoices();
            console.log('Voices loaded:', voices.length);
        }

        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = loadVoices;
        }
        loadVoices();

        // Initialize speech recognition
        function initSpeechRecognition() {
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                gameState.recognition = new SpeechRecognition();
                gameState.recognition.continuous = false;
                gameState.recognition.interimResults = false;
                gameState.recognition.lang = 'en-US';

                gameState.recognition.onresult = function(event) {
                    const transcript = event.results[0][0].transcript.toLowerCase().trim();
                    document.getElementById('gameInput').value = transcript;
                    checkAnswer();
                };

                gameState.recognition.onerror = function(event) {
                    console.error('Speech recognition error:', event.error);
                    showFeedback('Voice recognition error. Try again.', 'error');
                };

                gameState.recognition.onend = function() {
                    document.getElementById('micIndicator').classList.remove('active');
                    if (gameState.isVoiceMode && gameState.isPlaying) {
                        setTimeout(() => {
                            if (gameState.isVoiceMode && gameState.isPlaying) {
                                startListening();
                            }
                        }, 1000);
                    }
                };
            } else {
                console.log('Speech recognition not supported');
                document.getElementById('voiceBtn').style.display = 'none';
            }
        }

        initSpeechRecognition();

        function startGame() {
            gameState.isPlaying = true;
            gameState.level = 1;
            gameState.score = 0;
            gameState.streak = 0;
            gameState.correctAnswers = 0;
            gameState.totalAttempts = 0;
            gameState.currentLevelProgress = 0;
            gameState.startTime = new Date();

            document.getElementById('startBtn').disabled = true;
            document.getElementById('hintBtn').disabled = false;
            document.getElementById('voiceBtn').disabled = false;
            document.getElementById('skipBtn').disabled = false;
            document.getElementById('gameInput').disabled = false;
            document.getElementById('gameInput').focus();
            document.getElementById('gameOverModal').classList.add('hidden');
            document.getElementById('feedback').classList.add('hidden');

            updateDisplay();
            nextImage();
            
            // Start game timer for session tracking
            gameTimer = setInterval(() => {
                updateGameTime();
            }, 1000);
        }

        function nextImage() {
            if (!gameState.isPlaying) return;

            const images = imageData[gameState.level] || imageData[5];
            const randomImage = images[Math.floor(Math.random() * images.length)];
            gameState.currentImage = randomImage.image;
            gameState.currentAnswer = randomImage.answer;
            gameState.timeLeft = Math.max(5, 15 - gameState.level);

            // Display the image
            const imageDisplay = document.getElementById('imageDisplay');
            imageDisplay.innerHTML = `<img src="${gameState.currentImage}" alt="Image to recognize">`;

            document.getElementById('gameInput').value = '';
            document.getElementById('gameInput').className = 'game-input';

            startTimer();
            updateDisplay();

            // Auto-start voice recognition if enabled
            if (gameState.isVoiceMode) {
                setTimeout(startListening, 1000);
            }
        }

        function startTimer() {
            clearInterval(currentTimer);
            currentTimer = setInterval(() => {
                gameState.timeLeft--;
                document.getElementById('timer').textContent = `Time: ${gameState.timeLeft}s`;

                if (gameState.timeLeft <= 0) {
                    clearInterval(currentTimer);
                    wrongAnswer();
                }
            }, 1000);
        }

        function checkAnswer() {
            const userInput = document.getElementById('gameInput').value.toLowerCase().trim();
            
            if (userInput === gameState.currentAnswer.toLowerCase()) {
                correctAnswer();
            } else if (userInput.length >= gameState.currentAnswer.length) {
                wrongAnswer();
            }
        }

        function correctAnswer() {
            clearInterval(currentTimer);
            gameState.score += gameState.timeLeft * 10 + gameState.level * 5;
            gameState.streak++;
            gameState.correctAnswers++;
            gameState.totalAttempts++;
            gameState.currentLevelProgress++;

            document.getElementById('gameInput').className = 'game-input correct';
            showFeedback('Correct! Great job!', 'success');

            // Check level progression
            if (gameState.currentLevelProgress >= gameState.imagesPerLevel) {
                gameState.level++;
                gameState.currentLevelProgress = 0;
                showFeedback(`Level Up! Welcome to Level ${gameState.level}!`, 'success');
            }

            setTimeout(() => {
                nextImage();
            }, 1500);

            updateDisplay();
        }

        function wrongAnswer() {
            clearInterval(currentTimer);
            gameState.streak = 0;
            gameState.totalAttempts++;

            document.getElementById('gameInput').className = 'game-input incorrect';
            showFeedback(`Incorrect! The answer was "${gameState.currentAnswer}"`, 'error');

            // Game over after wrong answer
            setTimeout(() => {
                endGame();
            }, 2000);
        }

        function skipImage() {
            showFeedback(`Skipped! The answer was "${gameState.currentAnswer}"`, 'error');
            wrongAnswer();
        }

        function giveHint() {
            if (!gameState.currentImage) return;
            
            const images = imageData[gameState.level] || imageData[5];
            const currentImageData = images.find(img => img.image === gameState.currentImage);
            
            if (currentImageData && currentImageData.hint) {
                showFeedback(`Hint: ${currentImageData.hint}`, 'success');
                
                // Speak the hint
                if (synth) {
                    const utterance = new SpeechSynthesisUtterance(currentImageData.hint);
                    const englishVoice = voices.find(voice => 
                        voice.lang.startsWith('en') && voice.name.includes('Enhanced')
                    ) || voices.find(voice => voice.lang.startsWith('en'));
                    
                    if (englishVoice) {
                        utterance.voice = englishVoice;
                    }
                    
                    utterance.rate = 0.8;
                    synth.speak(utterance);
                }
            }
        }

        function toggleVoiceRecognition() {
            if (!gameState.recognition) {
                showFeedback('Voice recognition not supported in this browser.', 'error');
                return;
            }

            gameState.isVoiceMode = !gameState.isVoiceMode;
            const btn = document.getElementById('voiceBtn');
            
            if (gameState.isVoiceMode) {
                btn.innerHTML = '🎤 Voice: ON <span class="mic-indicator" id="micIndicator"></span>';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-warning');
                if (gameState.isPlaying) {
                    startListening();
                }
            } else {
                btn.innerHTML = '🎤 Voice Mode <span class="mic-indicator" id="micIndicator"></span>';
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-success');
                stopListening();
            }
        }

        function startListening() {
            if (gameState.recognition && gameState.isVoiceMode) {
                try {
                    gameState.recognition.start();
                    document.getElementById('micIndicator').classList.add('active');
                } catch (e) {
                    console.error('Error starting recognition:', e);
                }
            }
        }

        function stopListening() {
            if (gameState.recognition) {
                gameState.recognition.stop();
                document.getElementById('micIndicator').classList.remove('active');
            }
        }

        function showFeedback(message, type) {
            const feedback = document.getElementById('feedback');
            feedback.textContent = message;
            feedback.className = `feedback ${type}`;
            feedback.classList.remove('hidden');

            setTimeout(() => {
                feedback.classList.add('hidden');
            }, 3000);
        }

        function updateDisplay() {
            document.getElementById('level').textContent = gameState.level;
            document.getElementById('score').textContent = gameState.score;
            document.getElementById('streak').textContent = gameState.streak;
            
            const accuracy = gameState.totalAttempts > 0 ? 
                Math.round((gameState.correctAnswers / gameState.totalAttempts) * 100) : 100;
            document.getElementById('accuracy').textContent = accuracy + '%';

            const progressPercent = (gameState.currentLevelProgress / gameState.imagesPerLevel) * 100;
            document.getElementById('levelProgress').style.width = progressPercent + '%';
        }

        function updateGameTime() {
            if (gameState.startTime) {
                const now = new Date();
                const elapsed = Math.floor((now - gameState.startTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                const timeString = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                // Store for game over display
                gameState.sessionTime = timeString;
            }
        }

        function endGame() {
            gameState.isPlaying = false;
            clearInterval(currentTimer);
            clearInterval(gameTimer);
            stopListening();

            document.getElementById('startBtn').disabled = false;
            document.getElementById('hintBtn').disabled = true;
            document.getElementById('voiceBtn').disabled = true;
            document.getElementById('skipBtn').disabled = true;
            document.getElementById('gameInput').disabled = true;
            document.getElementById('imageDisplay').innerHTML = '<div class="image-placeholder">Click Start to Begin!</div>';

            // Update game over display
            document.getElementById('finalScore').textContent = gameState.score;
            document.getElementById('finalLevel').textContent = gameState.level;
            document.getElementById('imagesCompleted').textContent = gameState.correctAnswers;
            
            const finalAccuracy = gameState.totalAttempts > 0 ? 
                Math.round((gameState.correctAnswers / gameState.totalAttempts) * 100) : 100;
            document.getElementById('finalAccuracy').textContent = finalAccuracy + '%';
            document.getElementById('timePlayed').textContent = gameState.sessionTime || '00:00';

            document.getElementById('gameOverModal').classList.remove('hidden');

            // Save game session to database with additional metrics
            saveGameSession();
        }

        function saveGameSession() {
            const playerId = <?php echo json_encode($user['username'] ?? 'guest'); ?>;
            const accuracy = gameState.totalAttempts > 0 ? 
                (gameState.correctAnswers / gameState.totalAttempts) * 100 : 100;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=save_session&player_id=${encodeURIComponent(playerId)}&level_reached=${gameState.level}&final_score=${gameState.score}&session_time=${encodeURIComponent(gameState.sessionTime || '00:00')}&images_completed=${gameState.correctAnswers}&accuracy=${accuracy}&highest_streak=${gameState.highestStreak || gameState.streak}&completed_levels=${Math.floor(gameState.correctAnswers / gameState.imagesPerLevel)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Game session saved successfully');
                }
            })
            .catch(error => {
                console.error('Error saving game session:', error);
            });
        }

        function startNewGame() {
            // Close modal first
            document.getElementById('gameOverModal').classList.add('hidden');
            // Reset game state
            resetGame();
            // Start the game
            startGame();
        }

        function closeGameOverModal() {
            document.getElementById('gameOverModal').classList.add('hidden');
        }

        function resetGame() {
            gameState.isVoiceMode = false;
            document.getElementById('voiceBtn').innerHTML = '🎤 Voice Mode <span class="mic-indicator" id="micIndicator"></span>';
            document.getElementById('voiceBtn').classList.remove('btn-warning');
            document.getElementById('voiceBtn').classList.add('btn-success');
            updateDisplay();
        }

        // Event listeners
        document.getElementById('gameInput').addEventListener('input', checkAnswer);
        document.getElementById('gameInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                checkAnswer();
            }
        });

        // Load voices when page loads
        window.addEventListener('load', function() {
            setTimeout(loadVoices, 100);
        });

        console.log('Image Recognition Game initialized successfully!');
    </script>
</body>
</html>