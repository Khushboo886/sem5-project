<?php
/* ============================================
   CloudConnect SQLite Database (Azure Free)
============================================ */

try {
    $db = new PDO(
        "sqlite:" . __DIR__ . "/../data.sqlite",
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    error_log("DB ERROR: " . $e->getMessage());
    die("Database connection failed.");
}
