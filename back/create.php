<?php
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Variables d'initialisation
$message = '';
$messageType = '';
$isEdit = false;
$installation = null;

// Variables d'initialisation
$message = '';
$messageType = '';
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
            $message = "Installation introuvable avec l'ID : " . $id;
            $messageType = "danger";
            $isEdit = false;
        }
    } catch(Exception $e) {
        $message = "Erreur lors de la récupération des données : " . $e->getMessage();
        $messageType = "danger";
        $isEdit = false;
    }
}

// Récupération des listes pour les dropdowns
$installateurs = [];
$communes = [];
$marques_panneaux = [];
$modeles_panneaux = [];
$marques_onduleurs = [];
$modeles_onduleurs = [];

try {
    // Récupération des installateurs
    $stmt = $pdo->query("SELECT id, install_nom FROM Installateur ORDER BY install_nom");
    $installateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupération des communes avec leurs informations complètes
    $stmt = $pdo->query("
        SELECT c.id, c.com_nom, d.dep_nom, r.dep_reg, p.pays_nom, c.code_postal
        FROM Commune c
        JOIN Departement d ON c.id_Departement = d.id
        JOIN Region r ON d.id_Region = r.id
        JOIN Pays p ON r.id_Pays = p.id
        ORDER BY c.com_nom
    ");
    $communes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupération des marques et modèles de panneaux
    $stmt = $pdo->query("SELECT id, nom FROM Marque_Panneau ORDER BY nom");
    $marques_panneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id, nom, id_Marque_Panneau FROM Modele_Panneau ORDER BY nom");
    $modeles_panneaux = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Récupération des marques et modèles d'onduleurs
    $stmt = $pdo->query("SELECT id, nom FROM Marque_Onduleur ORDER BY nom");
    $marques_onduleurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SELECT id, nom, id_Marque_Onduleur FROM Modele_Onduleur ORDER BY nom");
    $modeles_onduleurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $message = "Erreur lors du chargement des données : " . $e->getMessage();
    $messageType = "warning";
}

// Traitement du formulaire (création ou modification)
if ($_POST && (isset($_POST['create']) || isset($_POST['update']))) {
    try {
        // Récupération des données du formulaire
        $id_installateur = $_POST['id_installateur'] ?? '';
        $nb_panneaux = $_POST['nb_panneaux'] ?? '';
        $id_commune = $_POST['id_commune'] ?? '';
        $id_marque_panneau = $_POST['id_marque_panneau'] ?? '';
        $id_modele_panneau = $_POST['id_modele_panneau'] ?? '';
        $id_marque_onduleur = $_POST['id_marque_onduleur'] ?? '';
        $id_modele_onduleur = $_POST['id_modele_onduleur'] ?? '';
        $nb_onduleurs = $_POST['nb_onduleurs'] ?? '';
        $surface = $_POST['surface'] ?? '';
        $an_installation = $_POST['an_installation'] ?? '';
        $mois_installation = $_POST['mois_installation'] ?? '';
        $lat = $_POST['lat'] ?? '';
        $lon = $_POST['lon'] ?? '';
        $puissance_crete = $_POST['puissance_crete'] ?? '';
        $prod_pvgis = $_POST['prod_pvgis'] ?? '';
        $pente = $_POST['pente'] ?? '';
        $pente_opti = $_POST['pente_opti'] ?? '';
        $ori = $_POST['ori'] ?? '';
        $ori_opti = $_POST['ori_opti'] ?? '';
        $code_postal = $_POST['code_postal'] ?? '';

        // Validation des données obligatoires
        if (empty($id_installateur) || empty($id_commune) || empty($nb_panneaux)) {
            throw new Exception("Les champs installateur, commune et nombre de panneaux sont obligatoires.");
        }

        // Gestion des panneaux et onduleurs
        $id_panneau = null;
        $id_onduleur = null;

        // Création ou récupération du panneau
        if (!empty($id_marque_panneau) && !empty($id_modele_panneau)) {
            $stmt = $pdo->prepare("SELECT id FROM Panneau WHERE id_Marque_Panneau = :marque AND id_Modele_Panneau = :modele");
            $stmt->execute([':marque' => $id_marque_panneau, ':modele' => $id_modele_panneau]);
            $panneau = $stmt->fetch();
            
            if ($panneau) {
                $id_panneau = $panneau['id'];
            } else {
                // Créer un nouveau panneau
                $stmt = $pdo->prepare("INSERT INTO Panneau (id_Marque_Panneau, id_Modele_Panneau) VALUES (:marque, :modele)");
                $stmt->execute([':marque' => $id_marque_panneau, ':modele' => $id_modele_panneau]);
                $id_panneau = $pdo->lastInsertId();
            }
        }

        // Création ou récupération de l'onduleur
        if (!empty($id_marque_onduleur) && !empty($id_modele_onduleur)) {
            $stmt = $pdo->prepare("SELECT id FROM Onduleur WHERE id_Marque_Onduleur = :marque AND id_Modele_Onduleur = :modele");
            $stmt->execute([':marque' => $id_marque_onduleur, ':modele' => $id_modele_onduleur]);
            $onduleur = $stmt->fetch();
            
            if ($onduleur) {
                $id_onduleur = $onduleur['id'];
            } else {
                // Créer un nouvel onduleur
                $stmt = $pdo->prepare("INSERT INTO Onduleur (id_Marque_Onduleur, id_Modele_Onduleur) VALUES (:marque, :modele)");
                $stmt->execute([':marque' => $id_marque_onduleur, ':modele' => $id_modele_onduleur]);
                $id_onduleur = $pdo->lastInsertId();
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
                ':prod_pvgis' => $prod_pvgis,
                ':pente' => $pente,
                ':pente_opti' => $pente_opti,
                ':ori' => $ori,
                ':ori_opti' => $ori_opti,
                ':code_postal' => $code_postal,
                ':id' => $id
            ];

            $message = "Installation modifiée avec succès !";
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
                ':prod_pvgis' => $prod_pvgis,
                ':pente' => $pente,
                ':pente_opti' => $pente_opti,
                ':ori' => $ori,
                ':ori_opti' => $ori_opti,
                ':code_postal' => $code_postal
            ];

            $message = "Installation créée avec succès !";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $messageType = "success";
        
        // Redirection vers la page de recherche après 2 secondes
        header("refresh:2;url=search.php");
        
    } catch(Exception $e) {
        $message = "Erreur : " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $isEdit ? 'Modifier' : 'Créer'; ?> Installation - Panneaux Photovoltaïque</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/create.css">
</head>

<body>
  <header class="header-container">
    <img class="logo" src="assets/logo.png" alt="Logo">

    <ul class="nav nav-tabs">
      <li class="nav-item">
        <a class="nav-link" href="home.php" data-tab="accueil">Accueil</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="search.php" data-tab="recherche">Recherche</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="map.php" data-tab="carte">Carte</a>
      </li>
    </ul>
  </header>

  <?php if ($message): ?>
  <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show m-3" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <form method="POST" action="">
    <section class="detail_content">
      <div class="mx-auto p-2" style="width:98%">
        <div class="rounded-2 border border-dark d-grid gap-3 p-2">
          <div class="rounded-2 d-flex flex-column resultElem">
            <div class="rounded-top-2 d-flex flex-column text-light elementTop">
              <div class="d-grid flex-column topRow">
                <p class="my-auto p-2 text-center"><?php echo $isEdit ? 'Modification Installation' : 'Nouvelle Installation'; ?></p>
              </div>
            </div>
            <div class="rounded-bottom-2 d-flex justify-content-between elementBottom">
              <div class="d-grid flex-column leftCol" style="width: 50%;">
                <p class="my-auto p-2 text-center">Installé par :</p>
                <p class="my-auto p-2 text-center">Nombre de panneaux :</p>
                <p class="my-auto p-2 text-center">Modèle des panneaux :</p>
                <p class="my-auto p-2 text-center">Marque des panneaux :</p>
                <p class="my-auto p-2 text-center">Nombre d'onduleurs :</p>
                <p class="my-auto p-2 text-center">Modèle des onduleurs :</p>
                <p class="my-auto p-2 text-center">Marque des onduleurs :</p>
                <p class="my-auto p-2 text-center">Surface :</p>
                <p class="my-auto p-2 text-center">Date d'installation :</p>
                <p class="my-auto p-2 text-center">Commune :</p>
                <p class="my-auto p-2 text-center">Département :</p>
                <p class="my-auto p-2 text-center">Code Postal :</p>
                <p class="my-auto p-2 text-center">Région :</p>
                <p class="my-auto p-2 text-center">Pays :</p>
                <p class="my-auto p-2 text-center">Coordonnées GPS :</p>
                <p class="my-auto p-2 text-center">Puissance Crête :</p>
                <p class="my-auto p-2 text-center">Production (PVGIS) :</p>
                <p class="my-auto p-2 text-center">Pente :</p>
                <p class="my-auto p-2 text-center">Pente Optimum :</p>
                <p class="my-auto p-2 text-center">Orientation (0°=Sud) :</p>
                <p class="my-auto p-2 text-center">Orientation Optimum :</p>
              </div>
              <div class="d-grid flex-column rightCol" style="width: 50%;">
                <input type="text" name="installateur" class="form-control my-1 p-2 text-center" placeholder="Nom de l'installateur" value="<?php echo $installation ? htmlspecialchars($installation['installateur']) : ''; ?>" required>
                <input type="number" name="nb_panneaux" class="form-control my-1 p-2 text-center" placeholder="Nombre" value="<?php echo $installation ? htmlspecialchars($installation['nb_panneaux']) : ''; ?>">
                <input type="text" name="modele_panneaux" class="form-control my-1 p-2 text-center" placeholder="Modèle" value="<?php echo $installation ? htmlspecialchars($installation['modele_panneaux']) : ''; ?>">
                <input type="text" name="marque_panneaux" class="form-control my-1 p-2 text-center" placeholder="Marque" value="<?php echo $installation ? htmlspecialchars($installation['marque_panneaux']) : ''; ?>">
                <input type="number" name="nb_onduleurs" class="form-control my-1 p-2 text-center" placeholder="Nombre" value="<?php echo $installation ? htmlspecialchars($installation['nb_onduleurs']) : ''; ?>">
                <input type="text" name="modele_onduleurs" class="form-control my-1 p-2 text-center" placeholder="Modèle" value="<?php echo $installation ? htmlspecialchars($installation['modele_onduleurs']) : ''; ?>">
                <input type="text" name="marque_onduleurs" class="form-control my-1 p-2 text-center" placeholder="Marque" value="<?php echo $installation ? htmlspecialchars($installation['marque_onduleurs']) : ''; ?>">
                <input type="number" step="0.01" name="surface" class="form-control my-1 p-2 text-center" placeholder="Surface (m²)" value="<?php echo $installation ? htmlspecialchars($installation['surface']) : ''; ?>">
                <input type="date" name="date_installation" class="form-control my-1 p-2 text-center" value="<?php echo $installation ? htmlspecialchars($installation['date_installation']) : ''; ?>">
                <input type="text" name="commune" class="form-control my-1 p-2 text-center" placeholder="Commune" value="<?php echo $installation ? htmlspecialchars($installation['commune']) : ''; ?>" required>
                <input type="text" name="departement" class="form-control my-1 p-2 text-center" placeholder="Département" value="<?php echo $installation ? htmlspecialchars($installation['departement']) : ''; ?>" required>
                <input type="text" name="code_postal" class="form-control my-1 p-2 text-center" placeholder="Code postal" value="<?php echo $installation ? htmlspecialchars($installation['code_postal']) : ''; ?>">
                <input type="text" name="region" class="form-control my-1 p-2 text-center" placeholder="Région" value="<?php echo $installation ? htmlspecialchars($installation['region']) : ''; ?>">
                <input type="text" name="pays" class="form-control my-1 p-2 text-center" placeholder="Pays" value="<?php echo $installation ? htmlspecialchars($installation['pays']) : 'France'; ?>">
                <input type="text" name="coordonnees_gps" class="form-control my-1 p-2 text-center" placeholder="Lat, Long" value="<?php echo $installation ? htmlspecialchars($installation['coordonnees_gps']) : ''; ?>">
                <input type="number" step="0.01" name="puissance_crete" class="form-control my-1 p-2 text-center" placeholder="kWc" value="<?php echo $installation ? htmlspecialchars($installation['puissance_crete']) : ''; ?>">
                <input type="number" step="0.01" name="production_pvgis" class="form-control my-1 p-2 text-center" placeholder="kWh/an" value="<?php echo $installation ? htmlspecialchars($installation['production_pvgis']) : ''; ?>">
                <input type="number" step="0.1" name="pente" class="form-control my-1 p-2 text-center" placeholder="Degrés" value="<?php echo $installation ? htmlspecialchars($installation['pente']) : ''; ?>">
                <input type="number" step="0.1" name="pente_optimum" class="form-control my-1 p-2 text-center" placeholder="Degrés" value="<?php echo $installation ? htmlspecialchars($installation['pente_optimum']) : ''; ?>">
                <input type="number" step="0.1" name="orientation" class="form-control my-1 p-2 text-center" placeholder="Degrés" value="<?php echo $installation ? htmlspecialchars($installation['orientation']) : ''; ?>">
                <input type="number" step="0.1" name="orientation_optimum" class="form-control my-1 p-2 text-center" placeholder="Degrés" value="<?php echo $installation ? htmlspecialchars($installation['orientation_optimum']) : ''; ?>">
              </div>
            </div>
          </div>
          
          <div class="rounded-2 d-flex flex-column resultElem">
            <div class="rounded-top-2 d-flex flex-column commentTop">
              <div class="d-grid flex-column topRow">
                <p class="my-auto p-2 text-center">Commentaires</p>
              </div>
            </div>
            <div class="rounded-bottom-2 d-flex flex-column commentBottom">
              <div class="d-grid flex-column leftCol m-4">
                <textarea name="commentaire" class="form-control" rows="4" placeholder="Ajoutez vos commentaires sur cette installation..."><?php echo $installation ? htmlspecialchars($installation['commentaire']) : ''; ?></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section>
      <div class="d-flex justify-content-between">
        <div class="m-5" style="width:98%">
          <div class="d-flex justify-content-center gap-3">
            <?php if ($isEdit): ?>
              <input type="hidden" name="id" value="<?php echo $installation['id']; ?>">
              <button type="submit" name="update" class="createBtn btn rounded-5 m-2 text-center">Modifier Installation</button>
            <?php else: ?>
              <button type="submit" name="create" class="createBtn btn rounded-5 m-2 text-center">Créer Installation</button>
            <?php endif; ?>
            <a href="search.php" class="btn btn-secondary rounded-5 m-2 text-center">Annuler</a>
          </div>
        </div>
      </div>
    </section>
  </form>

  <footer>
    <p>&copy; 2025 PanneauxPhotovoltaique.fr — Tous droits réservés.</p>
    <p>
      <a>Ibrahim SAOU</a> |
      <a>Erwan LANGLAIS</a> |
      <a>Achille WAGNER</a>
    </p>
  </footer>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
  <script src="js/create.js"></script>
</body>
</html>