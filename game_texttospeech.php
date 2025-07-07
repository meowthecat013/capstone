<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'stroke_patient_system';
$username = 'root'; // Change as needed
$password = ''; // Change as needed

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
    $completed = (int)($_POST['completed'] ?? 0);
    $words_completed = (int)($_POST['words_completed'] ?? 0);
    $accuracy = (float)($_POST['accuracy'] ?? 0);
    $streak_max = (int)($_POST['streak_max'] ?? 0);
    
    // Get user ID if logged in
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Save to game_sessions table
    $stmt = $pdo->prepare("INSERT INTO texttospeech_game_sessions (
        user_id, game_name, session_start, session_end, session_duration,
        level_reached, final_score, words_completed, accuracy, streak_max, completed
    ) VALUES (
        ?, ?, NOW(), NOW(), ?,
        ?, ?, ?, ?, ?, ?
    )");
    
    $stmt->execute([
        $user_id,
        'Text to Speech Game',
        $session_time,
        $level_reached,
        $final_score,
        $words_completed,
        $accuracy,
        $streak_max,
        $completed
    ]);
    
    $session_id = $pdo->lastInsertId();
    
    echo json_encode(['success' => true, 'session_id' => $session_id]);
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
    <title>Voice Typing Game - Stroke Rehabilitation</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        body {
            background-color: #f0f2f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #374151;
        }

        .game-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 90%;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .header {
            margin-bottom: 30px;
        }

        .title {
            font-size: 2.2em;
            color: #2d5a4c;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #2d5a4c;
            color: #4b5563;
        }

        .game-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .stat {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 5px;
            flex: 1;
            min-width: 120px;
            border: 1px solid rgba(229, 231, 235, 0.5);
        }

        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #2d5a4c;
        }

        .stat-label {
            font-size: 0.9em;
            color: #6b7280;
            margin-top: 5px;
        }

        .word-display {
            font-size: 3em;
            font-weight: bold;
            color: #457b9d;
            margin: 30px 0;
            min-height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 15px;
            border: 2px dashed #2a9d8f;
            padding: 20px;
        }

        .input-container {
            margin: 30px 0;
        }

        .game-input {
            font-size: 1.5em;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .game-input:focus {
            outline: none;
            border-color: #2d5a4c;
            box-shadow: 0 0 10px rgba(45, 90, 76, 0.1);
        }

        .game-input.correct {
            border-color: #2a9d8f;
            background-color: rgba(42, 157, 143, 0.1);
        }

        .game-input.incorrect {
            border-color: #e63946;
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
            border-radius: 25px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            min-width: 120px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2d5a4c, #2a9d8f);
            color: white;
        }

        .btn-secondary {
            background: #457b9d;
            color: white;
        }

        .btn-success {
            background: #2a9d8f;
            color: white;
        }

        .btn-warning {
            background: #f4a261;
            color: white;
        }

        .btn-danger {
            background: #e63946;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            opacity: 0.9;
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
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2a9d8f, #457b9d);
            transition: width 0.3s ease;
        }

        .timer {
            font-size: 1.2em;
            margin: 10px 0;
            color: #e63946;
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
            color: #2a9d8f;
            border: 1px solid rgba(42, 157, 143, 0.3);
        }

        .feedback.error {
            background: rgba(230, 57, 70, 0.1);
            color: #e63946;
            border: 1px solid rgba(230, 57, 70, 0.3);
        }

        .mic-indicator {
            display: inline-block;
            width: 20px;
            height: 20px;
            background: #e63946;
            border-radius: 50%;
            margin-left: 10px;
            animation: pulse 1s infinite;
        }

        .mic-indicator.active {
            background: #2a9d8f;
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
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .game-over-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            max-width: 500px;
            width: 90%;
            text-align: center;
            position: relative;
        }

        .game-over-content h2 {
            color: #2d5a4c;
            font-size: 2.2em;
            margin-bottom: 20px;
            font-weight: 300;
        }

        .game-over-content p {
            font-size: 1.1em;
            margin: 10px 0;
            color: #4b5563;
        }

        .final-stats {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #2d5a4c;
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
            .game-container {
                padding: 20px;
            }
            
            .title {
                font-size: 2em;
            }
            
            .word-display {
                font-size: 2em;
            }
            
            .game-input {
                font-size: 1.2em;
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
    <div class="game-container">
        <div class="header">
            <h1 class="title">Voice Typing Game</h1>
            <?php if ($user): ?>
            <div class="user-info">
                <strong>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</strong>
                <br>
                <small>Rehabilitation Progress Tracking Active</small>
            </div>
            <?php endif; ?>
        </div>

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

        <div class="word-display" id="wordDisplay">Click Start to Begin!</div>

        <div class="input-container">
            <input type="text" class="game-input" id="gameInput" placeholder="Type the word here..." disabled>
        </div>

        <div class="controls">
            <button class="btn btn-primary" id="startBtn" onclick="startGame()">Start Game</button>
            <button class="btn btn-secondary" id="speakBtn" onclick="speakCurrentWord()" disabled>Hear Word</button>
            <button class="btn btn-success" id="voiceBtn" onclick="toggleVoiceRecognition()" disabled>
                Voice Mode <span class="mic-indicator" id="micIndicator"></span>
            </button>
            <button class="btn btn-warning" id="skipBtn" onclick="skipWord()" disabled>Skip Word</button>
        </div>

        <div class="feedback hidden" id="feedback"></div>

        <!-- Game Over Modal -->
        <div class="game-over-modal hidden" id="gameOverModal">
            <div class="game-over-content">
                <h2>Game Over</h2>
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
                        <span>Words Completed:</span>
                        <span id="wordsCompleted">0</span>
                    </div>
                </div>
                <p style="color: #6b7280; margin: 15px 0;">Great effort! Keep practicing to improve your skills.</p>
                <div class="modal-buttons">
                    <button class="btn btn-primary" onclick="startNewGame()">Play Again</button>
                    <button class="btn btn-danger" onclick="closeGameOverModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Game state
        let gameState = {
            isPlaying: false,
            currentWord: '',
            level: 1,
            score: 0,
            streak: 0,
            correctAnswers: 0,
            totalAttempts: 0,
            timeLeft: 10,
            wordsPerLevel: 5,
            currentLevelProgress: 0,
            startTime: null,
            isVoiceMode: false,
            recognition: null,
            highestStreak: 0
        };

        // Word lists by difficulty
        const wordLists = {
            1: ['cat', 'dog', 'sun', 'car', 'book', 'tree', 'ball', 'cake', 'fish', 'bird'],
            2: ['apple', 'water', 'house', 'green', 'happy', 'music', 'phone', 'paper', 'chair', 'plant'],
            3: ['computer', 'rainbow', 'garden', 'hospital', 'mountain', 'keyboard', 'elephant', 'bicycle', 'sandwich', 'umbrella'],
            4: ['beautiful', 'wonderful', 'adventure', 'chocolate', 'butterfly', 'telephone', 'basketball', 'motorcycle', 'strawberry', 'helicopter'],
            5: ['extraordinary', 'photography', 'encyclopedia', 'pharmaceutical', 'mathematician', 'rehabilitation', 'extraordinary', 'revolutionary', 'entertainment', 'sophisticated']
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
            document.getElementById('speakBtn').disabled = false;
            document.getElementById('voiceBtn').disabled = false;
            document.getElementById('skipBtn').disabled = false;
            document.getElementById('gameInput').disabled = false;
            document.getElementById('gameInput').focus();
            document.getElementById('gameOverModal').classList.add('hidden');
            document.getElementById('feedback').classList.add('hidden');

            updateDisplay();
            nextWord();
            
            // Start game timer for session tracking
            gameTimer = setInterval(() => {
                updateGameTime();
            }, 1000);
        }

        function nextWord() {
            if (!gameState.isPlaying) return;

            const words = wordLists[gameState.level] || wordLists[5];
            gameState.currentWord = words[Math.floor(Math.random() * words.length)];
            gameState.timeLeft = Math.max(5, 15 - gameState.level);

            document.getElementById('wordDisplay').textContent = gameState.currentWord;
            document.getElementById('gameInput').value = '';
            document.getElementById('gameInput').className = 'game-input';

            // Automatically speak the word
            speakCurrentWord();

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
            
            if (userInput === gameState.currentWord.toLowerCase()) {
                correctAnswer();
            } else if (userInput.length >= gameState.currentWord.length) {
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
            gameState.streak++;
            if (gameState.streak > gameState.highestStreak) {
                gameState.highestStreak = gameState.streak;
            }
            // Check level progression
            if (gameState.currentLevelProgress >= gameState.wordsPerLevel) {
                gameState.level++;
                gameState.currentLevelProgress = 0;
                showFeedback(`Level Up! Welcome to Level ${gameState.level}!`, 'success');
            }

            setTimeout(() => {
                nextWord();
            }, 1500);

            updateDisplay();
        }

        function wrongAnswer() {
            clearInterval(currentTimer);
            gameState.streak = 0;
            gameState.totalAttempts++;

            document.getElementById('gameInput').className = 'game-input incorrect';
            showFeedback(`Incorrect! The word was "${gameState.currentWord}"`, 'error');

            // Game over after wrong answer
            setTimeout(() => {
                endGame();
            }, 2000);
        }

        function skipWord() {
            showFeedback(`Skipped! The word was "${gameState.currentWord}"`, 'error');
            wrongAnswer();
        }

        function speakCurrentWord() {
            if (gameState.currentWord && synth) {
                const utterance = new SpeechSynthesisUtterance(gameState.currentWord);
                
                // Try to use a clear English voice
                const englishVoice = voices.find(voice => 
                    voice.lang.startsWith('en') && voice.name.includes('Enhanced')
                ) || voices.find(voice => voice.lang.startsWith('en'));
                
                if (englishVoice) {
                    utterance.voice = englishVoice;
                }
                
                utterance.rate = 0.8;
                utterance.pitch = 1;
                utterance.volume = 1;
                
                synth.speak(utterance);
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
                btn.innerHTML = 'Voice: ON <span class="mic-indicator" id="micIndicator"></span>';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-warning');
                if (gameState.isPlaying) {
                    startListening();
                }
            } else {
                btn.innerHTML = 'Voice Mode <span class="mic-indicator" id="micIndicator"></span>';
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

            const progressPercent = (gameState.currentLevelProgress / gameState.wordsPerLevel) * 100;
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
            document.getElementById('speakBtn').disabled = true;
            document.getElementById('voiceBtn').disabled = true;
            document.getElementById('skipBtn').disabled = true;
            document.getElementById('gameInput').disabled = true;

            // Update game over display
            document.getElementById('finalScore').textContent = gameState.score;
            document.getElementById('finalLevel').textContent = gameState.level;
            document.getElementById('wordsCompleted').textContent = gameState.correctAnswers;
            
            const finalAccuracy = gameState.totalAttempts > 0 ? 
                Math.round((gameState.correctAnswers / gameState.totalAttempts) * 100) : 100;
            document.getElementById('finalAccuracy').textContent = finalAccuracy + '%';
            document.getElementById('timePlayed').textContent = gameState.sessionTime || '00:00';

            document.getElementById('gameOverModal').classList.remove('hidden');

            // Save game session to database
            saveGameSession();
        }

        function saveGameSession() {
            const playerId = <?php echo json_encode($user['username'] ?? 'guest'); ?>;
            const finalAccuracy = gameState.totalAttempts > 0 ? 
                (gameState.correctAnswers / gameState.totalAttempts) * 100 : 100;
            
            const data = {
                action: 'save_session',
                player_id: playerId,
                level_reached: gameState.level,
                final_score: gameState.score,
                session_time: gameState.sessionTime || '00:00',
                completed: gameState.level > 3 ? 1 : 0,
                words_completed: gameState.correctAnswers,
                accuracy: finalAccuracy,
                streak_max: gameState.highestStreak || 0
            };
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data).toString()
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
            document.getElementById('voiceBtn').innerHTML = 'Voice Mode <span class="mic-indicator" id="micIndicator"></span>';
            document.getElementById('voiceBtn').classList.remove('btn-warning');
            document.getElementById('voiceBtn').classList.add('btn-success');
            document.getElementById('wordDisplay').textContent = 'Click Start to Begin!';
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

        console.log('Voice Typing Game initialized successfully!');
    </script>
</body>
</html>