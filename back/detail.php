<?php

header('Content-Type: application/json');



// Include the database connection
require_once __DIR__ . '/db.php';

try {
  if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    throw new Exception("ID d'installation invalide");
  }

    $installId = intval($_GET["id"]);
    $sql = 
    "SELECT i.id, i.nb_pann, i.nb_ond, CONCAT(LPAD(i.mois_installation, 2, '0'), '/', i.an_installation) AS date_installation, i.surface, i.puissance_crete, i.lat, i.lon, i.ori, i.ori_opti, i.pente, i.pente_opti, i.prod_pvgis, i.code_postal, c.com_nom, d.dep_nom, r.dep_reg, p.pays_nom, mqo.nom, mdo.nom, mqpn.nom, mdpn.nom, inst.install_nom
    FROM Installation i
    LEFT JOIN Commune c ON i.id_Commune = c.id
    LEFT JOIN Departement d ON c.id_Departement = d.id
    LEFT JOIN Region r ON d.id_Region = r.id
    LEFT JOIN Pays p ON r.id_Pays = p.id
    LEFT JOIN Onduleur o ON i.id_Onduleur = o.id
    LEFT JOIN Marque_Onduleur mqo ON o.id_Marque_Onduleur = mqo.id
    LEFT JOIN Modele_Onduleur mdo ON o.id_Modele_Onduleur = mdo.id
    LEFT JOIN Panneau pn ON i.id_Panneau = pn.id
    LEFT JOIN Marque_Panneau mqpn ON pn.id_Marque_Panneau = mqpn.id
    LEFT JOIN Modele_Panneau mdpn ON pn.id_Modele_Panneau = mdpn.id
    LEFT JOIN Installateur inst ON i.id_Installateur = inst.id
    
    WHERE i.id = ?;";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$installId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        throw new Exception("Installation non trouvée v1S");
    }
    $resp = [
        "id" => [],
        "nb_pann" => [],
        "nb_ond" => [],
        "date_install" => [],
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
    
    if (!$row) {
        throw new Exception("Installation non trouvée");
    }
    $resp = [
        "id" => (int)$row['i.id'],
        "date_install" => $row['date_installation'],
        "nb_pann" => (int)$row['i.nb_pann'],
        "nb_ond" => (int)$row['i.nb_ond'],
        "surface" => (float)$row['i.surface'],
        "puissance_crete" => (float)$row['i.puissance_crete'],
        "lat" => (float)$row['i.lat'],
        "lon" => (float)$row['i.lon'],
        "ori" => (float)$row['i.ori'],
        "ori_opti" => (float)$row['i.ori_opti'],
        "pente" => (float)$row['i.pente'],
        "pente_opti" => (float)$row['i.pente_opti'],
        "prod_pvgis" => (float)$row['i.prod_pvgis'],
        "code_postal" => $row['i.code_postal'],
        "com_nom" => $row['c.com_nom'],
        "dep_nom" => $row['d.dep_nom'],
        "reg_nom" => $row['r.dep_reg'],
        "pays_nom" => $row['p.pays_nom'],
        "marque_ond" => $row['mqo.nom'],
        "modele_ond" => $row['mdo.nom'],
        "marque_pn" => $row['mqpn.nom'],
        "modele_pn" => $row['mdpn.nom'],
        "nom_installateur" => $row['inst.install_nom']
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