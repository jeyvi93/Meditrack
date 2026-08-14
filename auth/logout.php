<?php
session_start();

// Remove all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect back to login page
header("Location: ../index.php");
exit();
?>