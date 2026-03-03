<?php
/* ============================================
   CloudConnect SQLite Database (Azure Free)
============================================ */

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=CloudConnect_db;charset=utf8mb4",
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    error_log("DB ERROR: " . $e->getMessage());
    die("Database connection failed.");