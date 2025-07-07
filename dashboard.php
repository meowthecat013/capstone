<?php
require_once 'config.php';
require_once 'header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userData = getUserData($userId);

// Check if today's vitals already submitted
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT * FROM daily_vitals WHERE user_id = ? AND date = ?");
$stmt->execute([$userId, $today]);
$todayVitals = $stmt->fetch();

// Get music suggestions
$stmt = $pdo->prepare("SELECT * FROM music_library ORDER BY RAND() LIMIT 3");
$stmt->execute();
$musicSuggestions = $stmt->fetchAll();

// Get random quote
$quotes = [
    "Every day may not be good, but there's something good in every day.",
    "Small steps every day lead to big results.",
    "Recovery is not a race. You don't have to feel guilty if it takes you longer than you thought it would.",
    "You're braver than you believe, stronger than you seem, and smarter than you think.",
    "Healing is an art. It takes time, it takes practice, it takes love."
];
$randomQuote = $quotes[array_rand($quotes)];

// Get recent vitals
$stmt = $pdo->prepare("SELECT * FROM daily_vitals WHERE user_id = ? ORDER BY date DESC LIMIT 5");
$stmt->execute([$userId]);
$recentVitals = $stmt->fetchAll();

// Get upcoming reminders
$stmt = $pdo->prepare("SELECT * FROM reminders WHERE user_id = ? AND is_active = 1 ORDER BY reminder_time LIMIT 3");
$stmt->execute([$userId]);
$upcomingReminders = $stmt->fetchAll();

