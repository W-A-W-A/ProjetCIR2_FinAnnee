<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET ');
header('Access-Control-Allow-Headers: Content-Type');


// Include the database connection
require_once __DIR__ . '/db.php';

// get 20 brands of ondulators
function getOndBrands($limit = 20) {

  global $pdo;
  $sql = "SELECT DISTINCT id, nom FROM Marque_Onduleur ORDER BY RAND() LIMIT $limit";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $resp = [
    "values" => [null],
    "names" => [" "]
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

// get 20 brands of panels
function getPanelBrands($limit = 20) {
  global $pdo;
  $sql = "SELECT DISTINCT id, nom FROM Marque_Panneau ORDER BY RAND() LIMIT $limit";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $resp = [
    "values" => [null],
    "names" => [" "]
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

// get 20 departments
function getDeps($limit = 20) {
  global $pdo;
  $sql = "SELECT DISTINCT id, dep_nom FROM Departement ORDER BY RAND() LIMIT $limit";
  
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $resp = [
    "values" => [null],
    "names" => [" "]
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
  // returns all collected data as json
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