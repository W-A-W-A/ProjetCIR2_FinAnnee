
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


// Global variables
let correctKeyHash = '';

// Load correct key hash from keyCheck.txt
function loadKeyHash() {
  $.ajax({
    url: './keyCheck.txt',
    method: 'GET',
    success: function (response) {
      correctKeyHash = response.trim().split('\n')[0];
      console.log('Key hash loaded');
    },
    error: function () {
      console.error('Could not load key hash file');
    }
  });
}

// Cookie management functions
function setCookie(name, value, hours) {
  const date = new Date();
  date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
  const expires = "expires=" + date.toUTCString();
  document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

function getCookie(name) {
  const nameEQ = name + "=";
  const ca = document.cookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

function deleteAllCookies() {
  const cookies = document.cookie.split(";");

  for (const cookie of cookies) {
    const name = cookie.split("=")[0].trim();
    document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
  }
}


// Check if user is already authenticated
function checkAuthentication() {
  const authToken = getCookie('secretKeyAuth');
  if (authToken === 'authenticated') {
    // User is authenticated, hide modal and show content
    console.log('User already authenticated - skipping modal');
    return true;
  }
  return false;
}


// MD5 hash function
function hashMD5(input) {
  return CryptoJS.MD5(input).toString();
}

// Show key modal
function showKeyModal() {
  console.log("new installation has been clicked");
  isAuthenticated = checkAuthentication();
  if (isAuthenticated) {
    // If already authenticated, go directly to create page
    window.location.href = 'search.html';
    return;
  }
  document.getElementById('keyModal').style.display = 'flex';
  document.getElementById('passwordInput').focus();
  document.getElementById('errorMessage').style.display = 'none';
}

// Hide key modal
function hideKeyModal() {
  document.getElementById('keyModal').style.display = 'none';
  document.getElementById('passwordInput').value = '';
  document.getElementById('errorMessage').style.display = 'none';
}

// Validate key
function validateKey() {
  const inputKey = document.getElementById('passwordInput').value;
  const remember = document.getElementById('rememberCheckbox').checked;
  const inputHash = hashMD5(inputKey);

  if (inputHash === correctKeyHash) {

    if (remember) {
      // Set cookie for 1 hour
      setCookie('secretKeyAuth', 'authenticated', 1);
      console.log('Authentication cookie set for 1 hour');
    }
    // Correct key
    hideKeyModal();

    // Redirect to create page
    window.location.href = 'search.html';
  } else {
    // Incorrect key
    document.getElementById('errorMessage').style.display = 'block';
    document.getElementById('passwordInput').value = '';
    document.getElementById('passwordInput').focus();
  }
}


// Handle Enter key in password input
document.addEventListener('keydown', function (event) {
  if (event.key === 'Enter' && document.getElementById('keyModal').style.display === 'flex') {
    validateKey();
  }
  if (event.key === 'Escape') {
    hideKeyModal();
  }
});

document.addEventListener('click', function (event) {
  const openBtn1 = document.getElementById('changeInst');
  const openBtn2 = document.getElementById('delInst')
  if (document.getElementById('keyModal').style.display === 'flex' && !document.getElementById('contentModal').contains(event.target) && event.target !== openBtn1 && event.target !== openBtn2) {
    hideKeyModal();
  }
});

document.addEventListener('DOMContentLoaded', async () => {
  try {

    // Load key hash
    loadKeyHash();


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
