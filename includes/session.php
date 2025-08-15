<?php
// Start or resume session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout to 30 minutes
$session_timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session has expired, destroy it
    session_unset();
    session_destroy();
    
    // Redirect to login page with session timeout message
    if (!isset($no_redirect) || !$no_redirect) {
        header("Location: login.php?timeout=1");
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
