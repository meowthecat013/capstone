<?php
/**
 * Function to manipulate time for testing purposes
 * 
 * @param string $timeString The time to set (format: 'Y-m-d H:i:s' or relative time like '+1 day')
 * @param bool $persist Whether to persist the time change in session
 * @return string The current simulated time
 */
function manipulateTime($timeString = null, $persist = false) {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // If no time specified, return current time (real or simulated)
    if ($timeString === null) {
        return isset($_SESSION['simulated_time']) 
            ? $_SESSION['simulated_time'] 
            : date('Y-m-d H:i:s');
    }
    
    // If reset requested
    if ($timeString === 'reset') {
        unset($_SESSION['simulated_time']);
        return date('Y-m-d H:i:s');
    }
    
    // Check if it's a relative time (like "+1 hour")
    if (preg_match('/^[+-]\s*\d+\s*[a-z]+$/i', $timeString)) {
        $simulatedTime = isset($_SESSION['simulated_time']) 
            ? $_SESSION['simulated_time'] 
            : date('Y-m-d H:i:s');
        $newTime = strtotime($timeString, strtotime($simulatedTime));
    } else {
        // Absolute time
        $newTime = strtotime($timeString);
    }
    
    if ($newTime === false) {
        return "Invalid time format";
    }
    
    $formattedTime = date('Y-m-d H:i:s', $newTime);
    
    if ($persist) {
        $_SESSION['simulated_time'] = $formattedTime;
    }
    
    return $formattedTime;
}

// Example usage:
// manipulateTime('2023-01-15 08:00:00', true); // Set to specific time
// manipulateTime('+1 day', true); // Add 1 day to current simulated time
// manipulateTime('reset'); // Reset to real time
?>