<?php
$dsn = "mysql:host=localhost;dbname=solaire;charset=utf8mb4";
$username = "admin";
$password = "isen44";

$dsnP = "psql:host=localhost;dbname=solaire;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    // Enable error mode for debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set charset explicitly for MariaDB compatibility
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {

    // Add debug information to see the actual error
    error_log("Database connection error: " . $e->getMessage());
    
    // Only set response code if headers haven't been sent
    if (!headers_sent()) {
        http_response_code(500);
    }
    
    echo json_encode([
        "error" => "Database connection failed",
        "details" => $e->getMessage()
    ]);
    exit;
}
?>