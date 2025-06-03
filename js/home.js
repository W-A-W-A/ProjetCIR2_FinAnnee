
// Simulated API data
const apiData = {
    enregistrements: 1500,
    regions: 2300,
    installateurs: 800,
    regionsAnnee: 3000,
    annee: 1200,
    marques: 400,
    onduleurs: 600,
    baseAnnee: 1900,
    installations: 2800
};

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