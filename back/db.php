
<?php
$dsn = "mysql:host=localhost;dbname=solaire;charset=utf8";
$username = "admin";
$password = "isen44";

try {
    $pdo = new PDO($dsn, $username, $password);
    //enable error mode for debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    echo "\n";
    exit;
}
?>