// Get recent journal entries
$stmt = $pdo->prepare("SELECT * FROM journal_entries WHERE user_id = ? ORDER BY entry_date DESC LIMIT 2");
$stmt->execute([$userId]);
$recentJournals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<main class="content">
    <h2>Welcome back, <?php echo htmlspecialchars($userData['full_name']); ?></h2>
    
    <!-- Daily Vitals Modal -->
    <?php if (!$todayVitals): ?>
    <div class="modal" id="vitalsModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Daily Health Check</h3>
            <p>Please provide your current health information:</p>
            
            <form id="vitalsForm">
                <div class="form-group">
                    <label>How are you feeling today?</label>
                    <div class="mood-options">
                        <input type="radio" name="mood" id="mood-happy" value="Happy" required>
                        <label for="mood-happy" class="mood-option" title="Happy">
                            <i class="fas fa-smile"></i>
                        </label>
                        
                        <input type="radio" name="mood" id="mood-sad" value="Sad">
                        <label for="mood-sad" class="mood-option" title="Sad">
                            <i class="fas fa-sad-tear"></i>
                        </label>
                        
                        <input type="radio" name="mood" id="mood-anxious" value="Anxious">
                        <label for="mood-anxious" class="mood-option" title="Anxious">
                            <i class="fas fa-flushed"></i>
                        </label>
                        
                        <input type="radio" name="mood" id="mood-angry" value="Angry">
                        <label for="mood-angry" class="mood-option" title="Angry">
                            <i class="fas fa-angry"></i>
                        </label>
                        
                        <input type="radio" name="mood" id="mood-tired" value="Tired">
                        <label for="mood-tired" class="mood-option" title="Tired">
                            <i class="fas fa-tired"></i>
                        </label>
                        
                        <input type="radio" name="mood" id="mood-neutral" value="Neutral">
                        <label for="mood-neutral" class="mood-option" title="Neutral">
                            <i class="fas fa-meh"></i>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="blood_pressure">Blood Pressure (mmHg)</label>
                    <div class="input-group">
                        <input type="number" id="blood_pressure_systolic" name="blood_pressure_systolic" placeholder="Systolic" required>
                        <span>/</span>
                        <input type="number" id="blood_pressure_diastolic" name="blood_pressure_diastolic" placeholder="Diastolic" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="heart_rate">Heart Rate (bpm)</label>
                    <input type="number" id="heart_rate" name="heart_rate" required>
                </div>
                
                <div class="form-group">
                    <label for="blood_sugar">Blood Sugar (mg/dL) - Optional</label>
                    <input type="number" step="0.1" id="blood_sugar" name="blood_sugar">
                </div>
                
                <div class="form-group">
                    <label for="feelings">How would you describe your feelings today?</label>
                    <textarea id="feelings" name="feelings" rows="3"></textarea>
                </div>
                
                <button type="submit" class="btn">Submit</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="dashboard-widgets">
        <div class="main-widgets">
            <div class="widget music">
                <h3><i class="fas fa-music"></i> Music Therapy</h3>
                <div class="music-player">
                    <div class="now-playing">
                        <p>Now Playing: <span id="current-song">Melodies of Recovery</span></p>
                    </div>
                    <div class="player-controls">
                        <button id="prev-song"><i class="fas fa-step-backward"></i></button>
                        <button id="play-pause"><i class="fas fa-play"></i></button>
                        <button id="next-song"><i class="fas fa-step-forward"></i></button>
                    </div>
                    <div class="volume-control">
                        <i class="fas fa-volume-down"></i>
                        <input type="range" id="volume" min="0" max="1" step="0.1" value="0.7">
                        <i class="fas fa-volume-up"></i>
                    </div>
                </div>
                <div class="music-suggestions">
                    <h4>Suggested for you:</h4>
                    <ul>
                        <?php foreach ($musicSuggestions as $song): ?>
                        <li data-song-id="<?php echo $song['id']; ?>">
                            <i class="fas fa-play-circle play-song"></i>
                            <?php echo htmlspecialchars($song['title']); ?> - <?php echo htmlspecialchars($song['artist']); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="widget quote">
                <h3><i class="fas fa-quote-left"></i> Daily Inspiration</h3>
                <blockquote>
                    <p><?php echo $randomQuote; ?></p>
                </blockquote>
                <button id="new-quote" class="btn-small">New Quote</button>
            </div>
        </div>
        
        <div class="secondary-widgets">
            <div class="widget reminders">
                <h3><i class="fas fa-bell"></i> Upcoming Reminders</h3>
                <?php if (!empty($upcomingReminders)): ?>
                    <ul class="reminder-list">
                        <?php foreach ($upcomingReminders as $reminder): ?>
                        <li>
                            <span class="reminder-time"><?php echo date('h:i A', strtotime($reminder['reminder_time'])); ?></span>
                            <span class="reminder-title"><?php echo htmlspecialchars($reminder['title']); ?></span>
                            <?php if (!empty($reminder['description'])): ?>
                                <p class="reminder-desc"><?php echo htmlspecialchars($reminder['description']); ?></p>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-reminders">No upcoming reminders</p>
                <?php endif; ?>
                <a href="reminder.php" class="btn-small">View All</a>
            </div>
            
            <div class="widget journal">
                <h3><i class="fas fa-book"></i> Recent Journal Entries</h3>
                <?php if (!empty($recentJournals)): ?>
                    <div class="journal-entries">
                        <?php foreach ($recentJournals as $entry): ?>
                        <div class="journal-entry">
                            <h4><?php echo htmlspecialchars($entry['title']); ?></h4>
                            <span class="entry-date"><?php echo date('M j', strtotime($entry['entry_date'])); ?></span>
                            <p><?php echo substr(htmlspecialchars($entry['content']), 0, 100); ?>...</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-entries">No journal entries yet</p>
                <?php endif; ?>
                <a href="journal.php" class="btn-small">View Journal</a>
            </div>
        </div>
    </div>
    
    <div class="quick-access">
        <h3>Quick Access</h3>
        <div class="quick-buttons">
            <a href="recreation.php" class="btn">Recreation</a>
            <a href="fitness.php" class="btn">Fitness</a>
            <a href="reminder.php" class="btn">Reminder</a>
            <a href="journal.php" class="btn">Journal</a>
        </div>
        <button class="accessibility-btn"><i class="fas fa-universal-access"></i> Accessibility</button>
    </div>
    
    <section class="vital-status">
        <h3><i class="fas fa-heartbeat"></i> Health Overview</h3>
        
        <?php if ($todayVitals): ?>
            <div class="today-vitals">
                <h4>Today's Status</h4>
                <div class="vitals-grid">
                    <div class="vital-item">
                        <span class="vital-label">Mood:</span>
                        <span class="vital-value mood-<?php echo strtolower($todayVitals['mood']); ?>">
                            <?php echo $todayVitals['mood']; ?>
                        </span>
                    </div>
                    <div class="vital-item">
                        <span class="vital-label">Blood Pressure:</span>
                        <span class="vital-value">
                            <?php echo $todayVitals['blood_pressure_systolic']; ?>/<?php echo $todayVitals['blood_pressure_diastolic']; ?> mmHg
                        </span>
                    </div>
                    <div class="vital-item">
                        <span class="vital-label">Heart Rate:</span>
                        <span class="vital-value">
                            <?php echo $todayVitals['heart_rate']; ?> bpm
                        </span>
                    </div>
                    <?php if ($todayVitals['blood_sugar']): ?>
                    <div class="vital-item">
                        <span class="vital-label">Blood Sugar:</span>
                        <span class="vital-value">
                            <?php echo $todayVitals['blood_sugar']; ?> mg/dL
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($todayVitals['feelings']): ?>
                    <div class="vital-notes">
                        <p><strong>Your notes:</strong> <?php echo htmlspecialchars($todayVitals['feelings']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>No health data recorded for today. Please complete your daily health check.</p>
        <?php endif; ?>
        
        <?php if ($recentVitals): ?>
            <div class="vitals-history">
                <h4>Recent Trends</h4>
                <div class="vitals-chart">
                    <!-- This would be replaced with a real chart using Chart.js or similar -->
                    <div class="chart-placeholder">
                        <p>Blood pressure and heart rate trends chart would display here</p>
                    </div>
                </div>
                <a href="health.php" class="btn">View Full Health History</a>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php
require_once 'footer.php';
?>