<?php
require_once 'db.php'; // Assure you have a PDO $pdo object in db.php

try {
  $stmt = $pdo->query("SELECT DISTINCT marque FROM onduleurs ORDER BY marque ASC LIMIT 21");
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $marque = htmlspecialchars($row['marque']);

    $resp = [
      "value" => $marque,
      "marque" => $marque
    ];

    echo json_encode($resp);
  }
}
catch (Exception $e) {
  // parce que le JS veux du json
  $resp = [
    "error" => "Erreur de chargement des marques",
    "message" => $e->getMessage()
  ];

  echo json_encode($resp);
}
?>