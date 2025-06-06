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
    "SELECT i.id, i.an_installation, i.nb_pann, i.nb_ond, i.mois_installation, i.surface, i.puissance_crete, i.lat, i.lon, i.ori, i.ori_opti, i.pente, i.pente_opti, i.prod_pvgis, i.code_postal, c.com_nom, d.dep_nom, r.dep_reg, p.pays_nom, mqo.nom, mdo.nom, mqpn.nom, mdpn.nom, inst.install_nom FROM Installation i WHERE i.id = \"$instId\"
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
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($data as $row) {
        $id = htmlspecialchars($row['i.id']);
        $anInst = htmlspecialchars($row['i.an_installation']);
        $nbPann = htmlspecialchars($row['i.nb_pann']);
        $nbOnd = htmlspecialchars($row['i.nb_ond']);
        $moInst = htmlspecialchars($row['i.mois_installation']);
        $surface = htmlspecialchars($row['i.surface']);
        $pCrete = htmlspecialchars($row['i.puissance_crete']);
        $prodPvgis = htmlspecialchars($row['i.prod_pvgis']);
        $lat = htmlspecialchars($row['i.lat']);
        $lon = htmlspecialchars($row['i.lon']);
        $ori = htmlspecialchars($row['i.ori']);
        $oriOpti = htmlspecialchars($row['i.ori_opti']);
        $pente = htmlspecialchars($row['i.pente']);
        $penteOpti = htmlspecialchars($row['i.pente_opti']);
        $marquePan = htmlspecialchars($row['mqpn.nom']);
        $modelePan = htmlspecialchars($row['mdpn.nom']);
        $marqueOnd = htmlspecialchars($row['mqo.nom']);
        $modeleOnd = htmlspecialchars($row['mdo.nom']);
        $instNom = htmlspecialchars($row['inst.install_nom']);
        $comNom = htmlspecialchars($row['c.com_nom']);
        $depNom = htmlspecialchars($row['d.dep_nom']);
        $regNom = htmlspecialchars($row['r.dep_reg']);
        $paysNom = htmlspecialchars($row['p.pays_nom']);
        $cp = htmlspecialchars($row['i.code_postal']);

        $resp["id"][] = $id;
        $resp["an_installation"][] = $anInst;
        $resp["nb_pann"][] = $nbPann;
        $resp["nb_ond"][] = $nbOnd;
        $resp["mois_installation"][] = $moInst;
        $resp["surface"][] = $surface;
        $resp["puissance_crete"][] = $pCrete;
        $resp["prod_pvgis"][] = $prodPvgis;
        $resp["lat"][] = $lat;
        $resp["lon"][] = $lon;
        $resp["ori"][] = $ori;
        $resp["ori_opti"][] = $oriOpti;
        $resp["pente"][] = $pente;
        $resp["pente_opti"][] = $penteOpti;
        $resp["marque_pan"][] = $marquePan;
        $resp["modele_pan"][] = $modelePan;
        $resp["marque_ond"][] = $marqueOnd;
        $resp["modele_ond"][] = $modeleOnd;
        $resp["nom_installateur"][] = $instNom;
        $resp["com_nom"][] = $comNom;
        $resp["dep_nom"][] = $depNom;
        $resp["reg_nom"][] = $regNom;
        $resp["pays_nom"][] = $paysNom;
        $resp["code_postal"][] = $cp;
    }
  echo json_encode($resp);
  exit;
}
catch (Exception $e) {
  // parce que le JS veux du json
  $resp = [
    "error" => "Erreur de chargement des données",
    "message" => $e->getMessage()
  ];

  echo json_encode($resp);
}
?>