<?php

$host = 'localhost'; // connection a la mariadb 
$db   = 'solaire';
$user = 'monuser';
$altUser = 'admin'; // used during testing
$pass = 'isen44';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC 
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, $options);
} catch (PDOException $e) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $altUser, $pass, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erreur de connexion : " . $e->getMessage()]);
        exit;
    }
}
