// Initialize map centered on France
const map = L.map('map').setView([46.6034, 1.8883], 6);

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
    //     , 
    //     {
    //     attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    // }
).addTo(map);

let solarInstallations = [];
let apiData = {};

// Function to load solar installations data
function loadSolarInstallations(filters = {}) {
    // Build query parameters
    let queryParams = new URLSearchParams();

    if (filters.department && filters.department !== 'all') {
        queryParams.append('department', filters.department);
    }
    if (filters.region && filters.region !== 'all') {
        queryParams.append('region', filters.region);
    }
    if (filters.year && filters.year !== 'all') {
        queryParams.append('year', filters.year);
    }

    const url = `back/solar_installations.php${queryParams.toString() ? '?' + queryParams.toString() : ''}`;

    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                solarInstallations = response.data;
                apiData.solarInstallations = response.data;

                // Update map with new data
                updateMap(solarInstallations);

                console.log(`Loaded ${response.count} solar installations`);
            } else {
                console.error('API Error:', response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error during data retrieval from database:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
        }
    });
}


// Initial load when document is ready
$(document).ready(function () {
    // Load all installations initially
    loadSolarInstallations();

    // Load other stats if needed
    $.ajax({
        url: 'back/stats.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            for (const key in data) {
                apiData[key] = data[key];
            }
        },
        error: function () {
            console.error('Error during stats retrieval from database');
        }
    });
});




// Sample solar installation data (matching your image)
const solarInstallationsSimulation = [
    // Northern France
    { lat: 50.6292, lng: 3.0573, city: "Lille", year: 2023, department: "59", power: "150 kW" },
    { lat: 49.4944, lng: 0.1079, city: "Le Havre", year: 2022, department: "76", power: "200 kW" },
    { lat: 48.8566, lng: 2.3522, city: "Paris", year: 2024, department: "75", power: "80 kW" },
    { lat: 49.2583, lng: 4.0317, city: "Reims", year: 2023, department: "51", power: "120 kW" },

    // Central France
    { lat: 47.9029, lng: 1.9093, city: "Orléans", year: 2022, department: "45", power: "180 kW" },
    { lat: 47.3215, lng: 5.0415, city: "Dijon", year: 2023, department: "21", power: "160 kW" },
    { lat: 45.7640, lng: 4.8357, city: "Lyon", year: 2024, department: "69", power: "250 kW" },
    { lat: 46.3197, lng: 2.5730, city: "Bourges", year: 2021, department: "18", power: "90 kW" },

    // Western France
    { lat: 47.2184, lng: -1.5536, city: "Nantes", year: 2023, department: "44", power: "140 kW" },
    { lat: 48.1173, lng: -1.6778, city: "Rennes", year: 2022, department: "35", power: "110 kW" },
    { lat: 46.1603, lng: -1.1511, city: "La Rochelle", year: 2024, department: "17", power: "170 kW" },

    // Eastern France
    { lat: 48.5734, lng: 7.7521, city: "Strasbourg", year: 2023, department: "67", power: "190 kW" },
    { lat: 47.7516, lng: 7.3358, city: "Mulhouse", year: 2022, department: "68", power: "130 kW" },
    { lat: 48.6921, lng: 6.1844, city: "Nancy", year: 2024, department: "54", power: "100 kW" },

    // Southern France
    { lat: 43.6047, lng: 1.4442, city: "Toulouse", year: 2024, department: "31", power: "220 kW" },
    { lat: 43.2965, lng: 5.3698, city: "Marseille", year: 2023, department: "13", power: "280 kW" },
    { lat: 43.7102, lng: 7.2620, city: "Nice", year: 2022, department: "06", power: "150 kW" },
    { lat: 43.8378, lng: 4.3601, city: "Nîmes", year: 2024, department: "30", power: "160 kW" },
    { lat: 44.8378, lng: -0.5792, city: "Bordeaux", year: 2023, department: "33", power: "200 kW" },
    { lat: 43.9493, lng: 4.8055, city: "Avignon", year: 2021, department: "84", power: "140 kW" },

    // Additional installations
    { lat: 45.1885, lng: 5.7245, city: "Grenoble", year: 2023, department: "38", power: "175 kW" },
    { lat: 43.1102, lng: 5.9280, city: "Toulon", year: 2022, department: "83", power: "195 kW" },
    { lat: 43.5263, lng: 5.4454, city: "Aix-en-Provence", year: 2024, department: "13", power: "165 kW" }
];

