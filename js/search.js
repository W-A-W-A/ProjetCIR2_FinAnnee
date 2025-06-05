
$(document).ready(function () {
    $.ajax({
        url: 'api/stats.php', // not sure it's the right php file to call, will see later
        method: 'GET',
        dataType: 'json',
        success: function (brands) {
            const ondBrandSelect = document.getElementById('ondBrand');
            if (ondBrandSelect && Array.isArray(brands)) { // if we found the HTML element and the brands exists
                ondBrandSelect.innerHTML = ''; // flush placeholders down the skibidi
                brands.forEach(brand => {
                    const option = document.createElement('option');
                    option.value = brand.id || brand.value || brand; // Adjust according to your data structure
                    option.textContent = brand.name || brand.label || brand;
                    ondBrandSelect.appendChild(option);
                });
            }
        },
        error: function () {
            console.error('Error loading brands from database');
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