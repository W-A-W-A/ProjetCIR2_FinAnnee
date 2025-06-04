// Initialize map centered on France
const map = L.map('map').setView([46.6034, 1.8883], 6);

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Sample solar installation data (matching your image)
const solarInstallations = [
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

    let filtered = solarInstallations;

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
addMarkers(solarInstallations);

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