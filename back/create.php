<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/db.php';

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Variables d'initialisation
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

$isEdit = false;
$installation = null;

// Vérification si c'est une modification (ID passé en paramètre)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $isEdit = true;
    
    // Récupération des données existantes avec jointures
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

// Traitement du formulaire (création ou modification)
if ($_POST && (isset($_POST['create']) || isset($_POST['update']))) {
    try {
        // Récupération des données du formulaire
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

        // Validation des données obligatoires
        if (empty($installateur) || empty($commune) || $nb_panneaux <= 0) {
            throw new Exception("Les champs installateur, commune et nombre de panneaux sont obligatoires.");
        }

        // Validation du nombre de panneaux et onduleurs
        if ($nb_panneaux <= 0) {
            throw new Exception("Le nombre de panneaux doit être supérieur à 0.");
        }
        
        if ($nb_onduleurs <= 0) {
            throw new Exception("Le nombre d'onduleurs doit être supérieur à 0.");
        }

        // Parse des coordonnées GPS
        $lat = null;
        $lon = null;
        if (!empty($coordonnees_gps)) {
            $coords = explode(',', $coordonnees_gps);
            if (count($coords) == 2) {
                $lat = floatval(trim($coords[0]));
                $lon = floatval(trim($coords[1]));
                
                // Validation des coordonnées GPS
                if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                    throw new Exception("Coordonnées GPS invalides. Latitude: -90 à 90, Longitude: -180 à 180.");
                }
            } else {
                throw new Exception("Format des coordonnées GPS invalide. Utilisez: latitude,longitude");
            }
        }

        // Parse de la date d'installation
        $an_installation = null;
        $mois_installation = null;
        if (!empty($date_installation)) {
            $date_parts = explode('-', $date_installation);
            if (count($date_parts) == 3) {
                $an_installation = intval($date_parts[0]);
                $mois_installation = intval($date_parts[1]);
                
                // Validation de la date (ne doit pas être dans le futur)
                $install_date = new DateTime($date_installation);
                $today = new DateTime();
                if ($install_date > $today) {
                    throw new Exception("La date d'installation ne peut pas être dans le futur.");
                }
            }
        }

        // Validation des valeurs numériques
        if ($surface < 0) {
            throw new Exception("La surface ne peut pas être négative.");
        }
        
        if ($puissance_crete < 0) {
            throw new Exception("La puissance crête ne peut pas être négative.");
        }
        
        if ($production_pvgis < 0) {
            throw new Exception("La production PVGIS ne peut pas être négative.");
        }

        // Début de la transaction
        $pdo->beginTransaction();

        // Gestion de l'installateur
        $id_installateur = null;
        if (!empty($installateur)) {
            // Vérifier si l'installateur existe
            $stmt = $pdo->prepare("SELECT id FROM Installateur WHERE install_nom = :nom");
            $stmt->execute([':nom' => $installateur]);
            $inst = $stmt->fetch();
            
            if ($inst) {
                $id_installateur = $inst['id'];
            } else {
                // Créer un nouvel installateur
                $stmt = $pdo->prepare("INSERT INTO Installateur (install_nom) VALUES (:nom)");
                $stmt->execute([':nom' => $installateur]);
                $id_installateur = $pdo->lastInsertId();
            }
        }

        // Gestion du pays
        $id_pays = null;
        if (!empty($pays)) {
            $stmt = $pdo->prepare("SELECT id FROM Pays WHERE pays_nom = :nom");
            $stmt->execute([':nom' => $pays]);
            $p = $stmt->fetch();
            
            if ($p) {
                $id_pays = $p['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO Pays (pays_nom) VALUES (:nom)");
                $stmt->execute([':nom' => $pays]);
                $id_pays = $pdo->lastInsertId();
            }
        }

        // Gestion de la région
        $id_region = null;
        if (!empty($region) && $id_pays) {
            $stmt = $pdo->prepare("SELECT id FROM Region WHERE dep_reg = :nom AND id_Pays = :id_pays");
            $stmt->execute([':nom' => $region, ':id_pays' => $id_pays]);
            $r = $stmt->fetch();
            
            if ($r) {
                $id_region = $r['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO Region (dep_reg, id_Pays) VALUES (:nom, :id_pays)");
                $stmt->execute([':nom' => $region, ':id_pays' => $id_pays]);
                $id_region = $pdo->lastInsertId();
            }
        }

        // Gestion du département
        $id_departement = null;
        if (!empty($departement) && $id_region) {
            $stmt = $pdo->prepare("SELECT id FROM Departement WHERE dep_nom = :nom AND id_Region = :id_region");
            $stmt->execute([':nom' => $departement, ':id_region' => $id_region]);
            $d = $stmt->fetch();
            
            if ($d) {
                $id_departement = $d['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO Departement (dep_nom, id_Region) VALUES (:nom, :id_region)");
                $stmt->execute([':nom' => $departement, ':id_region' => $id_region]);
                $id_departement = $pdo->lastInsertId();
            }
        }

        // Gestion de la commune
        $id_commune = null;
        if (!empty($commune) && $id_departement) {
            $stmt = $pdo->prepare("SELECT id FROM Commune WHERE com_nom = :nom AND id_Departement = :id_dep");
            $stmt->execute([':nom' => $commune, ':id_dep' => $id_departement]);
            $c = $stmt->fetch();
            
            if ($c) {
                $id_commune = $c['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO Commune (com_nom, id_Departement, code_insee) VALUES (:nom, :id_dep, :code)");
                $stmt->execute([':nom' => $commune, ':id_dep' => $id_departement, ':code' => $code_postal]);
                $id_commune = $pdo->lastInsertId();
            }
        }

        // Gestion des marques et modèles de panneaux
        $id_panneau = null;
        if (!empty($marque_panneaux) && !empty($modele_panneaux)) {
            // Marque panneau
            $stmt = $pdo->prepare("SELECT id FROM Marque_Panneau WHERE nom = :nom");
            $stmt->execute([':nom' => $marque_panneaux]);
            $mp = $stmt->fetch();
            
            if (!$mp) {
                $stmt = $pdo->prepare("INSERT INTO Marque_Panneau (nom) VALUES (:nom)");
                $stmt->execute([':nom' => $marque_panneaux]);
                $id_marque_panneau = $pdo->lastInsertId();
            } else {
                $id_marque_panneau = $mp['id'];
            }

            // Modèle panneau
            $stmt = $pdo->prepare("SELECT id FROM Modele_Panneau WHERE nom = :nom");
            $stmt->execute([':nom' => $modele_panneaux]);
            $mdp = $stmt->fetch();
            
            if (!$mdp) {
                $stmt = $pdo->prepare("INSERT INTO Modele_Panneau (nom) VALUES (:nom)");
                $stmt->execute([':nom' => $modele_panneaux]);
                $id_modele_panneau = $pdo->lastInsertId();
            } else {
                $id_modele_panneau = $mdp['id'];
            }

            // Panneau
            $stmt = $pdo->prepare("SELECT id FROM Panneau WHERE id_Marque_Panneau = :marque AND id_Modele_Panneau = :modele");
            $stmt->execute([':marque' => $id_marque_panneau, ':modele' => $id_modele_panneau]);
            $pn = $stmt->fetch();
            
            if (!$pn) {
                $stmt = $pdo->prepare("INSERT INTO Panneau (id_Marque_Panneau, id_Modele_Panneau) VALUES (:marque, :modele)");
                $stmt->execute([':marque' => $id_marque_panneau, ':modele' => $id_modele_panneau]);
                $id_panneau = $pdo->lastInsertId();
            } else {
                $id_panneau = $pn['id'];
            }
        }

        // Gestion des marques et modèles d'onduleurs
        $id_onduleur = null;
        if (!empty($marque_onduleurs) && !empty($modele_onduleurs)) {
            // Marque onduleur
            $stmt = $pdo->prepare("SELECT id FROM Marque_Onduleur WHERE nom = :nom");
            $stmt->execute([':nom' => $marque_onduleurs]);
            $mo = $stmt->fetch();
            
            if (!$mo) {
                $stmt = $pdo->prepare("INSERT INTO Marque_Onduleur (nom) VALUES (:nom)");
                $stmt->execute([':nom' => $marque_onduleurs]);
                $id_marque_onduleur = $pdo->lastInsertId();
            } else {
                $id_marque_onduleur = $mo['id'];
            }

            // Modèle onduleur
            $stmt = $pdo->prepare("SELECT id FROM Modele_Onduleur WHERE nom = :nom");
            $stmt->execute([':nom' => $modele_onduleurs]);
            $mdo = $stmt->fetch();
            
            if (!$mdo) {
                $stmt = $pdo->prepare("INSERT INTO Modele_Onduleur (nom) VALUES (:nom)");
                $stmt->execute([':nom' => $modele_onduleurs]);
                $id_modele_onduleur = $pdo->lastInsertId();
            } else {
                $id_modele_onduleur = $mdo['id'];
            }

            // Onduleur
            $stmt = $pdo->prepare("SELECT id FROM Onduleur WHERE id_Marque_Onduleur = :marque AND id_Modele_Onduleur = :modele");
            $stmt->execute([':marque' => $id_marque_onduleur, ':modele' => $id_modele_onduleur]);
            $ond = $stmt->fetch();
            
            if (!$ond) {
                $stmt = $pdo->prepare("INSERT INTO Onduleur (id_Marque_Onduleur, id_Modele_Onduleur) VALUES (:marque, :modele)");
                $stmt->execute([':marque' => $id_marque_onduleur, ':modele' => $id_modele_onduleur]);
                $id_onduleur = $pdo->lastInsertId();
            } else {
                $id_onduleur = $ond['id'];
            }
        }

        if ($isEdit && isset($_POST['update'])) {
            // Modification d'une installation existante
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
            // Création d'une nouvelle installation
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

        // Valider la transaction
        $pdo->commit();

        $response['success'] = true;
        
        if (!$isEdit) {
            $response['data'] = ['installation_id' => $pdo->lastInsertId()];
        }
        
    } catch(Exception $e) {
        // Annuler la transaction en cas d'erreur
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $response['message'] = $e->getMessage();
        $response['success'] = false;
    }
    
    // Return JSON response for AJAX requests
    if ($isAjax) {
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // For non-AJAX requests, redirect or show HTML response
    if ($response['success']) {
        header('Location: search.html?success=' . urlencode($response['message']));
        exit;
    }
}

// If it's an AJAX request asking for installation data (for editing)
if ($isAjax && $isEdit && $installation) {
    $response['success'] = true;
    $response['data'] = $installation;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// For non-AJAX requests, you can include your HTML form here or redirect
if (!$isAjax) {
    // Include HTML form or redirect to the form page
    if ($isEdit && !$installation) {
        header('Location: create.html?error=' . urlencode('Installation not found'));
        exit;
    }
    
    // You can include your HTML form here or redirect to create.html
    header('Location: create.html');
    exit;
}

// Default JSON response for AJAX requests
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>