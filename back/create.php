<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/db.php';

// Checks if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Default response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

$isEdit = false;
$installation = null;

// Checks if we are editing data (data ID passed through GET or POST)
$id = null;
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $isEdit = true;
} elseif (isset($_POST['id']) && !empty($_POST['id'])) {
    $id = intval($_POST['id']);
    $isEdit = true;
}

if ($isEdit && $id) {
    // Gather data using join SQL requests
    try {
        $sql = "SELECT i.id, i.an_installation, i.nb_pann, i.nb_ond, i.mois_installation, i.surface, 
                       i.puissance_crete, i.lat, i.lon, i.ori, i.ori_opti, i.pente, i.pente_opti, 
                       i.prod_pvgis, i.code_postal, c.com_nom, d.dep_nom, r.dep_reg, p.pays_nom, 
                       mqo.nom as marque_onduleur, mdo.nom as modele_onduleur, 
                       mqpn.nom as marque_panneau, mdpn.nom as modele_panneau, inst.install_nom,
                       i.id_Commune, i.id_Onduleur, i.id_Panneau, i.id_Installateur,
                       o.id_Marque_Onduleur, o.id_Modele_Onduleur, 
                       pn.id_Marque_Panneau, pn.id_Modele_Panneau
                FROM Installation i
                JOIN Commune c ON i.id_Commune = c.id
                JOIN Departement d ON c.id_Departement = d.id
                JOIN Region r ON d.id_Region = r.id
                JOIN Pays p ON r.id_Pays = p.id
                JOIN Onduleur o ON i.id_Onduleur = o.id
                JOIN Marque_Onduleur mqo ON o.id_Marque_Onduleur = mqo.id
                JOIN Modele_Onduleur mdo ON o.id_Modele_Onduleur = mdo.id
                JOIN Panneau pn ON i.id_Panneau = pn.id
                JOIN Marque_Panneau mqpn ON pn.id_Marque_Panneau = mqpn.id
                JOIN Modele_Panneau mdpn ON pn.id_Modele_Panneau = mdpn.id
                JOIN Installateur inst ON i.id_Installateur = inst.id
                WHERE i.id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $installation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$installation) {
            $response['message'] = "Installation introuvable avec l'ID : " . $id;
            if ($isAjax) {
                echo json_encode($response);
                exit;
            }
            $isEdit = false;
        }
    } catch(Exception $e) {
        $response['message'] = "Erreur lors de la récupération des données : " . $e->getMessage();
        if ($isAjax) {
            echo json_encode($response);
            exit;
        }
        $isEdit = false;
    }
}

