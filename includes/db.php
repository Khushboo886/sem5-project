<?php
/* ============================================
   CloudConnect Database Connection (FINAL)
============================================ */

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

/* Detect Azure environment */
$isAzure = isset($_SERVER['WEBSITE_INSTANCE_ID']);

try {

    if ($isAzure) {
        // AZURE MYSQL
        $pdo = new PDO(
            "mysql:host=cloudconnectserver.mysql.database.azure.com;dbname=cloudconnect;charset=utf8mb4",
            "cloudadmin@cloudconnectserver",
            "admin@123",
            $options
        );
    } else {
        // LOCALHOST (XAMPP)
        $pdo = new PDO(
            "mysql:host=localhost;dbname=cloudconnect;charset=utf8mb4",
            "root",
            "",
            $options
        );
    }

} catch (PDOException $e) {
    error_log("DB ERROR: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
