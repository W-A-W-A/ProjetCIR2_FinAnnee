console.log("Map script loaded");

// Global variables
let map;
let markersLayer;
let solarData = [];

// Initialize the map
function initializeMap() {
    console.log("Initializing map...");

    // Create map centered on France
    map = L.map('map').setView([46.603354, 1.888334], 6);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);

    // Create markers layer group
    markersLayer = L.layerGroup().addTo(map);

    console.log("Map initialized successfully");
}

// Load solar installations data
function loadSolarInstallations(filters = {}) {
    console.log("Loading solar installations with filters:", filters);

    // Check if this is the initial load (no filters applied)
    const isInitialLoad = !filters.year && !filters.department && !filters.region;

    // Build query parameters
    const params = new URLSearchParams();
    if (filters.year && filters.year !== '') {
        params.append('year', filters.year);
    }
    if (filters.department && filters.department !== '') {
        params.append('department', filters.department);
    }
    if (filters.region && filters.region !== '') {
        params.append('region', filters.region);
    }

    // Add limit parameter for initial load
    if (isInitialLoad) {
        params.append('limit', '500');
    }

    const url = `./back/map.php${params.toString() ? '?' + params.toString() : ''}`;
    console.log("Requesting URL:", url);

    return $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        timeout: 15000,
        success: function (response) {
            console.log('Solar installations loaded:', response);

            if (response.success && response.data) {
                solarData = response.data;
                updateMapMarkers();
                updateDataInfo(response.count, response.total_in_db, filters, isInitialLoad);
            } else {
                console.error('Invalid response format:', response);
                showError('Format de réponse invalide');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error during data retrieval:', {
                status: status,
                error: error,
                statusCode: xhr.status,
                responseText: xhr.responseText
            });

            let errorMessage = 'Erreur lors du chargement des données';

            if (xhr.status === 404) {
                errorMessage = 'API introuvable - Vérifiez que map.php existe dans le dossier back/';
            } else if (xhr.status === 500) {
                try {
                    const errorData = JSON.parse(xhr.responseText);
                    errorMessage = errorData.error || 'Erreur serveur';
                } catch (e) {
                    errorMessage = 'Erreur serveur interne';
                }
            } else if (status === 'timeout') {
                errorMessage = 'Délai d\'attente dépassé';
            }

            showError(errorMessage);
        }
    });
}

