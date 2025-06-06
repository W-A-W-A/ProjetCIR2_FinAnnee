<?php
/*
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
*/

// Include the database connection
require_once __DIR__ . '/db.php';


function getOndBrands($limit = 20) {
  global $pdo;
  $sql = "SELECT DISTINCT id, nom FROM Marque_Ondulateur ORDER BY nom ASC LIMIT $limit";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $resp = [
    "values" => [],
    "names" => []
  ];
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($data as $row) {
    $id = htmlspecialchars($row['id']);
    $brand = htmlspecialchars($row['nom']);
    
    $resp["values"][] = $id;
    $resp["names"][] = $brand;
  }
  return $resp;
}

function getPanelBrands($limit = 20) {
  global $pdo;
  $sql = "SELECT DISTINCT id, nom FROM Marque_Panneau ORDER BY nom ASC LIMIT $limit";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $resp = [
    "values" => [],
    "names" => []
  ];
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($data as $row) {
    $id = htmlspecialchars($row['id']);
    $brand = htmlspecialchars($row['nom']);
    
    $resp["values"][] = $id;
    $resp["names"][] = $brand;
  }
  return $resp;
}

function getDeps($limit = 20) {
  global $pdo;
  $sql = "SELECT DISTINCT id, dep_nom FROM Departement ORDER BY dep_nom ASC LIMIT $limit";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $resp = [
    "values" => [],
    "names" => []
  ];
  $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($data as $row) {
    $id = htmlspecialchars($row['id']);
    $brand = htmlspecialchars($row['dep_nom']);
    
    $resp["values"][] = $id;
    $resp["names"][] = $brand;
  }
  return $resp;
}

try {
  
  echo json_encode([
    "ondBrand" => getOndBrands(),
    "panelBrand" => getPanelBrands(),
    "department" => getDeps()
  ]);
  
  /*echo json_encode([
    "value" => "1",
    "marque" => "1"
  ]);*/
  exit;
}
catch (Exception $e) {
  // because the js wants json
  $resp = [
    "error" => "Error loading brands and departments",
    "message" => $e->getMessage()
  ];

  echo json_encode($resp);
}
?>