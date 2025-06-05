
<?php
$dsn = "mysql:host=localhost;dbname=solaire;charset=utf8mb4";
$username = "admin";
$password = "isen44";

try {
    $pdo = new PDO($dsn, $username, $password);
    // Enable error mode for debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set charset explicitly for MariaDB compatibility
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    echo "\n";
    exit;
}
?>