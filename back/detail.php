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
    "SELECT i.id as id,
    i.nb_pann as nb_pann,
    i.nb_ond as nb_ond,
    CONCAT(LPAD(i.mois_installation, 2, '0'), '/', i.an_installation) as date_installation,
    i.surface as surface,
    i.puissance_crete as puissance_crete,
    i.lat as lat,
    i.lon as lon,
    i.ori as ori,
    i.ori_opti as ori_opti,
    i.pente as pente,
    i.pente_opti as pente_opti,
    i.prod_pvgis as prod_pvgis,
    i.code_postal as code_postal,
    c.com_nom as com_nom,
    d.dep_nom as dep_nom,
    r.dep_reg as reg_nom,
    p.pays_nom as pays_nom,
    mqo.nom as marque_ond,
    mdo.nom as modele_ond,
    mqpn.nom as marque_pan,
    mdpn.nom as modele_pan,
    inst.install_nom as install_nom
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
        "id" => (int)$row['id'],
        "date_install" => $row['date_installation'],
        "nb_pann" => (int)$row['nb_pann'],
        "nb_ond" => (int)$row['nb_ond'],
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
        "reg_nom" => $row['reg_nom'],
        "pays_nom" => $row['pays_nom'],
        "marque_ond" => $row['marque_ond'],
        "modele_ond" => $row['modele_ond'],
        "marque_pn" => $row['marque_pan'],
        "modele_pn" => $row['modele_pan'],
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