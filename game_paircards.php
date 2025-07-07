<?php
session_start();
require_once 'auth.php';

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

// Initialize game session
if (!isset($_SESSION['game_started'])) {
    $_SESSION['game_started'] = false;
    $_SESSION['level'] = 1;
    $_SESSION['score'] = 0;
    $_SESSION['player_id'] = isLoggedIn() ? $_SESSION['user_id'] : 'guest_' . uniqid();
    $_SESSION['start_time'] = time();
    $_SESSION['level_start_time'] = time();
    $_SESSION['moves_count'] = 0;
    $_SESSION['session_id'] = null;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'start_game') {
        $_SESSION['game_started'] = true;
        $_SESSION['level'] = 1;
        $_SESSION['score'] = 0;
        $_SESSION['start_time'] = time();
        $_SESSION['level_start_time'] = time();
        $_SESSION['moves_count'] = 0;
        
        // Create a new game session record with initial duration of 0
        $stmt = $pdo->prepare("INSERT INTO paircards_game_sessions 
            (user_id, game_name, session_start, session_end, session_duration, level_reached, final_score, device_info, ip_address) 
            VALUES (?, ?, NOW(), NOW(), '00:00:00', ?, ?, ?, ?)");
        
        $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
        $device_info = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        $stmt->execute([
            $user_id,
            'Memory Card Game',
            $_SESSION['level'],
            $_SESSION['score'],
            $device_info,
            $ip_address
        ]);
        
        $_SESSION['session_id'] = $pdo->lastInsertId();
        
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    if ($action === 'card_flipped') {
        if ($_SESSION['game_started']) {
            $_SESSION['moves_count']++;
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    if ($action === 'level_complete') {
        $level_time = time() - $_SESSION['level_start_time'];
        $level_score = (int)$_POST['level_score'];
        
        // Record level completion
        if ($_SESSION['session_id']) {
            $stmt = $pdo->prepare("INSERT INTO paircards_game_level_progress 
                (session_id, level_number, level_score, completion_time, moves_count, grid_size) 
                VALUES (?, ?, ?, ?, ?, ?)");
            
            $grid_size = $_SESSION['level'] <= 3 ? '4x4' : '6x6';
            $stmt->execute([
                $_SESSION['session_id'],
                $_SESSION['level'],
                $level_score,
                $level_time,
                $_SESSION['moves_count'],
                $grid_size
            ]);
        }
        
        $_SESSION['level']++;
        $_SESSION['score'] += $level_score;
        $_SESSION['level_start_time'] = time();
        $_SESSION['moves_count'] = 0;
        
        echo json_encode(['status' => 'success', 'new_level' => $_SESSION['level']]);
        exit;
    }
    
    if ($action === 'game_over') {
        $session_time = time() - $_SESSION['start_time'];
        $completed = $_POST['completed'] === 'true' ? 1 : 0;
        
        // Format duration as H:i:s
        $hours = floor($session_time / 3600);
        $minutes = floor(($session_time % 3600) / 60);
        $seconds = $session_time % 60;
        $duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        // Update game session record with actual duration
        if ($_SESSION['session_id']) {
            $stmt = $pdo->prepare("UPDATE paircards_game_sessions 
                SET session_end = NOW(), 
                    session_duration = ?, 
                    level_reached = ?, 
                    final_score = ?, 
                    completed = ? 
                WHERE id = ?");
            $stmt->execute([
                $duration,
                $_SESSION['level'],
                $_SESSION['score'],
                $completed,
                $_SESSION['session_id']
            ]);
        }
        
        // Reset session
        $_SESSION['game_started'] = false;
        $_SESSION['level'] = 1;
        $_SESSION['score'] = 0;
        $_SESSION['session_id'] = null;
        
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    if ($action === 'heartbeat') {
        // Update session duration periodically for active sessions
        if ($_SESSION['game_started'] && $_SESSION['session_id']) {
            $session_time = time() - $_SESSION['start_time'];
            $hours = floor($session_time / 3600);
            $minutes = floor(($session_time % 3600) / 60);
            $seconds = $session_time % 60;
            $duration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            
            $stmt = $pdo->prepare("UPDATE paircards_game_sessions 
                SET session_duration = ?
                WHERE id = ?");
            $stmt->execute([$duration, $_SESSION['session_id']]);
        }
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// Get current level and score
$current_level = $_SESSION['level'] ?? 1;
$current_score = $_SESSION['score'] ?? 0;
$player_id = isLoggedIn() ? $_SESSION['username'] : $_SESSION['player_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Card Game</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #2d5a4c 0%, #2d5a4c 35%, white 35.05%, white 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .game-header {
            text-align: center;
            color: #2d5a4c;
            margin-bottom: 30px;
        }

        .game-info {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
            color: #2d5a4c;
            font-size: 18px;
        }

        .timer {
            font-size: 24px;
            font-weight: bold;
            color: #ff6b6b;
        }

        .game-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .game-grid {
            display: grid;
            gap: 10px;
            margin: 20px 0;
        }

        .grid-4x4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .grid-6x6 {
            grid-template-columns: repeat(6, 1fr);
        }

        .card {
            width: 80px;
            height: 80px;
            background: #4a90e2;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            transform-style: preserve-3d;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card.flipped {
            transform: rotateY(180deg);
        }

        .card.matched {
            background: #4caf50;
            cursor: default;
        }

        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .card-front {
            background: #2c3e50;
            color: white;
        }

        .card-back {
            background: #2d5a4c;
            transform: rotateY(180deg);
            font-size: 32px;
        }

        .start-screen, .game-over-screen {
            text-align: center;
            color: #2d5a4c;
        }

        .btn {
            background: #4caf50;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 18px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .btn:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .btn-restart {
            background: #ff6b6b;
        }

        .btn-restart:hover {
            background: #ff5252;
        }

        .level-complete {
            text-align: center;
            color: #2d5a4c;
            margin: 20px 0;
        }

        .hidden {
            display: none;
        }

        .progress-bar {
            width: 100%;
            height: 10px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            margin: 10px 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #4caf50;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="game-header">
        <h1>🧠 Memory Card Game</h1>
        <div class="game-info">
            <div>Player: <?php echo htmlspecialchars($player_id); ?></div>
            <div>Level: <span id="level-display"><?php echo $current_level; ?></span></div>
            <div>Score: <span id="score-display"><?php echo $current_score; ?></span></div>
            <div class="timer">Time: <span id="timer">00:00</span></div>
        </div>
    </div>

    <div class="game-container">
        <!-- Start Screen -->
        <div id="start-screen" class="start-screen <?php echo $_SESSION['game_started'] ? 'hidden' : ''; ?>">
            <h2>Welcome to Memory Card Game!</h2>
            <p>Match all pairs of cards before time runs out!</p>
            <p>Each level gets harder with more cards and less time.</p>
            <button class="btn" onclick="startGame()">Start Game</button>
        </div>

        <!-- Game Screen -->
        <div id="game-screen" class="<?php echo !$_SESSION['game_started'] ? 'hidden' : ''; ?>">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <div id="game-grid" class="game-grid"></div>
            <div id="level-complete" class="level-complete hidden">
                <h3>Level Complete! 🎉</h3>
                <p>Great job! Moving to next level...</p>
            </div>
        </div>

        <!-- Game Over Screen -->
        <div id="game-over-screen" class="game-over-screen hidden">
            <h2 id="game-over-title">Game Over!</h2>
            <p id="game-over-message"></p>
            <p>Final Level: <span id="final-level"></span></p>
            <p>Final Score: <span id="final-score"></span></p>
            <button class="btn btn-restart" onclick="location.reload()">Play Again</button>
        </div>
    </div>

    <script>
        let gameState = {
            level: <?php echo $current_level; ?>,
            score: <?php echo $current_score; ?>,
            cards: [],
            flippedCards: [],
            matchedPairs: 0,
            totalPairs: 0,
            gameTimer: null,
            timeRemaining: 0,
            isGameActive: false
        };

        const cardSymbols = ['🎮', '🎯', '🎲', '🎪', '🎨', '🎭', '🎪', '🎼', '🎵', '🎶', '🎤', '🎧', '🎸', '🥁', '🎺', '🎷', '🎹', '🎻', '🎪', '🎡', '🎢', '🎠', '🎊', '🎉'];

        function startGame() {
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=start_game'
            }).then(() => {
                document.getElementById('start-screen').classList.add('hidden');
                document.getElementById('game-screen').classList.remove('hidden');
                initializeLevel();
            });
        }

        function initializeLevel() {
            gameState.isGameActive = true;
            gameState.matchedPairs = 0;
            gameState.flippedCards = [];
            
            // Calculate grid size and time based on level
            const gridSize = gameState.level <= 3 ? 4 : 6;
            gameState.totalPairs = (gridSize * gridSize) / 2;
            gameState.timeRemaining = Math.max(30, 60 - (gameState.level * 5)); // Decreasing time per level
            
            createGameGrid(gridSize);
            startTimer();
            updateDisplay();
        }

        function createGameGrid(size) {
            const grid = document.getElementById('game-grid');
            grid.innerHTML = '';
            grid.className = `game-grid grid-${size}x${size}`;
            
            // Create pairs of cards
            const symbols = cardSymbols.slice(0, gameState.totalPairs);
            const cardPairs = [...symbols, ...symbols];
            
            // Shuffle cards
            for (let i = cardPairs.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [cardPairs[i], cardPairs[j]] = [cardPairs[j], cardPairs[i]];
            }
            
            // Create card elements
            gameState.cards = cardPairs.map((symbol, index) => {
                const card = document.createElement('div');
                card.className = 'card';
                card.dataset.symbol = symbol;
                card.dataset.index = index;
                card.innerHTML = `
                    <div class="card-face card-front">?</div>
                    <div class="card-face card-back">${symbol}</div>
                `;
                card.addEventListener('click', () => flipCard(index));
                grid.appendChild(card);
                return { element: card, symbol, flipped: false, matched: false };
            });
        }

        function flipCard(index) {
            if (!gameState.isGameActive || gameState.flippedCards.length >= 2) return;
            
            const card = gameState.cards[index];
            if (card.flipped || card.matched) return;
            
            card.element.classList.add('flipped');
            card.flipped = true;
            gameState.flippedCards.push(index);
            
            // Track the card flip
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=card_flipped'
            });
            
            if (gameState.flippedCards.length === 2) {
                setTimeout(checkMatch, 1000);
            }
        }

        function checkMatch() {
            const [first, second] = gameState.flippedCards;
            const firstCard = gameState.cards[first];
            const secondCard = gameState.cards[second];
            
            if (firstCard.symbol === secondCard.symbol) {
                // Match found
                firstCard.matched = true;
                secondCard.matched = true;
                firstCard.element.classList.add('matched');
                secondCard.element.classList.add('matched');
                gameState.matchedPairs++;
                
                // Check if level complete
                if (gameState.matchedPairs === gameState.totalPairs) {
                    levelComplete();
                }
            } else {
                // No match, flip back
                firstCard.element.classList.remove('flipped');
                secondCard.element.classList.remove('flipped');
                firstCard.flipped = false;
                secondCard.flipped = false;
            }
            
            gameState.flippedCards = [];
        }

        function levelComplete() {
            gameState.isGameActive = false;
            clearInterval(gameState.gameTimer);
            
            // Calculate score bonus
            const timeBonus = gameState.timeRemaining * 10;
            const levelScore = (gameState.level * 100) + timeBonus;
            gameState.score += levelScore;
            
            // Show level complete message
            document.getElementById('level-complete').classList.remove('hidden');
            
            // Send level complete to server
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=level_complete&level_score=${levelScore}`
            }).then(response => response.json()).then(data => {
                gameState.level = data.new_level;
                
                setTimeout(() => {
                    document.getElementById('level-complete').classList.add('hidden');
                    initializeLevel();
                }, 2000);
            });
        }

        function gameOver(completed = false) {
            gameState.isGameActive = false;
            clearInterval(gameState.gameTimer);
            
            // Send game over to server
            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=game_over&completed=${completed}`
            }).then(() => {
                document.getElementById('game-screen').classList.add('hidden');
                document.getElementById('game-over-screen').classList.remove('hidden');
                
                document.getElementById('game-over-title').textContent = completed ? 'Congratulations!' : 'Time\'s Up!';
                document.getElementById('game-over-message').textContent = completed ? 'You completed all levels!' : 'Better luck next time!';
                document.getElementById('final-level').textContent = gameState.level;
                document.getElementById('final-score').textContent = gameState.score;
            });
        }

        function startTimer() {
            clearInterval(gameState.gameTimer);
            gameState.gameTimer = setInterval(() => {
                gameState.timeRemaining--;
                updateTimer();
                updateProgress();
                
                if (gameState.timeRemaining <= 0) {
                    gameOver(false);
                }
            }, 1000);
        }

        function updateTimer() {
            const minutes = Math.floor(gameState.timeRemaining / 60);
            const seconds = gameState.timeRemaining % 60;
            document.getElementById('timer').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function updateProgress() {
            const maxTime = Math.max(30, 60 - (gameState.level * 5));
            const progress = (gameState.timeRemaining / maxTime) * 100;
            document.getElementById('progress-fill').style.width = `${progress}%`;
        }

        function updateDisplay() {
            document.getElementById('level-display').textContent = gameState.level;
            document.getElementById('score-display').textContent = gameState.score;
        }

        // Initialize game if already started
        <?php if ($_SESSION['game_started']): ?>
        initializeLevel();
        <?php endif; ?>

        function startHeartbeat() {
        setInterval(() => {
            if (gameState.isGameActive) {
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=heartbeat'
                });
            }
        }, 30000); // Update every 30 seconds
    }

    // Initialize game if already started
    <?php if ($_SESSION['game_started']): ?>
    initializeLevel();
    startHeartbeat();
    <?php endif; ?>
    </script>
</body>
</html>