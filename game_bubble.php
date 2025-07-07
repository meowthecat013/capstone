<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bubble Games - Level Challenge</title>
    <style>
        /* Main Layout */
        .main-container {
            display: flex;
            max-width: 1800px;
            margin: 0 auto;
            min-height: calc(100vh - 140px);
            background: linear-gradient(to right, #2d5a4c 0%, #2d5a4c 35%, white 35.05%, white 100%);
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 32px;
            position: relative;
        }

        /* Welcome Section */
        .welcome-section {
            text-align: right;
            margin-right: 100px;
            margin-bottom: 40px;
            padding: 24px 0;
        }

        .welcome-title {
            font-size: 32px;
            color: #2d5a4c;
            font-weight: 300;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Card Styles */
        .game-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
            margin-bottom: 5px;
            padding: 5px;
        }

        .game-title {
            color: #2d5a4c;
            font-size: 24px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .bubble-icon {
            width: 30px;
            height: 30px;
            position: relative;
        }

        .bubble-icon::before,
        .bubble-icon::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: #2d5a4c;
        }

        .bubble-icon::before {
            width: 20px;
            height: 20px;
            top: 0;
            left: 0;
        }

        .bubble-icon::after {
            width: 12px;
            height: 12px;
            top: 15px;
            left: 15px;
        }

        /* Game Stats */
        .game-stats {
            display: flex;
            justify-content: space-between;
            background: rgba(45, 90, 76, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-label {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2d5a4c;
        }

        .time-left {
            color: #e63946 !important;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Game Board */
        .game-board {
            background: #f0f0f0;
            border-radius: 10px;
            width: 100%;
            height: 400px;
            position: relative;
            margin-bottom: 20px;
            overflow: hidden;
            cursor: crosshair;
        }

        /* Bubbles */
        .bubble {
            position: absolute;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            animation: float 3s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .bubble:hover {
            transform: scale(1.1);
        }

        .bubble.small {
            width: 40px;
            height: 40px;
            background: radial-gradient(circle at 30% 30%, #ff6b6b, #e55555);
            font-size: 12px;
        }

        .bubble.medium {
            width: 60px;
            height: 60px;
            background: radial-gradient(circle at 30% 30%, #4ecdc4, #45b7aa);
            font-size: 14px;
        }

        .bubble.large {
            width: 80px;
            height: 80px;
            background: radial-gradient(circle at 30% 30%, #45b7d1, #3a9bc1);
            font-size: 16px;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }

        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.5); opacity: 0.8; }
            100% { transform: scale(0); opacity: 0; }
        }

        .bubble.popping {
            animation: pop 0.4s ease-out forwards;
        }

        /* Progress Bar */
        .level-progress {
            background: rgba(45, 90, 76, 0.1);
            border-radius: 10px;
            padding: 10px;
            margin-bottom: 20px;
        }

        .progress-bar {
            background: rgba(229, 231, 235, 0.5);
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            background: linear-gradient(90deg, #2d5a4c, #45a049);
            height: 100%;
            transition: width 0.3s ease;
            border-radius: 10px;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: black;
            font-size: 12px;
            font-weight: bold;
        }

        /* Controls */
        .controls {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .close-btn {
            background: #2d5a4c;
            color: white;
        }

        .close-btn:hover {
            background: #1e3a21;
        }

        .leaderboard-btn {
            background: #f39c12;
            color: white;
        }

        .leaderboard-btn:hover {
            background: #d68910;
        }

        .restart-btn {
            background: #4a90e2;
            color: white;
        }

        .restart-btn:hover {
            background: #357abd;
        }

        .history-btn {
            background: #9c27b0;
            color: white;
        }

        .history-btn:hover {
            background: #7b1fa2;
        }

        /* Game Over Screen */
        .game-over {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.9);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            min-width: 300px;
            z-index: 100;
        }

        .game-over.failed {
            background: rgba(231, 76, 60, 0.9);
        }

        .game-over.success {
            background: rgba(46, 204, 113, 0.9);
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            font-size: 14px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .notification.error {
            background: #f44336;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .close-modal {
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }

        .close-modal:hover {
            color: #000;
        }

        /* Tables */
        .leaderboard-table, .sessions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .leaderboard-table th,
        .leaderboard-table td,
        .sessions-table th,
        .sessions-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .leaderboard-table th,
        .sessions-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .content-area {
                padding: 16px;
            }

            .game-stats {
                flex-wrap: wrap;
                gap: 10px;
            }

            .stat-item {
                flex: 1 0 45%;
            }

            .controls {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Main Container -->
    <div class="main-container">
        <!-- Content Area -->
        <main class="content-area">
            <!-- Welcome Section -->


            <!-- Game Card -->
            <div class="game-card">
                <div class="game-title">
                    <div class="bubble-icon"></div>
                    <span>Pop the Bubble Game</span>
                </div>
                
                <div class="game-stats">
                    <div class="stat-item">
                        <span class="stat-label">LEVEL</span>
                        <span class="stat-value" id="currentLevel">1</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">SCORE</span>
                        <span class="stat-value" id="score">0</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">SESSION TIME</span>
                        <span class="stat-value" id="sessionTime">0:00</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">TIME LEFT</span>
                        <span class="stat-value" id="timeLeft">60</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">TARGET</span>
                        <span class="stat-value" id="levelTarget">100</span>
                    </div>
                </div>

                <div class="level-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                        <div class="progress-text" id="progressText">0 / 100</div>
                    </div>
                </div>
                
                <div class="game-board" id="gameBoard">
                    <div class="game-over" id="gameOver">
                        <h2 id="gameOverTitle">Level Complete!</h2>
                        <p>Level: <span id="finalLevel">1</span></p>
                        <p>Score: <span id="finalScore">0</span></p>
                        <p>Session Time: <span id="finalSessionTime">0:00</span></p>
                        <div style="margin-top: 20px;">
                            <button class="btn restart-btn" onclick="nextLevel()" id="nextLevelBtn">Next Level</button>
                            <button class="btn restart-btn" onclick="restartGame()">Restart Game</button>
                        </div>
                    </div>
                </div>
                
                <div class="controls">
                    <button class="btn restart-btn" onclick="startLevel()">Restart Level</button>
                    <button class="btn leaderboard-btn" onclick="showLeaderboard()">Leaderboard</button>
                    <button class="btn history-btn" onclick="showUserSessions()">History</button>
                    <button class="btn close-btn" onclick="closeGame()">Close</button>
                </div>
            </div>
        </main>
    </div>

    <!-- Leaderboard Modal -->
    <div id="leaderboardModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Game Sessions Leaderboard</h2>
                <span class="close-modal" onclick="closeLeaderboardModal()">&times;</span>
            </div>
            <div id="leaderboardContent">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Player ID</th>
                            <th>Level</th>
                            <th>Score</th>
                            <th>Session Time</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardBody">
                        <tr><td colspan="6">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- User Sessions Modal -->
    <div id="userSessionsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Your Game Sessions</h2>
                <span class="close-modal" onclick="closeModal('userSessionsModal')">&times;</span>
            </div>
            <div id="userSessionsContent">
                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Level</th>
                            <th>Score</th>
                            <th>Session Time</th>
                        </tr>
                    </thead>
                    <tbody id="userSessionsBody">
                        <tr><td colspan="4">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // All the original game JavaScript code remains exactly the same
        // Game state variables
        let gameActive = false;
        let currentLevel = 1;
        let score = 0;
        let sessionStartTime = 0;
        let levelStartTime = 0;
        let timeLeft = 60; // Time limit per level in seconds
        let sessionTimer;
        let levelTimer;
        let bubbleTimer;
        let bubbles = [];
        let levelTarget = 100;
        let levelProgress = 0;
        let playerId = null;

        // Game configuration
        const LEVEL_TIME_LIMIT = 60; // seconds per level
        const BUBBLE_SPAWN_BASE_INTERVAL = 2000; // milliseconds
        const MIN_BUBBLE_SPAWN_INTERVAL = 500;

        // Initialize player ID
        function generatePlayerId() {
            if (!playerId) {
                playerId = 'player_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            }
            return playerId;
        }

        // Start a new game session
        function startGame() {
            generatePlayerId();
            currentLevel = 1;
            score = 0;
            levelTarget = 100;
            sessionStartTime = Date.now();
            startLevel();
        }

        // Start current level
        function startLevel() {
            gameActive = true;
            levelProgress = 0;
            timeLeft = LEVEL_TIME_LIMIT;
            bubbles = [];
            levelStartTime = Date.now();
            
            updateDisplay();
            document.getElementById('gameOver').style.display = 'none';
            clearGameBoard();
            
            // Start timers
            sessionTimer = setInterval(updateSessionTime, 1000);
            levelTimer = setInterval(updateLevelTimer, 1000);
            
            const spawnInterval = Math.max(BUBBLE_SPAWN_BASE_INTERVAL - (currentLevel * 50), MIN_BUBBLE_SPAWN_INTERVAL);
            bubbleTimer = setInterval(createBubble, spawnInterval);
            
            // Create initial bubbles
            for (let i = 0; i < Math.min(3 + Math.floor(currentLevel / 5), 8); i++) {
                setTimeout(() => createBubble(), i * 300);
            }
        }

        // Update session time display
        function updateSessionTime() {
            const elapsed = Math.floor((Date.now() - sessionStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            document.getElementById('sessionTime').textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        // Update level timer and check for timeout
        function updateLevelTimer() {
            timeLeft--;
            const timeLeftElement = document.getElementById('timeLeft');
            timeLeftElement.textContent = timeLeft;
            
            // Add visual warning when time is running out
            if (timeLeft <= 10) {
                timeLeftElement.classList.add('time-left');
            } else {
                timeLeftElement.classList.remove('time-left');
            }
            
            // Time's up - check if level target was met
            if (timeLeft <= 0) {
                if (levelProgress >= levelTarget) {
                    completeLevel();
                } else {
                    gameOver();
                }
            }
        }

        // Update all display elements
        function updateDisplay() {
            document.getElementById('currentLevel').textContent = currentLevel;
            document.getElementById('score').textContent = score;
            document.getElementById('levelTarget').textContent = levelTarget;
            document.getElementById('timeLeft').textContent = timeLeft;
            
            // Update progress bar
            const progressPercent = Math.min((levelProgress / levelTarget) * 100, 100);
            document.getElementById('progressFill').style.width = progressPercent + '%';
            document.getElementById('progressText').textContent = `${levelProgress} / ${levelTarget}`;
        }

        // Create a new bubble
        function createBubble() {
            if (!gameActive || bubbles.length >= Math.min(6 + Math.floor(currentLevel / 10), 12)) return;
            
            const gameBoard = document.getElementById('gameBoard');
            const bubble = document.createElement('div');
            
            // Random size with level-based probability
            const rand = Math.random();
            let size, points;
            if (rand < 0.3 + (currentLevel * 0.01)) {
                size = 'small';
                points = 25;
            } else if (rand < 0.7) {
                size = 'medium';
                points = 15;
            } else {
                size = 'large';
                points = 10;
            }
            
            bubble.className = `bubble ${size}`;
            bubble.textContent = points;
            bubble.dataset.points = points;
            
            // Random position
            const bubbleSize = size === 'small' ? 40 : size === 'medium' ? 60 : 80;
            const maxX = gameBoard.offsetWidth - bubbleSize;
            const maxY = gameBoard.offsetHeight - bubbleSize;
            
            bubble.style.left = Math.random() * maxX + 'px';
            bubble.style.top = Math.random() * maxY + 'px';
            
            // Add click event
            bubble.addEventListener('click', popBubble);
            
            // Random animation delay
            bubble.style.animationDelay = Math.random() * 2 + 's';
            
            gameBoard.appendChild(bubble);
            bubbles.push(bubble);
            
            // Remove bubble after some time if not clicked
            setTimeout(() => {
                if (bubble.parentNode && !bubble.classList.contains('popping')) {
                    bubble.remove();
                    bubbles = bubbles.filter(b => b !== bubble);
                }
            }, Math.max(8000 - (currentLevel * 100), 3000));
        }

        // Handle bubble click
        function popBubble(event) {
            if (!gameActive) return;
            
            const bubble = event.target;
            bubble.classList.add('popping');
            
            const points = parseInt(bubble.dataset.points);
            score += points;
            levelProgress += points;
            
            updateDisplay();
            
            // Check level completion (early completion)
            if (levelProgress >= levelTarget) {
                completeLevel();
            }
            
            // Remove bubble after animation
            setTimeout(() => {
                if (bubble.parentNode) {
                    bubble.remove();
                    bubbles = bubbles.filter(b => b !== bubble);
                }
            }, 400);
        }

        // Complete current level successfully
        function completeLevel() {
            gameActive = false;
            clearAllTimers();
            
            const sessionTime = getSessionTimeString();
            
            // Save game session
            saveGameSession(sessionTime, true);
            
            // Show success screen
            showGameOverScreen('Level Complete!', 'success', true);
        }

        // Handle game over (failed to meet target in time)
        function gameOver() {
            gameActive = false;
            clearAllTimers();
            
            const sessionTime = getSessionTimeString();
            
            // Save game session
            saveGameSession(sessionTime, false);
            
            // Show game over screen
            showGameOverScreen('Time\'s Up! Target Not Reached', 'failed', false);
        }

        // Show game over screen
        function showGameOverScreen(title, type, canContinue) {
            const gameOverElement = document.getElementById('gameOver');
            const sessionTime = getSessionTimeString();
            
            document.getElementById('gameOverTitle').textContent = title;
            document.getElementById('finalLevel').textContent = currentLevel;
            document.getElementById('finalScore').textContent = score;
            document.getElementById('finalSessionTime').textContent = sessionTime;
            
            gameOverElement.className = `game-over ${type}`;
            gameOverElement.style.display = 'block';
            
            // Show/hide next level button based on success
            document.getElementById('nextLevelBtn').style.display = canContinue ? 'inline-block' : 'none';
        }

        // Get formatted session time string
        function getSessionTimeString() {
            const elapsed = Math.floor((Date.now() - sessionStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        // Clear all game timers
        function clearAllTimers() {
            if (sessionTimer) clearInterval(sessionTimer);
            if (levelTimer) clearInterval(levelTimer);
            if (bubbleTimer) clearInterval(bubbleTimer);
        }

        function saveGameSession(sessionTime, completed) {
            const sessionTimeParts = sessionTime.split(':');
            const sessionDuration = parseInt(sessionTimeParts[0]) * 60 + parseInt(sessionTimeParts[1]);
            
            const gameData = {
                action: 'save_game_session',
                player_id: playerId,
                level: currentLevel,
                score: score,
                session_time: sessionTime,
                completed: completed,
                session_duration: sessionDuration
            };
            
            fetch('game_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(gameData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Session saved successfully!' + (data.user_logged_in ? ' (User session)' : ' (Guest session)'));
                } else {
                    showNotification('Error saving session: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error saving session:', error);
                showNotification('Error saving session', 'error');
            });
        }

        // Proceed to next level
        function nextLevel() {
            currentLevel++;
            levelTarget = 100 + (currentLevel - 1) * 50; // Increase target each level
            document.getElementById('gameOver').style.display = 'none';
            startLevel();
        }

        // Restart entire game
        function restartGame() {
            clearAllTimers();
            document.getElementById('gameOver').style.display = 'none';
            startGame();
        }

        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            
            document.body.appendChild(notification);
            
            // Fade in
            setTimeout(() => notification.style.opacity = '1', 100);
            
            // Fade out and remove
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Show leaderboard modal
        function showLeaderboard() {
            document.getElementById('leaderboardModal').style.display = 'block';
            
            fetch('game_handler.php?action=get_leaderboard', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayLeaderboard(data.data);
                } else {
                    document.getElementById('leaderboardBody').innerHTML = 
                        `<tr><td colspan="6">Error loading leaderboard: ${data.message}</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error loading leaderboard:', error);
                document.getElementById('leaderboardBody').innerHTML = 
                    '<tr><td colspan="6">Error loading leaderboard</td></tr>';
            });
        }

        function displayLeaderboard(records) {
            const tbody = document.getElementById('leaderboardBody');
            if (records.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6">No records found</td></tr>';
                return;
            }
            
            tbody.innerHTML = records.map((record, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>${record.player_name || record.player_id}</td>
                    <td>${record.level}</td>
                    <td>${record.score}</td>
                    <td>${record.session_time}</td>
                    <td>${new Date(record.created_at).toLocaleDateString()}</td>
                </tr>
            `).join('');
        }

        // Close leaderboard modal
        function closeLeaderboardModal() {
            document.getElementById('leaderboardModal').style.display = 'none';
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close game
        function closeGame() {
            if (confirm('Are you sure you want to close the game? Your current session will be saved.')) {
                if (gameActive) {
                    const sessionTime = getSessionTimeString();
                    saveGameSession(sessionTime, false);
                }
                alert('Thanks for playing Bubble Games!');
            }
        }

        // Clear game board
        function clearGameBoard() {
            const gameBoard = document.getElementById('gameBoard');
            bubbles.forEach(bubble => {
                if (bubble.parentNode) {
                    bubble.remove();
                }
            });
            bubbles = [];
        }

        // Show user sessions
        function showUserSessions() {
            fetch('game_handler.php?action=get_user_sessions', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('userSessionsBody');
                    if (data.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4">No game sessions found</td></tr>';
                    } else {
                        tbody.innerHTML = data.data.map(session => `
                            <tr>
                                <td>${new Date(session.created_at).toLocaleString()}</td>
                                <td>${session.level_reached}</td>
                                <td>${session.score}</td>
                                <td>${session.session_time}</td>
                            </tr>
                        `).join('');
                    }
                    document.getElementById('userSessionsModal').style.display = 'block';
                } else {
                    showNotification(data.message || 'Error loading your sessions', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading user sessions:', error);
                showNotification('Error loading your sessions', 'error');
            });
        }

        // Event listeners
        window.addEventListener('load', () => {
            setTimeout(startGame, 500);
        });

        window.addEventListener('click', (event) => {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });

        // Handle page visibility change to pause game
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && gameActive) {
                // Pause game when tab is hidden
                clearAllTimers();
            } else if (!document.hidden && gameActive) {
                // Resume game when tab is visible again
                sessionTimer = setInterval(updateSessionTime, 1000);
                levelTimer = setInterval(updateLevelTimer, 1000);
                const spawnInterval = Math.max(BUBBLE_SPAWN_BASE_INTERVAL - (currentLevel * 50), MIN_BUBBLE_SPAWN_INTERVAL);
                bubbleTimer = setInterval(createBubble, spawnInterval);
            }
        });
    </script>
</body>
</html>