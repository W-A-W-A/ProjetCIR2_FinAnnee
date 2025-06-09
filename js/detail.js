
// $(document).ready(function () {
//     $.ajax({
//         url: './back/detail.php',
//         method: 'GET',
//         dataType: 'json',
//         success: function (data) {
//             for (const key in data) {
//                 apiData[key] = data[key];
//             }
//         },
//         error: function () {
//             console.error('Error during data retrival from database');
//         }
//     });
// });

document.addEventListener('DOMContentLoaded', async () => {
  try {
    // 1) Récupérer l'ID d'installation depuis la query string
    const params = new URLSearchParams(window.location.search);
    const installId = params.get('detail');
    if (!installId) {
      throw new Error("Paramètre 'id' manquant dans l'URL");
    }

    // 2) Appel au PHP qui renvoie le JSON
    const response = await fetch(`./back/detail.php?id=${installId}`);
    if (!response.ok) {
      // Tenter de parser un message d'erreur éventuel
      let errMessage = `Erreur HTTP ${response.status}`;
      try {
        const errJson = await response.json();
        errMessage = errJson.message || errMessage;
      } catch { /* ignore */ }
      throw new Error(errMessage);
    }

    const data = await response.json();
    if (data.error) {
      throw new Error(data.message);
    }

    // 3) Injection dans le DOM
    // On récupère les 3 colonnes de droite (installation / localisation / paramètres)
    const inst = document.getElementById('installation').children;
    const loc = document.getElementById('localisation').children;
    const param = document.getElementById('parametres').children;

    // --- Installation ---
    inst[0].textContent = data.nom_installateur;
    inst[1].textContent = data.nb_pann;
    inst[2].textContent = data.modele_pn;
    inst[3].textContent = data.marque_pn;
    inst[4].textContent = data.nb_ond;
    inst[5].textContent = data.modele_ond;
    inst[6].textContent = data.marque_ond;
    inst[7].textContent = `${data.surface} m²`;
    inst[8].textContent = data.date_install;

    // --- Localisation ---
    loc[0].textContent = data.com_nom;
    loc[1].textContent = data.dep_nom;
    loc[2].textContent = data.code_postal;
    loc[3].textContent = data.reg_nom;
    loc[4].textContent = data.pays_nom;
    loc[5].textContent = `${data.lat.toFixed(5)}, ${data.lon.toFixed(5)}`;

    // --- Paramètres de l'installation ---
    param[0].textContent = `${data.puissance_crete} kWc`;
    param[1].textContent = `${data.prod_pvgis} kWh/an`;
    param[2].textContent = `${data.pente}°`;
    param[3].textContent = `${data.pente_opti}°`;
    param[4].textContent = `${data.ori}°`;
    param[5].textContent = `${data.ori_opti}°`;

  } catch (err) {
    console.error("Detail.js :", err);
    // Afficher l'erreur à l'utilisateur
    const container = document.querySelector('.detail_content');
    container.innerHTML = `
      <div class="alert alert-danger text-center">
        ${err.message}
      </div>`;
  }
});