// Gets form data (creation or update)
if ($_POST && (isset($_POST['create']) || isset($_POST['update']))) {
    try {
        $installateur = trim($_POST['installateur'] ?? '');
        $nb_panneaux = intval($_POST['nb_panneaux'] ?? 0);
        $modele_panneaux = trim($_POST['modele_panneaux'] ?? '');
        $marque_panneaux = trim($_POST['marque_panneaux'] ?? '');
        $nb_onduleurs = intval($_POST['nb_onduleurs'] ?? 0);
        $modele_onduleurs = trim($_POST['modele_onduleurs'] ?? '');
        $marque_onduleurs = trim($_POST['marque_onduleurs'] ?? '');
        $surface = floatval($_POST['surface'] ?? 0);
        $date_installation = $_POST['date_installation'] ?? '';
        $commune = trim($_POST['commune'] ?? '');
        $departement = trim($_POST['departement'] ?? '');
        $code_postal = intval($_POST['code_postal'] ?? 0);
        $region = trim($_POST['region'] ?? '');
        $pays = trim($_POST['pays'] ?? 'France');
        $coordonnees_gps = trim($_POST['coordonnees_gps'] ?? '');
        $puissance_crete = floatval($_POST['puissance_crete'] ?? 0);
        $production_pvgis = floatval($_POST['production_pvgis'] ?? 0);
        $pente = floatval($_POST['pente'] ?? 0);
        $pente_optimum = floatval($_POST['pente_optimum'] ?? 0);
        $orientation = floatval($_POST['orientation'] ?? 0);
        $orientation_optimum = floatval($_POST['orientation_optimum'] ?? 0);

        // Checks if required fields are filled
        if (empty($installateur) || empty($commune) || $nb_panneaux <= 0) {
            throw new Exception("Les champs installateur, commune et nombre de panneaux sont obligatoires.");
        }

        // Checks validity of panels and ondulators amounts
        if ($nb_panneaux <= 0) {
            throw new Exception("Le nombre de panneaux doit être supérieur à 0.");
        }
        if ($nb_onduleurs <= 0) {
            throw new Exception("Le nombre d'onduleurs doit être supérieur à 0.");
        }

        // GPS data parsing
        $lat = null;
        $lon = null;
        if (!empty($coordonnees_gps)) {
            $coords = explode(',', $coordonnees_gps);
            if (count($coords) == 2) {
                $lat = floatval(trim($coords[0]));
                $lon = floatval(trim($coords[1]));
                
                // Checks if GPS data is valid
                if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                    throw new Exception("Coordonnées GPS invalides. Latitude: -90 à 90, Longitude: -180 à 180.");
                }
            } else {
                throw new Exception("Format des coordonnées GPS invalide. Utilisez: latitude,longitude");
            }
        }

        // getting installation date
        $an_installation = null;
        $mois_installation = null;
        if (!empty($date_installation)) {
            $date_parts = explode('-', $date_installation);
            if (count($date_parts) >= 2) {
                $an_installation = intval($date_parts[0]);
                $mois_installation = intval($date_parts[1]);
                
                // Checking if date is correct
                $install_date = new DateTime($date_installation);
                $today = new DateTime();
                if ($install_date > $today) {
                    throw new Exception("La date d'installation ne peut pas être dans le futur.");
                }
            }
        }

        // Checking if surface, power and production values are valid
        if ($surface < 0) {
            throw new Exception("La surface ne peut pas être négative.");
        }
        if ($puissance_crete < 0) {
            throw new Exception("La puissance crête ne peut pas être négative.");
        }
        if ($production_pvgis < 0) {
            throw new Exception("La production PVGIS ne peut pas être négative.");
        }

        $pdo->beginTransaction();

        // Installer data
        $id_installateur = null;
        if (!empty($installateur)) {
            $stmt = $pdo->prepare("SELECT id FROM Installateur WHERE install_nom = :nom");
            $stmt->execute([':nom' => $installateur]);
            $inst = $stmt->fetch();
            
            if ($inst) {
                $id_installateur = $inst['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO Installateur (install_nom) VALUES (:nom)");
                $stmt->execute([':nom' => $installateur]);
                $id_installateur = $pdo->lastInsertId();
            }
        }

        $isUpdate = isset($_POST['update']) || ($isEdit && $id);

        if ($isUpdate) {
            // Editing an existing installation
            $sql = "UPDATE Installation SET 
                id_Installateur = :id_installateur, nb_pann = :nb_panneaux, 
                id_Commune = :id_commune, id_Panneau = :id_panneau,
                id_Onduleur = :id_onduleur, nb_ond = :nb_onduleurs, 
                surface = :surface, an_installation = :an_installation,
                mois_installation = :mois_installation, lat = :lat, lon = :lon,
                puissance_crete = :puissance_crete, prod_pvgis = :prod_pvgis, 
                pente = :pente, pente_opti = :pente_opti, 
                ori = :ori, ori_opti = :ori_opti, code_postal = :code_postal
                WHERE id = :id";

            $params = [
                ':id_installateur' => $id_installateur,
                ':nb_panneaux' => $nb_panneaux,
                ':id_commune' => $id_commune,
                ':id_panneau' => $id_panneau,
                ':id_onduleur' => $id_onduleur,
                ':nb_onduleurs' => $nb_onduleurs,
                ':surface' => $surface,
                ':an_installation' => $an_installation,
                ':mois_installation' => $mois_installation,
                ':lat' => $lat,
                ':lon' => $lon,
                ':puissance_crete' => $puissance_crete,
                ':prod_pvgis' => $production_pvgis,
                ':pente' => $pente,
                ':pente_opti' => $pente_optimum,
                ':ori' => $orientation,
                ':ori_opti' => $orientation_optimum,
                ':code_postal' => $code_postal,
                ':id' => $id
            ];

            $response['message'] = "Installation modifiée avec succès !";
        } else {
            // creating a new installation
            $sql = "INSERT INTO Installation (
                id_Installateur, nb_pann, id_Commune, id_Panneau, id_Onduleur,
                nb_ond, surface, an_installation, mois_installation, lat, lon,
                puissance_crete, prod_pvgis, pente, pente_opti, ori, ori_opti, code_postal
            ) VALUES (
                :id_installateur, :nb_panneaux, :id_commune, :id_panneau, :id_onduleur,
                :nb_onduleurs, :surface, :an_installation, :mois_installation, :lat, :lon,
                :puissance_crete, :prod_pvgis, :pente, :pente_opti, :ori, :ori_opti, :code_postal
            )";

            $params = [
                ':id_installateur' => $id_installateur,
                ':nb_panneaux' => $nb_panneaux,
                ':id_commune' => $id_commune,
                ':id_panneau' => $id_panneau,
                ':id_onduleur' => $id_onduleur,
                ':nb_onduleurs' => $nb_onduleurs,
                ':surface' => $surface,
                ':an_installation' => $an_installation,
                ':mois_installation' => $mois_installation,
                ':lat' => $lat,
                ':lon' => $lon,
                ':puissance_crete' => $puissance_crete,
                ':prod_pvgis' => $production_pvgis,
                ':pente' => $pente,
                ':pente_opti' => $pente_optimum,
                ':ori' => $orientation,
                ':ori_opti' => $orientation_optimum,
                ':code_postal' => $code_postal
            ];

            $response['message'] = "Installation créée avec succès !";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Sending request
        $pdo->commit();

        $response['success'] = true;
        
        if (!$isUpdate) {
            $response['data'] = ['installation_id' => $pdo->lastInsertId()];
        }
        
    } catch(Exception $e) {
        // Cancelling request if an error occurs to prevent data loss
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = $e->getMessage();
        $response['success'] = false;
    }
    
    // Return JSON response for AJAX requests
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// If it's an AJAX request asking for installation data (for editing)
if ($isAjax && $isEdit && $installation) {
    $response['success'] = true;
    $response['data'] = $installation;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// For non-AJAX requests, redirect to the form page
if (!$isAjax) {
    if ($isEdit && !$installation) {
        header('Location: create.html?error=' . urlencode('Installation not found'));
        exit;
    }
    
    header('Location: create.html');
    exit;
}

// Default JSON response for AJAX requests
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>