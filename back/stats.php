<?php
// Set header to return JSON
header('Content-Type: application/json');

// Include the database connection (reuse it across all APIs)
require_once __DIR__ . '/../back/db.php';

// fetch dynamic stats from a table named `installations`
try {
    // Total installations
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM installations");
    $totalInstallations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Enregistrements en base total
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM etc");
    $totalInputs;

    //Installations par region 
    $stmt = $pdo->query("SELECT COUNT(*) FROM installations WHERE region = 'Occitanie'");
    $totalRegion = $stmt->fetchColumn();

    //installateurs en total
    // $stmt;
    // $totalinstallateurs

    //installations par année
    // $stmt ;
    // $totalInstallationsByYear

    //installation en total
    // $stmt ;
    // $totalInstallations;

    //installationsn d'une region quelconque par année
    // $stmt ;
    // $totalInstallationsFromRegionByYear

    //marques de panneaux total
    // $stmt ;
    // $totalBrandsPann

    //marques de onduleurs total
    // $stmt ;
    // $totalBrandsOnd

    //enregistrements en base total par année
    // $stmt ;
    // $totalInputsByYear


    // Installations by region (simplified example)
    

    // Create response
    $response = [
        "enregistrements" => $totalInputs,
        "regions" => $totalRegiion,
        "base" => $totalInstallations,
        "installateurs" => $totalinstallateurs,
        "par_annee" => $totalInstallationsByYear,
        "region_par_annee" => $totalInstallationsFromRegionByYear,
        "panneaux_marques" => $totalBrandsPann,
        "onduleurs" => $totalBrandsOnd,
        "base_par_annee" => $totalInputsByYear
    ];


    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur API : " . $e->getMessage()]);
    exit;
}
