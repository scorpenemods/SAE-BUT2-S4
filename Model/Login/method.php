<?php
session_start();

function checkSession($timeout_duration = 1800) {
    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    // Check if session has expired
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        session_unset();
        session_destroy();
        header('Location: Model\Login\login.php');
        exit();
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
}
?>