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
    $instId = $_GET["id"];
    $sql = 
    "SELECT i.id, i.an_installation, i.nb_pann, i.nb_ond, i.mois_installation, i.surface, i.puissance_crete, i.lat, i.lon, i.ori, i.ori_opti, i.pente, i.pente_opti, i.prod_pvgis, i.code_postal, c.com_nom, d.dep_nom, r.dep_reg, p.pays_nom, mqo.nom, mdo.nom, mqpn.nom, mdpn.nom  FROM Installation i WHERE i.id = \"$instId\"
    JOIN Commune c ON i.id_Commune = c.id
    JOIN Departement d ON = c.id_Departement = d.id
    JOIN Region r ON d.id_Region = r.id
    JOIN Pays p ON r.id_Pays = p.id
    JOIN Onduleur o ON i.id_Onduleur = o.id
    JOIN Marque_Onduleur mqo ON o.id_Marque_Onduleur = mqo.id
    JOIN Modele_Onduleur mdo ON o.id_Modele_Onduleur = mdo.id
    JOIN Panneau pn ON i.id_Panneau = pn.id
    JOIN Marque_Panneau mqpn ON pn.id_Marque_Panneau = mqpn.id
    JOIN Modele_Panneau mdpn ON pn.id_Modele_Panneau = mdpn.id
    JOIN Installateur inst ON i.id_Installateur = inst.id;";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $resp = [
        "id" => [],
        "an_inst" => [],
        "nb_pann" => [],
        "nb_ond" => [],
        "mois_inst" => [],
        "surface" => [],
        "p_crete" => [],
        "lat" => [],
        "lon" => [],
        "ori" => [],
        "ori_opt" => [],
        "pente" => [],
        "pente_opt" => [],
        "prod" => [],
        "code_postal" => [],
        "id_pann" => [],
        "id_ond" => [],
        "id_inst" => [],
        "id_com" => [],
    ];
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($data as $row) {
        $id = htmlspecialchars($row['id']);
        $anInst = htmlspecialchars($row['nom']);
        $nbPann = htmlspecialchars($row['id']);
        $nbOnd = htmlspecialchars($row['nom']);
        $id = htmlspecialchars($row['id']);
        $brand = htmlspecialchars($row['nom']);
        $id = htmlspecialchars($row['id']);
        $brand = htmlspecialchars($row['nom']);
        $id = htmlspecialchars($row['id']);
        $brand = htmlspecialchars($row['nom']);
        $id = htmlspecialchars($row['id']);
        $brand = htmlspecialchars($row['nom']);
        
        $resp["values"][] = $id;
        $resp["brands"][] = $brand;
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