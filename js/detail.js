// Global variables
let correctKeyHash = '';
let apiData = {};

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
        console.log('User already authenticated - skipping modal');
        return true;
    }
    return false;
}

// MD5 hash function (requires CryptoJS library)
function hashMD5(input) {
    return CryptoJS.MD5(input).toString();
}

// Show key modal
function showKeyModal() {
    console.log("Authentication modal triggered");
    const isAuthenticated = checkAuthentication();
    if (isAuthenticated) {
        // If already authenticated, proceed with the action
        return true;
    }
    document.getElementById('keyModal').style.display = 'flex';
    document.getElementById('passwordInput').focus();
    document.getElementById('errorMessage').style.display = 'none';
    return false;
}

// Hide key modal
function hideKeyModal() {
    document.getElementById('keyModal').style.display = 'none';
    document.getElementById('passwordInput').value = '';
    document.getElementById('errorMessage').style.display = 'none';
}

// Validate key
async function validateKey() {
    const inputKey = document.getElementById('passwordInput').value;
    const remember = document.getElementById('rememberCheckbox').checked;
    const inputHash = hashMD5(inputKey);

    if (inputHash === correctKeyHash) {
        if (remember) {
            setCookie('secretKeyAuth', 'authenticated', 1);
            console.log('Authentication cookie set for 1 hour');
        }
        hideKeyModal();

        // Execute the pending action
        const pendingAction = document.getElementById('keyModal').getAttribute('data-pending-action');
        if (pendingAction === 'modify') {
            executeModifyAction();
        } else if (pendingAction === 'delete') {
            await executeDeleteAction();
        }
    } else {
        document.getElementById('errorMessage').style.display = 'block';
        document.getElementById('passwordInput').value = '';
        document.getElementById('passwordInput').focus();
    }
}

// Execute modify action after authentication
function executeModifyAction() {
    const params = new URLSearchParams(window.location.search);
    const installId = params.get('detail');
    if (!installId) {
        alert("Impossible de récupérer l'identifiant de l'installation.");
        return;
    }
    window.location.href = `create.html?id=${installId}`;
}

// Execute delete action after authentication
async function executeDeleteAction() {
    if (!confirm("Voulez-vous vraiment supprimer cette installation ?")) return;

    try {
        const params = new URLSearchParams(window.location.search);
        const installId = params.get('detail');

        if (!installId) {
            throw new Error("ID d'installation manquant");
        }

        // Fixed: Use the correct PHP backend URL, not search.html
        const response = await $.ajax({
            url: `./back/detail.php?id=${installId}`,
            method: 'DELETE',
            dataType: 'json'
        });

        if (response.success) {
            alert("Installation supprimée !");
            window.location.href = "search.html";
        } else {
            alert(response.message || "Erreur lors de la suppression.");
        }
    } catch (error) {
        console.error('Delete error:', error);

        // More detailed error handling
        if (error.status === 405) {
            alert("Erreur: Méthode non autorisée. Vérifiez la configuration du serveur.");
        } else if (error.status === 404) {
            alert("Erreur: Fichier PHP non trouvé.");
        } else {
            alert("Erreur lors de la suppression: " + (error.responseText || error.message || "Erreur inconnue"));
        }
    }
}

// Load installation data using AJAX
function loadInstallationData(installId) {
    return $.ajax({
        url: `./back/detail.php?id=${installId}`,
        method: 'GET',
        dataType: 'json'
    });
}

