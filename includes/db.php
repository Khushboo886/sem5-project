<?php
// includes/db.php

if ($_SERVER['SERVER_NAME'] === 'localhost') {
    // LOCAL DATABASE (XAMPP)
    $host = "localhost";
    $db   = "cloudconnect";
    $user = "root";
    $pass = "";
} else {
    // AZURE DATABASE
    $host = "cloudconnectserver.mysql.database.azure.com";
    $db   = "cloudconnect";
    $user = "cloudadmin@cloudconnectserver";
    $pass = "admin@123"; // (later move to env variable)
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
