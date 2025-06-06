<?php
// Set header to return JSON
header('Content-Type: application/json');

// Include the database connection
require_once __DIR__ . '/db.php';

try {
    // Initialize response array
    $response = [
        "enregistrements" => 0,
        "regions" => 0,
        "installateurs" => 0,
        "regionsAnnee" => 0,
        "annee" => 0,
        "marques" => 0,
        "onduleurs" => 0,
        "modelePanneau" => 0,
        "onduleursTotal" => 0
    ];

    // Total enregistrements en base
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Installation");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["enregistrements"] = (int)$result['total'];


    // Installations par région (count distinct regions from Installation via Commune)
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT r.id) as total 
        FROM Installation i 
        JOIN Commune c ON i.id_Commune = c.id 
        JOIN Departement d ON c.id_Departement = d.id 
        JOIN Region r ON d.id_Region = r.id
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["regions"] = (int)$result['total'];

    // Total installateurs
    $stmt = $pdo->query("SELECT COUNT(DISTINCT id_Installateur) AS total FROM Installation WHERE id_Installateur IS NOT NULL");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["installateurs"] = (int)$result['total'];

    // Installations par année (count installations for current year)
    // $currentYear = date('Y');
    $targetYear = 2016;
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM Installation WHERE an_installation = ?");
    // $stmt->execute([$currentYear]);
    $stmt->execute([$targetYear]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["annee"] = (int)$result['total'];

    // Installations d'une région par année (example for a specific region)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM Installation i 
        JOIN Commune c ON i.id_Commune = c.id 
        JOIN Departement d ON c.id_Departement = d.id 
        JOIN Region r ON d.id_Region = r.id 
        WHERE i.an_installation = ? 
        LIMIT 1
    ");
    $stmt->execute([$targetYear]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["regionsAnnee"] = (int)$result['total'];

    // Marques de panneaux total
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Marque_Panneau");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["marques"] = (int)$result['total'];

    // Marques d'onduleurs total
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Marque_Onduleur");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["onduleurs"] = (int)$result['total'];

    // Nombre total de Modele de Panneau
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Modele_Panneau");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["modelePanneau"] = (int)$result['total'];
    
    // Nombre d'onduleurs total
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM Onduleur");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["onduleursTotal"] = (int)$result['total'];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur API : " . $e->getMessage()]);
    exit;
}