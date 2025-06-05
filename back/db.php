<?php
$host = 'localhost';      // ou l'adresse IP du serveur MySQL
$dbname = 'solaire';
$username = 'admin';
$password = 'isen44';
 
try {
    // Connexion à MySQL avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
   
    // Configuration des attributs PDO pour afficher les erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
    echo "Connexion réussie à la base de données.";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}

?>