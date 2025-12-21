<?php
/* =========================================================
   CloudConnect Database Connection
   - Auto detects Azure vs Localhost
   - Safe fallback mechanism
========================================================= */

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

/* -------------------------------
   AZURE MYSQL CONFIG
-------------------------------- */
$azure = [
    'host' => 'cloudconnectserver.mysql.database.azure.com',
    'db'   => 'cloudconnect',
    'user' => 'cloudadmin@cloudconnectserver',
    'pass' => 'admin@123'
];

/* -------------------------------
   LOCALHOST (XAMPP) CONFIG
-------------------------------- */
$local = [
    'host' => 'localhost',
    'db'   => 'cloudconnect',
    'user' => 'root',
    'pass' => ''
];

/* -------------------------------
   CONNECTION LOGIC
-------------------------------- */
try {
    // Try Azure first
    $pdo = new PDO(
        "mysql:host={$azure['host']};dbname={$azure['db']};charset=utf8mb4",
        $azure['user'],
        $azure['pass'],
        $options
    );

} catch (PDOException $e) {

    try {
        // Fallback to Localhost
        $pdo = new PDO(
            "mysql:host={$local['host']};dbname={$local['db']};charset=utf8mb4",
            $local['user'],
            $local['pass'],
            $options
        );

    } catch (PDOException $e2) {
        // Final failure (safe message)
        die("Database connection failed. Please try again later.");
    }
}