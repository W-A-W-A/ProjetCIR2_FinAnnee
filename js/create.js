// Global variables
let correctKeyHash = '';
let isModifyMode = false;
let currentInstallationId = null;

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

// Check if user is already authenticated
function checkAuthentication() {
    const authToken = getCookie('secretKeyAuth');
    return authToken === 'authenticated';
}

// Load installation data for modification
function loadInstallationForModify(installId) {
    return $.ajax({
        url: `./back/detail.php?id=${installId}`,
        method: 'GET',
        dataType: 'json'
    });
}

// Populate form with existing data
function populateForm(data) {
    // Parse coordinates
    let lat = '', lon = '';
    if (data.lat && data.lon) {
        lat = data.lat;
        lon = data.lon;
    }

    // Parse date - FIX: Handle date format properly
    let dateValue = '';
    if (data.date_install) {
        // Convert MM/YYYY to YYYY-MM-DD format for date input
        const dateParts = data.date_install.split('/');
        if (dateParts.length === 2) {
            const month = dateParts[0].padStart(2, '0');
            const year = dateParts[1];
            // Set to first day of the month for proper date input format
            dateValue = `${year}-${month}-01`;
        }
    } else if (data.an_installation && data.mois_installation) {
        // Alternative: use separate year and month fields if available
        const month = data.mois_installation.toString().padStart(2, '0');
        const year = data.an_installation.toString();
        dateValue = `${year}-${month}-01`;
    }

    console.log('Setting date value:', dateValue); // Debug log

    // Populate form fields
    const setValue = (name, value) => {
        const element = document.querySelector(`input[name="${name}"]`);
        if (element) {
            element.value = value || '';
        }
    };

    setValue('installateur', data.nom_installateur);
    setValue('nb_panneaux', data.nb_pann);
    setValue('modele_panneaux', data.modele_pn);
    setValue('marque_panneaux', data.marque_pn);
    setValue('nb_onduleurs', data.nb_ond);
    setValue('modele_onduleurs', data.modele_ond);
    setValue('marque_onduleurs', data.marque_ond);
    setValue('surface', data.surface);
    setValue('date_installation', dateValue);
    setValue('commune', data.com_nom);
    setValue('departement', data.dep_nom);
    setValue('code_postal', data.code_postal);
    setValue('region', data.reg_nom);
    setValue('pays', data.pays_nom);
    setValue('coordonnees_gps', lat && lon ? `${lat}, ${lon}` : '');
    setValue('puissance_crete', data.puissance_crete);
    setValue('production_pvgis', data.prod_pvgis);
    setValue('pente', data.pente);
    setValue('pente_optimum', data.pente_opti);
    setValue('orientation', data.ori);
    setValue('orientation_optimum', data.ori_opti);
}

// Handle form submission
function handleFormSubmit(event) {
    event.preventDefault();

    if (!checkAuthentication()) {
        alert('Vous devez être authentifié pour effectuer cette action.');
        window.location.href = 'search.html';
        return;
    }

    const formData = new FormData(event.target);

    // Add the appropriate action parameter
    if (isModifyMode && currentInstallationId) {
        formData.append('update', '1');
        formData.append('id', currentInstallationId);
    } else {
        formData.append('create', '1');
    }

    // FIX: Use create.php for both create and update operations
    // The PHP file handles both based on the presence of 'update' parameter
    $.ajax({
        url: './back/create.php', // Use the single PHP file that handles both operations
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            let result;
            try {
                // Try to parse JSON response
                result = typeof response === 'string' ? JSON.parse(response) : response;
            } catch (e) {
                console.error('JSON parse error:', e);
                console.log('Raw response:', response);
                alert('Erreur de format de réponse du serveur');
                return;
            }

            if (result.success) {
                const message = isModifyMode ? 'Installation modifiée avec succès!' : 'Installation créée avec succès!';
                alert(message);

                if (isModifyMode) {
                    // Redirect back to detail page
                    window.location.href = `detail.html?detail=${currentInstallationId}`;
                } else {
                    // Redirect to search page
                    window.location.href = 'search.html';
                }
            } else {
                alert(result.message || 'Erreur lors de la sauvegarde');
            }
        },
        error: function (xhr, status, error) {
            console.error('Form submission error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            alert('Erreur lors de la communication avec le serveur: ' + error);
        }
    });
}

// Cancel action - return to detail page or search
function handleCancel() {
    if (isModifyMode && currentInstallationId) {
        window.location.href = `detail.html?detail=${currentInstallationId}`;
    } else {
        window.location.href = 'search.html';
    }
}

// Update page title and button text based on mode
function updatePageForMode() {
    const titleElement = document.querySelector('title');
    const headerTitle = document.querySelector('.elementTop p');
    const submitButton = document.querySelector('button[name="create"]');

    if (isModifyMode) {
        if (titleElement) titleElement.textContent = 'Modifier Installation - Panneaux Photovoltaïque';
        if (headerTitle) headerTitle.textContent = 'Modifier Installation';
        if (submitButton) {
            submitButton.textContent = 'Modifier Installation';
            submitButton.name = 'modify';
        }
    } else {
        if (titleElement) titleElement.textContent = 'Créer Installation - Panneaux Photovoltaïque';
        if (headerTitle) headerTitle.textContent = 'Créer Installation';
        if (submitButton) {
            submitButton.textContent = 'Créer Installation';
            submitButton.name = 'create';
        }
    }
}

// Add cancel button
function addCancelButton() {
    const submitButton = document.querySelector('button[name="create"], button[name="modify"]');
    if (submitButton && !document.querySelector('.cancel-btn')) {
        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.className = 'createBtn btn rounded-5 m-2 text-center cancel-btn';
        cancelButton.textContent = 'Annuler';
        cancelButton.onclick = handleCancel;

        submitButton.parentNode.appendChild(cancelButton);
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Load key hash
        loadKeyHash();

        // Check if we're in modify mode
        const params = new URLSearchParams(window.location.search);
        const installId = params.get('id');

        if (installId) {
            isModifyMode = true;
            currentInstallationId = installId;

            console.log('Modify mode - loading installation:', installId);

            // Check authentication first
            if (!checkAuthentication()) {
                alert('Vous devez être authentifié pour modifier une installation.');
                window.location.href = 'search.html';
                return;
            }

            // Load existing data
            const data = await loadInstallationForModify(installId);

            if (data.error) {
                throw new Error(data.message);
            }

            // Populate form with existing data
            populateForm(data);
        }

        // Update page elements based on mode
        updatePageForMode();

        // Add cancel button
        addCancelButton();

        // Set up form submission handler
        const form = document.querySelector('form');
        if (form) {
            form.onsubmit = handleFormSubmit;
        }

    } catch (error) {
        console.error('Create page initialization error:', error);
        alert('Erreur lors du chargement de la page: ' + error.message);
        window.location.href = 'search.html';
    }
});