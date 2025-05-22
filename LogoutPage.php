<?php
// Set a custom session save path
session_save_path(__DIR__ . '/tmp_sessions'); 
session_start();

// Check if a session is active before attempting to destroy it
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset(); // Clear session variables
    session_destroy(); // Destroy the session
}


// Redirect to the login page
header("Location: LoginPage.php");
exit();
?>