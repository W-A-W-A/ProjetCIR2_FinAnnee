<?php

header('Content-Type: application/json');



// Include the database connection
require_once __DIR__ . '/db.php';

try {
  if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    throw new Exception("ID d'installation invalide");
  }

    $installId = (int)$_GET["id"];
    $sql = 
    "SELECT i.id, i.an_installation, i.nb_pann, i.nb_ond, i.mois_installation, i.surface, i.puissance_crete, i.lat, i.lon, i.ori, i.ori_opti, i.pente, i.pente_opti, i.prod_pvgis, i.code_postal, c.com_nom, d.dep_nom, r.dep_reg, p.pays_nom, mqo.nom, mdo.nom, mqpn.nom, mdpn.nom, inst.install_nom
    FROM Installation i
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
    JOIN Installateur inst ON i.id_Installateur = inst.id
    WHERE i.id = ?;";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$installId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        throw new Exception("Installation non trouvée");
    }
    $resp = [
        "id" => [],
        "an_installation" => [],
        "nb_pann" => [],
        "nb_ond" => [],
        "mois_installation" => [],
        "surface" => [],
        "puissance_crete" => [],
        "lat" => [],
        "lon" => [],
        "ori" => [],
        "ori_opti" => [],
        "pente" => [],
        "pente_opti" => [],
        "prod_pvgis" => [],
        "code_postal" => [],
        "com_nom" => [],
        "dep_nom" => [],
        "reg_nom" => [],
        "pays_nom" => [],
        "marque_ond" => [],
        "modele_ond" => [],
        "marque_pn" => [],
        "modele_pn" => [],
        "nom_installateur" => []
    ];
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        throw new Exception("Installation non trouvée");
    }
    $resp = [
        "id" => (int)$row['id'],
        "an_installation" => (int)$row['an_installation'],
        "nb_pann" => (int)$row['nb_pann'],
        "nb_ond" => (int)$row['nb_ond'],
        "mois_installation" => (int)$row['mois_installation'],
        "surface" => (float)$row['surface'],
        "puissance_crete" => (float)$row['puissance_crete'],
        "lat" => (float)$row['lat'],
        "lon" => (float)$row['lon'],
        "ori" => (float)$row['ori'],
        "ori_opti" => (float)$row['ori_opti'],
        "pente" => (float)$row['pente'],
        "pente_opti" => (float)$row['pente_opti'],
        "prod_pvgis" => (float)$row['prod_pvgis'],
        "code_postal" => $row['code_postal'],
        "com_nom" => $row['com_nom'],
        "dep_nom" => $row['dep_nom'],
        "reg_nom" => $row['dep_reg'],
        "pays_nom" => $row['pays_nom'],
        "marque_ond" => $row['marque_onduleur'],
        "modele_ond" => $row['modele_onduleur'],
        "marque_pan" => $row['marque_panneau'],
        "modele_pan" => $row['modele_panneau'],
        "nom_installateur" => $row['install_nom']
    ];
    
    echo json_encode($resp);
}
catch (Exception $e) {
  http_response_code(500);
  // parce que le JS veux du json
  $resp = [
    "error" => "Erreur de chargement des données",
    "message" => $e->getMessage()
  ];

  echo json_encode($resp);
}
?>