let markers = [];

// Function to create custom icon
function createSolarIcon() {
    return L.divIcon({
        className: 'solar-marker',
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });
}

// Function to add markers to map
function addMarkers(installations) {
    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    installations.forEach(installation => {
        const marker = L.marker([installation.lat, installation.lng], {
            icon: createSolarIcon()
        });

        const popupContent = `
          <div class="popup-title">${installation.city}</div>
          <div class="popup-details">
            <strong>Année:</strong> ${installation.year}<br>
            <strong>Département:</strong> ${installation.department}<br>
            <strong>Puissance:</strong> ${installation.power}
          </div>
        `;

        marker.bindPopup(popupContent);
        marker.addTo(map);
        markers.push(marker);
    });
}

// Function to filter installations
function filterInstallations() {
    const selectedYear = document.getElementById('yearSelect').value;
    const selectedDepartment = document.getElementById('departmentSelect').value;

    let filtered = solarInstallationsSimulation;

    if (selectedYear) {
        filtered = filtered.filter(installation => installation.year.toString() === selectedYear);
    }

    if (selectedDepartment) {
        filtered = filtered.filter(installation => installation.department === selectedDepartment);
    }

    addMarkers(filtered);
}

// Event listeners for filters
document.getElementById('yearSelect').addEventListener('change', filterInstallations);
document.getElementById('departmentSelect').addEventListener('change', filterInstallations);

// Initialize map with all markers
addMarkers(solarInstallationsSimulation);

// Tab functionality
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
        if (this.getAttribute('href').startsWith('#')) {
            e.preventDefault();

            // Remove active class from all tabs
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');
        }
    });
});



// Global variable to store the installations data




/*

// Event handlers for filter changes
$(document).on('change', '#department-select', function () {
    const selectedDepartment = $(this).val();
    const selectedRegion = $('#region-select').val();
    const selectedYear = $('#year-select').val();

    loadSolarInstallations({
        department: selectedDepartment,
        region: selectedRegion,
        year: selectedYear
    });
});

$(document).on('change', '#region-select', function () {
    const selectedRegion = $(this).val();
    const selectedDepartment = $('#department-select').val();
    const selectedYear = $('#year-select').val();

    loadSolarInstallations({
        department: selectedDepartment,
        region: selectedRegion,
        year: selectedYear
    });
});

$(document).on('change', '#year-select', function () {
    const selectedYear = $(this).val();
    const selectedDepartment = $('#department-select').val();
    const selectedRegion = $('#region-select').val();

    loadSolarInstallations({
        department: selectedDepartment,
        region: selectedRegion,
        year: selectedYear
    });
});

// Function to update the map (you'll need to implement this based on your mapping library)
function updateMap(installations) {
    // Clear existing markers
    clearMapMarkers();

    // Add new markers
    installations.forEach(function (installation) {
        addMapMarker({
            lat: installation.lat,
            lng: installation.lng,
            city: installation.city,
            year: installation.year,
            department: installation.department_name,
            region: installation.region_name,
            power: installation.power
        });
    });

    // Update map bounds if needed
    if (installations.length > 0) {
        fitMapBounds(installations);
    }
}

// Helper functions (implement these based on your mapping library)
function clearMapMarkers() {
    // Implementation depends on your mapping library (Google Maps, Leaflet, etc.)
    console.log('Clearing map markers...');
}

function addMapMarker(data) {
    // Implementation depends on your mapping library
    console.log('Adding marker:', data);
}

function fitMapBounds(installations) {
    // Implementation depends on your mapping library
    console.log('Fitting map bounds for', installations.length, 'installations');
}



*/