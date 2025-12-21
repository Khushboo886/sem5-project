<?php
/* =========================================================
   CloudConnect Database Connection
   - Azure (No SSL)
   - Localhost fallback
========================================================= */

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

/* -------------------------------
   AZURE MYSQL CONFIG (NO SSL)
-------------------------------- */
$azure = [
    'host' => 'cloudconnectserver.mysql.database.azure.com',
    'db'   => 'cloudconnect',
    'user' => 'cloudadmin@cloudconnectserver',
    'pass' => 'admin@123',
    'dsn'  => "mysql:host=cloudconnectserver.mysql.database.azure.com;
               dbname=cloudconnect;
               charset=utf8mb4;
               sslmode=DISABLED"
];

/* -------------------------------
   LOCALHOST CONFIG
-------------------------------- */
$local = [
    'dsn'  => "mysql:host=localhost;dbname=cloudconnect;charset=utf8mb4",
    'user' => 'root',
    'pass' => ''
];

/* -------------------------------
   CONNECTION LOGIC
-------------------------------- */
try {
    // Try Azure first
    $pdo = new PDO(
        $azure['dsn'],
        $azure['user'],
        $azure['pass'],
        $options
    );
} catch (PDOException $e) {

    try {
        // Fallback to Localhost
        $pdo = new PDO(
            $local['dsn'],
            $local['user'],
            $local['pass'],
            $options
        );
    } catch (PDOException $e2) {
        die("Database connection failed. Please try again later.");
    }
}