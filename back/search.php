<?php
/*
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
*/

// Include the database connection
require_once __DIR__ . '/db.php';

try {
  $sql = "SELECT DISTINCT nom FROM Marque_Ondulateur ORDER BY nom ASC LIMIT 21";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $resp = [
    "values" => [],
    "marques" => []
  ];
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($data as $row) {
    $marque = htmlspecialchars($row['nom']);
    
    $resp["values"][] = $marque; // TODO use id
    $resp["marques"][] = $marque;
  }
  echo json_encode($resp);
  
  /*echo json_encode([
    "value" => "1",
    "marque" => "1"
  ]);*/
  exit;
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