<?php
require_once 'config.php';
require_once 'header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
?>

<main class="content">
    <h2><i class="fas fa-brain"></i> Cognitive Training</h2>
    
    <div class="training-intro">
        <p>Regular cognitive exercises can help improve memory, attention, and problem-solving skills. Try these activities:</p>
    </div>
    
    <div class="training-activities">
        <div class="activity-card">
            <div class="activity-icon">
                <i class="fas fa-memory"></i>
            </div>
            <h3>Memory Game</h3>
            <p>Test your memory by matching pairs of cards.</p>
            <a href="memory-game.php" class="btn">Start Game</a>
        </div>
        
        <div class="activity-card">
            <div class="activity-icon">
                <i class="fas fa-sort-alpha-down"></i>
            </div>
            <h3>Word Recall</h3>
            <p>Remember and recall words after a short delay.</p>
            <a href="word-recall.php" class="btn">Start Exercise</a>
        </div>
        
        <div class="activity-card">
            <div class="activity-icon">
                <i class="fas fa-puzzle-piece"></i>
            </div>
            <h3>Puzzle Solving</h3>
            <p>Solve puzzles to improve problem-solving skills.</p>
            <a href="puzzles.php" class="btn">Start Puzzle</a>
        </div>
        
        <div class="activity-card">
            <div class="activity-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3>Daily Training</h3>
            <p>Personalized daily exercises based on your progress.</p>
            <a href="daily-training.php" class="btn">Start Training</a>
        </div>
    </div>
    
    <div class="progress-section">
        <h3>Your Training Progress</h3>
        <div class="progress-chart">
            <!-- Placeholder for progress chart -->
            <p>Your progress chart will appear here as you complete exercises.</p>
        </div>
    </div>
</main>

<?php
require_once 'footer.php';
?>