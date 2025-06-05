<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recherche - Panneaux Photovoltaïque</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/search.css">
</head>

<body>
  <header class="header-container">
    <img class="logo" src="assets/logo.png">

    <ul class="nav nav-tabs">
      <li class="nav-item">
        <a class="nav-link" href="home.php" data-tab="accueil">Accueil</a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="#" data-tab="recherche">Recherche</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="map.php" data-tab="carte">Carte</a>
      </li>
    </ul>
  </header>

  <section class="research-param">
    <select class="select-box" id="ondBrand">
      <h3>Marque de l'onduleur</h3>
      <?php
      require_once 'db.php'; // Assure you have a PDO $pdo object in db.php

      try {
        $stmt = $pdo->query("SELECT DISTINCT marque FROM onduleurs ORDER BY marque ASC LIMIT 21");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $marque = htmlspecialchars($row['marque']);
          echo "<option value=\"$marque\">$marque</option>";
        }
      } catch (Exception $e) {
        echo '<option disabled>Erreur de chargement</option>';
      }
      ?>
      <!-- A modifier avec les 20 premiers résultats de la BDD -->
    </select>
    <select class="select-box" id="panelBrand">
      <h3>Marque du panneau</h3>
      <option id="pan1">1</option>
      <option id="pan2">2</option>
      <option id="pan3">3</option>
      <option id="pan4">4</option>
      <option id="pan5">5</option>
      <option id="pan6">6</option>
      <option id="pan7">7</option>
      <option id="pan8">8</option>
      <option id="pan9">9</option>
      <option id="pan10">10</option>
      <option id="pan11">11</option>
      <option id="pan12">12</option>
      <option id="pan13">13</option>
      <option id="pan14">14</option>
      <option id="pan15">15</option>
      <option id="pan16">16</option>
      <option id="pan17">17</option>
      <option id="pan18">18</option>
      <option id="pan19">19</option>
      <option id="pan20">20</option>
      <!-- A modifier avec les 20 premiers résultats de la BDD -->
    </select>
    <select class="select-box" id="department">
      <h3>Département</h3>
      <option id="dep1">1</option>
      <option id="dep2">2</option>
      <option id="dep3">3</option>
      <option id="dep4">4</option>
      <option id="dep5">5</option>
      <option id="dep6">6</option>
      <option id="dep7">7</option>
      <option id="dep8">8</option>
      <option id="dep9">9</option>
      <option id="dep10">10</option>
      <option id="dep11">11</option>
      <option id="dep12">12</option>
      <option id="dep13">13</option>
      <option id="dep14">14</option>
      <option id="dep15">15</option>
      <option id="dep16">16</option>
      <option id="dep17">17</option>
      <option id="dep18">18</option>
      <option id="dep19">19</option>
      <option id="dep20">20</option>
      <!-- A modifier avec 20 résultats aléatoires de la BDD -->
    </select>
  </section>

  <footer>
    <p>&copy; 2025 PanneauxPhotovoltaique.fr — Tous droits réservés.</p>
    <p>
      <a>Ibrahim SAOU</a> |
      <a>Erwan LANGLAIS</a> |
      <a>Achille WAGNER</a>
    </p>
  </footer>
  <script src="js/home.js"></script>
</body>
</html>