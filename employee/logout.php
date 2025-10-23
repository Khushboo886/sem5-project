<?php
require_once '../includes/session.php'; // go up one directory
session_start();

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ../login.php');
exit();
