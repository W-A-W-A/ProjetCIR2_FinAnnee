console.log("Script Chargé");

const apiData = {
    enregistrements: 0,
    regions: 0,
    installateurs: 0,
    regionsAnnee: 0,
    annee: 0,
    marques: 0,
    onduleurs: 0,
    baseAnnee: 0,
    installations: 0
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

// Load stats from API with improved error handling
function loadStats() {
    return new Promise((resolve, reject) => {
        // Check if jQuery is available
        if (typeof $ !== 'undefined') {
            console.log('Loading stats with jQuery...');
            $.ajax({
                url: './back/stats.php',
                method: 'GET',
                dataType: 'json',
                timeout: 10000, // 10 second timeout
                success: function (data) {
                    console.log('Stats loaded successfully:', data);

                    // Validate response data
                    if (data && typeof data === 'object' && !data.error) {
                        for (const key in data) {
                            if (apiData.hasOwnProperty(key)) {
                                apiData[key] = parseInt(data[key]) || 0;
                            }
                        }
                        resolve(data);
                    } else {
                        console.error('Invalid or error response:', data);
                        reject(new Error(data.error || 'Invalid response format'));
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error Details:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status,
                        statusText: xhr.statusText
                    });

                    // Try to parse error response
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        reject(new Error(errorData.error || `Server Error: ${xhr.status}`));
                    } catch (parseError) {
                        reject(new Error(`Server Error: ${xhr.status} - ${xhr.responseText || error}`));
                    }
                }
            });
        } else {
            // Fallback to fetch API
            console.log('Loading stats with fetch API...');
            loadStatsWithFetch()
                .then(resolve)
                .catch(reject);
        }
    });
}

// Fetch API fallback
function loadStatsWithFetch() {
    return fetch('./back/stats.php', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            console.log('Fetch response status:', response.status);

            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                });
            }

            return response.json();
        })
        .then(data => {
            console.log('Stats loaded with fetch:', data);

            if (data && typeof data === 'object' && !data.error) {
                for (const key in data) {
                    if (apiData.hasOwnProperty(key)) {
                        apiData[key] = parseInt(data[key]) || 0;
                    }
                }
                return data;
            } else {
                throw new Error(data.error || 'Invalid response format');
            }
        });
}

// Initialize stats animation
function initializeStats() {
    console.log('Initializing stats animation with data:', apiData);

    Object.keys(apiData).forEach(key => {
        const circle = document.querySelector(`[data-stat="${key}"]`);
        if (circle) {
            console.log(`Animating ${key} with value ${apiData[key]}`);
            animateCounter(circle, apiData[key]);
        } else {
            console.warn(`Element with data-stat="${key}" not found`);
        }
    });
}

// Show error message to user
function showErrorMessage(message) {
    console.error('Showing error to user:', message);

    // You can customize this to show a nice error message in your UI
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-warning text-center';
        errorDiv.innerHTML = `
            <strong>Attention:</strong> Impossible de charger les statistiques en temps réel.<br>
            <small>Les données affichées sont des valeurs par défaut.</small>
        `;
        statsSection.insertBefore(errorDiv, statsSection.firstChild);
    }
}

// Main initialization function
function initializeApp() {
    console.log('Initializing application...');

    loadStats()
        .then(() => {
            console.log('Stats loaded successfully, starting animations...');
            setTimeout(initializeStats, 500);
        })
        .catch(error => {
            console.error('Failed to load stats:', error.message);
            showErrorMessage(error.message);

            // Still animate with default values (all zeros)
            setTimeout(initializeStats, 500);
        });
}

// jQuery DOM ready
$(document).ready(function () {
    console.log('jQuery DOM ready');
    initializeApp();
});

// Vanilla JS DOM ready (backup)
document.addEventListener('DOMContentLoaded', function () {
    console.log('Vanilla JS DOM ready');

    // Only run if jQuery version hasn't run yet
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, using vanilla JS');
        initializeApp();
    }
});

// Tab functionality
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            const tab = this.getAttribute('data-tab');

            // Only prevent default for hash links or the current page (accueil)
            if (href.startsWith('#') || tab === 'accueil') {
                e.preventDefault();

                // Remove active class from all tabs
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

                // Add active class to clicked tab
                this.classList.add('active');

                console.log(`Loading ${tab} content...`);
            } else {
                // For external links (like map.html, search.html), let the browser navigate normally
                console.log(`Navigating to: ${href}`);
                // Don't prevent default - let the link work normally
            }
        });
    });
});