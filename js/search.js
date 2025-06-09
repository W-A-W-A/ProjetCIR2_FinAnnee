// Global variables
let correctKeyHash = '';
let isAuthenticated = false;
let authTimer = null;

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

// MD5 hash function
function hashMD5(input) {
    return CryptoJS.MD5(input).toString();
}

// Show key modal
function showKeyModal() {
    if (isAuthenticated) {
        // If already authenticated, go directly to create page
        window.location.href = 'create.html';
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
    const inputHash = hashMD5(inputKey);

    if (inputHash === correctKeyHash) {
        // Correct key
        isAuthenticated = true;
        hideKeyModal();

        // Set timer for 1 hour
        authTimer = setTimeout(() => {
            isAuthenticated = false;
            alert('Session expirée. Veuillez vous reconnecter.');
        }, 3600000); // 1 hour in milliseconds

        // Redirect to create page
        window.location.href = 'create.html';
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

// Update field selection
function updateFieldSelection() {
    const selIdBrand = document.getElementById('ondBrand').value;
    const selIdPanel = document.getElementById('panelBrand').value;
    const selIdDep = document.getElementById('department').value;

    console.log("Current selection:", selIdBrand, selIdPanel, selIdDep);

    $.ajax({
        url: './back/panels.php',
        method: 'GET',
        dataType: 'json',
        data: {
            selIdBrand: selIdBrand,
            selIdPanel: selIdPanel,
            selIdDep: selIdDep
        },
        success: function (response) {
            const container = document.getElementById('resultsContainer');
            container.innerHTML = '';

            if (response.results && response.results.length > 0) {
                response.results.forEach(result => {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'result-item';
                    resultItem.innerHTML = `
                            <div class="result-header">
                                <div class="result-stats">
                                    <div class="stat-item">
                                        <div class="stat-label">Nombre de panneaux</div>
                                        <div class="stat-value">${result.nb_panneaux}</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Surface</div>
                                        <div class="stat-value">${result.surface} m²</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Puissance Crête</div>
                                        <div class="stat-value">${result.puissance_crete} kW</div>
                                    </div>
                                </div>
                                
                                <form action="detail.html" method="get" style="margin: 0;">
                                    <button type="submit" name="detail" value="${result["id"]}" class="detail-btn">
                                        Voir détail
                                    </button>
                                </form>
                            </div>
                            <div class="result-footer">
                                <div class="footer-item">
                                    <div class="footer-label">Date d'installation</div>
                                    <div class="footer-value">${result.date_installation}</div>
                                </div>
                                <div class="footer-item">
                                    <div class="footer-label">Localisation</div>
                                    <div class="footer-value">${result.localite}</div>
                                </div>
                            </div>
                        `;
                    container.appendChild(resultItem);
                });
            } else {
                container.innerHTML = '<p style="text-align: center; color: #64B6AC; padding: 2rem;">Aucun résultat trouvé</p>';
            }
        },
        error: function () {
            console.error('Erreur lors de la récupération des résultats');
        }
    });
}

// Initialize page
$(document).ready(function () {
    console.log("Script ready");

    // Load key hash
    loadKeyHash();

    const menuIds = ["ondBrand", "panelBrand", "department"];

    // Load available fields from database
    $.ajax({
        url: './back/search.php',
        method: 'GET',
        dataType: 'json',
        timeout: 15000,
        success: function (response) {
            console.log(response);
            menuIds.forEach(menuId => {
                const selectElement = document.getElementById(menuId);
                if (selectElement &&
                    Array.isArray(response[menuId]["names"]) &&
                    Array.isArray(response[menuId]["values"]) &&
                    response[menuId]["names"].length === response[menuId]["values"].length) {

                    // Keep the first empty option
                    const firstOption = selectElement.querySelector('option');
                    selectElement.innerHTML = '';
                    selectElement.appendChild(firstOption);

                    response[menuId]["names"].forEach((name, idx) => {
                        if (name.trim() !== '') { // Skip empty names
                            const option = document.createElement('option');
                            option.value = response[menuId]["values"][idx];
                            option.textContent = name;
                            selectElement.appendChild(option);
                        }
                    });
                }
            });

            // Initial load of results
            updateFieldSelection();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('Error: Couldn\'t load data from MySQL database');
            console.error('Status:', textStatus);
            console.error('Error Thrown:', errorThrown);
            console.error('Response:', jqXHR.responseText);
        }
    });

    // Add event listeners for field selection changes
    menuIds.forEach(menuId => {
        const selectElem = document.getElementById(menuId);
        if (selectElem) {
            selectElem.addEventListener('change', updateFieldSelection);
        }
    });
});