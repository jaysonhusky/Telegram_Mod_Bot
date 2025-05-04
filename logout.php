<?php
// logout.php - Logout script for the admin
session_start();

// Destroy the session to log out
session_destroy();

// Redirect to the login page
header('Location: login.php');
exit;
?>
