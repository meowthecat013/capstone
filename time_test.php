<?php
require_once 'time_helpers.php';

// Check for actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'set':
            $time = manipulateTime($_GET['time'], true);
            echo "Time set to: $time";
            break;
        case 'add':
            $time = manipulateTime($_GET['time'], true);
            echo "Time adjusted to: $time";
            break;
        case 'reset':
            $time = manipulateTime('reset');
            echo "Time reset to real time: $time";
            break;
        case 'check':
            $time = manipulateTime();
            echo "Current time: $time";
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Time Manipulation Test</title>
</head>
<body>
    <h1>Time Manipulation Test</h1>
    
    <h2>Current Time</h2>
    <p id="current-time"><?php echo manipulateTime(); ?></p>
    <button onclick="fetchTime('check')">Refresh</button>
    
    <h2>Set Absolute Time</h2>
    <form onsubmit="setTime(event)">
        <input type="datetime-local" name="time" required>
        <button type="submit">Set Time</button>
    </form>
    
    <h2>Adjust Time</h2>
    <button onclick="adjustTime('+1 hour')">+1 Hour</button>
    <button onclick="adjustTime('+1 day')">+1 Day</button>
    <button onclick="adjustTime('-1 hour')">-1 Hour</button>
    
    <h2>Reset</h2>
    <button onclick="resetTime()">Reset to Real Time</button>
    
    <script>
    function fetchTime(action, time = '') {
        fetch(`time_test.php?action=${action}${time ? '&time=' + encodeURIComponent(time) : ''}`)
            .then(response => response.text())
            .then(text => {
                alert(text);
                document.getElementById('current-time').textContent = text.split(': ').pop();
            });
    }
    
    function setTime(e) {
        e.preventDefault();
        const timeValue = e.target.time.value + ':00';
        fetchTime('set', timeValue);
    }
    
    function adjustTime(adjustment) {
        fetchTime('add', adjustment);
    }
    
    function resetTime() {
        fetchTime('reset');
    }
    </script>
</body>
</html>