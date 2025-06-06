
const apiData = {
    marqueOnduleur: "",
    marquePanneau: "",
    departement: "",
    mois: 0,
    annee: 0,
    surface: 0,
    nbPanneaux: 0,
    puissanceCrete: 0,
    commune: "",
};

$(document).ready(function () {
    $.ajax({
        url: 'back/detail.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            for (const key in data) {
                apiData[key] = data[key];
            }
        },
        error: function () {
            console.error('Error during data retrival from database');
        }
    });
});


// Animate counters
function animateCounter(element, finalValue) {
    let currentValue = 0;
    const increment = finalValue / 50;
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= finalValue) {
            currentValue = finalValue;
            clearInterval(timer);
        }
        element.textContent = Math.floor(currentValue).toLocaleString();
    }, 30);
}

// Load stats with animation
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
        Object.keys(apiData).forEach(key => {
            const circle = document.querySelector(`[data-stat="${key}"]`);
            if (circle) {
                animateCounter(circle, apiData[key]);
            }
        });
    }, 500);
});

// Tab functionality
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();

        // Remove active class from all tabs
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

        // Add active class to clicked tab
        this.classList.add('active');

        // Here you would typically load different content based on the tab
        const tab = this.getAttribute('data-tab');
        console.log(`Loading ${tab} content...`);
    });
});