// Populate the DOM with installation data
function populateInstallationData(data) {
    // Store data globally for potential future use
    apiData = data;

    // Get the three columns (installation / localisation / paramètres)
    const inst = document.getElementById('installation').children;
    const loc = document.getElementById('localisation').children;
    const param = document.getElementById('parametres').children;

    // Installation
    inst[0].textContent = data.nom_installateur || 'N/A';
    inst[1].textContent = data.nb_pann || 'N/A';
    inst[2].textContent = data.modele_pn || 'N/A';
    inst[3].textContent = data.marque_pn || 'N/A';
    inst[4].textContent = data.nb_ond || 'N/A';
    inst[5].textContent = data.modele_ond || 'N/A';
    inst[6].textContent = data.marque_ond || 'N/A';
    inst[7].textContent = data.surface ? `${data.surface} m²` : 'N/A';
    inst[8].textContent = data.date_install || 'N/A';

    // Position
    loc[0].textContent = data.com_nom || 'N/A';
    loc[1].textContent = data.dep_nom || 'N/A';
    loc[2].textContent = data.code_postal || 'N/A';
    loc[3].textContent = data.reg_nom || 'N/A';
    loc[4].textContent = data.pays_nom || 'N/A';
    loc[5].textContent = (data.lat && data.lon) ? `${data.lat.toFixed(5)}, ${data.lon.toFixed(5)}` : 'N/A';

    // Installation parameters
    param[0].textContent = data.puissance_crete ? `${data.puissance_crete} kWc` : 'N/A';
    param[1].textContent = data.prod_pvgis ? `${data.prod_pvgis} kWh/an` : 'N/A';
    param[2].textContent = data.pente ? `${data.pente}°` : 'N/A';
    param[3].textContent = data.pente_opti ? `${data.pente_opti}°` : 'N/A';
    param[4].textContent = data.ori ? `${data.ori}°` : 'N/A';
    param[5].textContent = data.ori_opti ? `${data.ori_opti}°` : 'N/A';
}

// Handle keyboard events
document.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' && document.getElementById('keyModal').style.display === 'flex') {
        validateKey();
    }
    if (event.key === 'Escape') {
        hideKeyModal();
    }
});

// Handle click outside modal
document.addEventListener('click', function (event) {
    const modal = document.getElementById('keyModal');
    const contentModal = document.getElementById('contentModal');
    const openBtn1 = document.getElementById('delInst');
    const openBtn2 = document.getElementById('changeInst');

    if (modal && modal.style.display === 'flex' &&
        contentModal && !contentModal.contains(event.target) &&
        event.target !== openBtn1 && event.target !== openBtn2) {
        hideKeyModal();
    }
});

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Load key hash
        loadKeyHash();

        // Get installation ID from URL
        const params = new URLSearchParams(window.location.search);
        const installId = params.get('detail');

        if (!installId) {
            throw new Error("Paramètre 'detail' manquant dans l'URL");
        }

        // Load installation data using AJAX
        const data = await loadInstallationData(installId);

        if (data.error) {
            throw new Error(data.message);
        }

        // Populate the DOM with the data
        populateInstallationData(data);

        // Set up button event handlers with improved performance
        const changeBtn = document.getElementById('changeInst');
        if (changeBtn) {
            changeBtn.addEventListener('click', function (event) {
                event.preventDefault();

                // Immediate response for better UX
                const isAuth = checkAuthentication();

                if (isAuth) {
                    executeModifyAction();
                } else {
                    document.getElementById('keyModal').setAttribute('data-pending-action', 'modify');
                    showKeyModal();
                }
            });
        }

        const deleteBtn = document.getElementById('delInst');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function (event) {
                event.preventDefault();

                // Immediate response for better UX
                const isAuth = checkAuthentication();

                if (isAuth) {
                    executeDeleteAction();
                } else {
                    document.getElementById('keyModal').setAttribute('data-pending-action', 'delete');
                    showKeyModal();
                }
            });
        }

    } catch (error) {
        console.error("detail.js error:", error);

        // Display error to user
        const container = document.querySelector('.detail_content');
        if (container) {
            container.innerHTML = `
        <div class="alert alert-danger text-center">
          <h4>Erreur de chargement</h4>
          <p>${error.message}</p>
          <a href="search.html" class="btn btn-primary">Retour à la recherche</a>
        </div>`;
        }
    }
});