// Load years for dropdown
function loadYears() {
    console.log("Loading years...");

    return $.ajax({
        url: './back/map.php?action=get_years',
        method: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function (response) {
            console.log('Years loaded:', response);

            if (response.success && response.data) {
                populateYearSelect(response.data);
            } else {
                console.error('Failed to load years:', response);
                showError('Erreur lors du chargement des années');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error loading years:', error);
            showError('Erreur lors du chargement des années');
        }
    });
}

// Load departments for dropdown
function loadDepartments() {
    console.log("Loading departments...");

    return $.ajax({
        url: './back/map.php?action=get_departments',
        method: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function (response) {
            console.log('Departments loaded:', response);

            if (response.success && response.data) {
                populateDepartmentSelect(response.data);
            } else {
                console.error('Failed to load departments:', response);
                showError('Erreur lors du chargement des départements');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error loading departments:', error);
            showError('Erreur lors du chargement des départements');
        }
    });
}

// Populate year select dropdown
function populateYearSelect(years) {
    const $select = $('#yearSelect');

    // Keep the default "Toutes les années" option
    $select.find('option:not(:first)').remove();

    // Add year options
    years.forEach(function (year) {
        $select.append(`<option value="${year}">${year}</option>`);
    });

    console.log(`Added ${years.length} year options`);
}

// Populate department select dropdown
function populateDepartmentSelect(departments) {
    const $select = $('#departmentSelect');

    // Keep the default "Tous les départements" option
    $select.find('option:not(:first)').remove();

    // Add department options
    departments.forEach(function (dept) {
        $select.append(`<option value="${dept.id}">${dept.name} (${dept.code})</option>`);
    });

    console.log(`Added ${departments.length} department options`);
}

// Update map markers
function updateMapMarkers() {
    console.log(`Updating map with ${solarData.length} installations`);

    // Clear existing markers
    markersLayer.clearLayers();

    // Add new markers
    solarData.forEach(function (installation, index) {
        if (installation.lat && installation.lng) {
            // Create custom icon based on power
            const powerIcon = getPowerIcon(installation.power_numeric);

            // Create marker
            const marker = L.marker([installation.lat, installation.lng], {
                icon: powerIcon
            });

            // Create popup content
            const popupContent = `
                <div class="installation-popup">
                    <h6><strong>${installation.city || 'Localisation inconnue'}</strong></h6>
                    <p><strong>Année:</strong> ${installation.year || 'N/A'}</p>
                    <p><strong>Puissance:</strong> ${installation.power || 'N/A'}</p>
                    <p><strong>Département:</strong> ${installation.department_name || 'N/A'}</p>
                    <p><strong>Région:</strong> ${installation.region_name || 'N/A'}</p>
                    <p><a href="./detail.html?detail=${installation.id}"><strong>Details d'Installation</strong></a></p>
                </div>
            `;

            marker.bindPopup(popupContent);
            markersLayer.addLayer(marker);
        } else {
            console.warn(`Installation ${index} has invalid coordinates:`, installation);
        }
    });

    // Fit map to markers if we have data and not too many markers
    if (solarData.length > 0 && solarData.length <= 1000) {
        try {
            // Get all marker positions
            const markers = [];
            markersLayer.eachLayer(function (layer) {
                if (layer.getLatLng) {
                    markers.push(layer.getLatLng());
                }
            });

            if (markers.length > 0) {
                const group = new L.featureGroup(markersLayer.getLayers());
                map.fitBounds(group.getBounds(), { padding: [20, 20] });
            }
        } catch (e) {
            console.warn('Could not fit bounds:', e);
        }
    }
}

// Get icon based on power rating
function getPowerIcon(power) {
    let color = '#28a745'; // green for small installations
    let size = [20, 20];

    if (power > 100) {
        color = '#dc3545'; // red for large installations
        size = [30, 30];
    } else if (power > 50) {
        color = '#ffc107'; // yellow for medium installations
        size = [25, 25];
    }

    return L.divIcon({
        html: `<div style="background-color: ${color}; width: ${size[0]}px; height: ${size[1]}px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
        className: 'custom-marker',
        iconSize: size,
        iconAnchor: [size[0] / 2, size[1] / 2]
    });
}

// Update data information display
function updateDataInfo(count, total, filters, isInitialLoad = false) {
    console.log(`Displaying ${count} installations`);

    // Remove existing info panel
    $('.data-info').remove();

    // Create info panel
    let filterText = '';
    if (filters.year) {
        filterText += ` • Année: ${filters.year}`;
    }
    if (filters.department) {
        filterText += ` • Département: ${filters.department}`;
    }

    // Show different messages for initial load vs filtered results
    let infoText = '';
    if (isInitialLoad && count >= 500) {
        infoText = `<strong>${count}</strong> installation${count > 1 ? 's' : ''} affichée${count > 1 ? 's' : ''} sur ${total} total${filterText}<br>
                   <small class="text-muted">Sélectionnez une année ou un département pour voir tous les résultats</small>`;
    } else {
        infoText = `<strong>${count}</strong> installation${count > 1 ? 's' : ''} trouvée${count > 1 ? 's' : ''}${filterText}`;
    }

    const infoPanel = `
        <div class="data-info alert alert-info">
            ${infoText}
        </div>
    `;

    $('.map-controls').after(infoPanel);
}

// Show error message
function showError(message) {
    $('.error-message').remove();

    const errorPanel = `
        <div class="error-message alert alert-danger">
            <strong>Erreur:</strong> ${message}
        </div>
    `;

    $('.map-controls').after(errorPanel);
}

// Handle filter changes
function handleFilterChange() {
    const filters = {
        year: $('#yearSelect').val(),
        department: $('#departmentSelect').val()
    };

    console.log('Filters changed:', filters);
    loadSolarInstallations(filters);
}

// Initialize everything when document is ready
$(document).ready(function () {
    console.log('Document ready, initializing map application...');

    // Initialize map
    initializeMap();

    // Load filter options first
    Promise.all([
        loadYears(),
        loadDepartments()
    ]).then(() => {
        console.log('Filter options loaded successfully');

        // Then load initial data (limited to 500 markers)
        return loadSolarInstallations();
    }).then(() => {
        console.log('Initial data loaded successfully');
    }).catch((error) => {
        console.error('Failed to initialize application:', error);
    });

    // Set up event listeners for filters
    $('#yearSelect, #departmentSelect').on('change', handleFilterChange);

    console.log('Map application initialized');
});

// Handle map resize (useful if the map container size changes)
function resizeMap() {
    if (map) {
        map.invalidateSize();
    }
}

// Export functions for potential external use
window.mapApp = {
    resizeMap: resizeMap,
    loadSolarInstallations: loadSolarInstallations,
    handleFilterChange: handleFilterChange,
    loadYears: loadYears,
    loadDepartments: loadDepartments
};