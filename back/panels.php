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
    if (isset($_GET['selIdBrand']) || isset($_GET['selIdPanel']) || isset($_GET['selIdDep'])) { // no point in doing searches with no field filled
        // filtering cases where some search fields aren't set yet
        $selIdBrand = $_GET['selIdBrand'] ?? null;
        $selIdPanel = $_GET['selIdPanel'] ?? null;
        $selIdDep = $_GET['selIdDep'] ?? null;

        $sql = "SELECT 
            i.id, 
            i.nb_pann AS nb_panneaux, 
            i.surface, 
            i.puissance_crete, 
            CONCAT(LPAD(i.mois_installation, 2, '0'), '/', i.an_installation) AS date_installation, 
            c.com_nom AS localite,
            o.id_Marque_Onduleur,
            p.id_Marque_Panneau,
            c.id_Departement
            FROM Installation i
            JOIN Commune c ON i.id_Commune = c.id
            JOIN Onduleur o ON i.id_Onduleur = o.id
            JOIN Panneau p ON i.id_Panneau = p.id
            WHERE 1=1"; // placeholder jusst to start the WHERE statement
            // may remove o.id_Marque_Ondulateur and p.id_Marque_Panneau from selection
        
        // TODOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOO ajouter des champs de recherche FACULATIFS, parce que 3 champs de recherche
        // à la fois ont 99% de chance de retourner 0 résultats
        if ($selIdBrand) {
            $sql .= " AND o.id_Marque_Onduleur = $selIdBrand";
        }
        if ($selIdPanel) {
            $sql .= " AND p.id_Marque_Panneau = $selIdPanel";
        }
        if ($selIdDep) {
            $sql .= " AND c.id_Departement = $selIdDep";
        }
        $sql .= ";";

        $returnJson = [
            "sql" => $sql // testing
        ];

        // fetching from MySQL after filtering
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $returnJson['results'] = $results;
        echo json_encode($returnJson);
    }
    exit;
}
catch (Exception $e) {
    // because the js wants json
    $resp = [
        "error" => "Error loading block",
        "message" => $e->getMessage()
    ];

    echo json_encode($resp);
}
?>