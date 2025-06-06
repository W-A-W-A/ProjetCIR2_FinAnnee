
// launched when a field selection is changed
function updateFieldSelection(event) {
    // we don't use the event's data because we need ALL infos, even the ones already selected beforehand
    const selIdBrand = document.getElementById('ondBrand').value;
    const selIdPanel = document.getElementById('panelBrand').value;
    const selIdDep = document.getElementById('department').value;

    console.log("current selection : ", selIdBrand, selIdPanel,selIdDep);

    resultBlock = `<div class="rounded-2 border border-dark d-grid gap-3 p-2">
        <div class="rounded-2 border border-dark d-flex flex-column resultElem">
          <div class="d-flex justify-content-between text-light elementTop">
            <p class="my-auto p-2 text-center" style="width:20%;">Nombre de panneaux</p>
            <p class="my-auto p-2 text-center" style="width:20%;">Surface</p>
            <p class="my-auto p-2 text-center" style="width:20%;">Puissance crête</p>
            <form action="details.html" method="get" class="my-auto p-2 text-center">
              <button type="submit" name="detail" class="detailBtn btn rounded-5 m-2 text-center">Voir détail</button>
            </form>
          </div>
          <div class="d-flex justify-content-between elementBottom">
            <p class="my-auto p-2 text-center" style="width:50%;">Date</p>
            <p class="my-auto p-2 text-center" style="width:50%;">Localité</p>
          </div>
        </div>
      </div>`;
    
    // creates the results blocks with data pulled from the MySQL db
    $.ajax({
        url: './back/panels.php',
        method: 'GET',
        dataType: 'json',
        data: {
            selIdBrand: selIdBrand,
            selIdPanel: selIdPanel,
            selIdDep: selIdDep
        },
        success: function(response) {
            // Clear previous results
            $('.research-result').empty();

            // Loop through results and create result blocks
            response["results"].forEach(result => {
                // formats result block with data from MySQL db
                const resultBlock = `
                <div class="rounded-2 border border-dark d-grid gap-3 p-2">
                    <div class="rounded-2 border border-dark d-flex flex-column resultElem">
                        <div class="d-flex justify-content-between text-light elementTop">
                            <p class="my-auto p-2 text-center" style="width:20%;">${result["nb_panneaux"]} panneaux</p>
                            <p class="my-auto p-2 text-center" style="width:20%;">${result["surface"]} m²</p>
                            <p class="my-auto p-2 text-center" style="width:20%;">${result["puissance_crete"]} kW/h</p>
                            <form action="detail.html" method="get" class="my-auto p-2 text-center">
                                <button type="submit" name="detail" value="${result["id"]}" class="detailBtn btn rounded-5 m-2 text-center">Voir détail</button>
                            </form>
                        </div>
                        <div class="d-flex justify-content-between elementBottom">
                            <p class="my-auto p-2 text-center" style="width:50%;">${result["date_installation"]} </p>
                            <p class="my-auto p-2 text-center" style="width:50%;">${result["localite"]}</p>
                        </div>
                    </div>
                </div>`;
                
                // best way i found to insert into HTML code because other methods throw weird errors here
                document.getElementsByClassName('research-result')[0].insertAdjacentHTML('beforeend', resultBlock);
            });
        },
        error: function() {
            console.error('Erreur lors de la récupération des résultats');
        }
    });
}

// launched when page is loaded
$(document).ready(function () {
    console.log("Script ready")
    const menuIds = ["ondBrand", "panelBrand", "department"];

    // loading the available fields from MySQL db
    $.ajax({
        url: './back/search.php', // not sure it's the right php file to call, will see later
        method: 'GET',
        dataType: 'json',
        timeout: 15000,
        success: function (response) {
            console.log(response); // Debugging: log the name data to console
            menuIds.forEach(menuId => { // for each selection menu
                const ondBrandSelect = document.getElementById(menuId); // Get the menu element by ID
                if (
                    ondBrandSelect &&
                    Array.isArray(response[menuId]["names"]) && // checks the brand array
                    Array.isArray(response[menuId]["values"]) && // checks value array too for missing ids
                    response[menuId]["names"].length === response[menuId]["values"].length
                ) {
                    ondBrandSelect.innerHTML = ''; // clears placeholder options
                    response[menuId]["names"].forEach((brand, idx) => {
                        const option = document.createElement('option');
                        option.value = response[menuId]["values"][idx]; // uses ID as value
                        option.textContent = brand; // uses brand name as display text
                        ondBrandSelect.appendChild(option);
                    });
                }
            });
        },
        error: function (jqXHR, textStatus, errorThrown) {
            // the most detailed error message on earth
            console.error('Error : Couldn\'t load name from MySQL database');
            console.error('Status:', textStatus);
            console.error('Error Thrown:', errorThrown);
            console.error('Response:', jqXHR.responseText);
        }
    });

    // event listener for field selection change
    menuIds.forEach(menuId => {
        const selectElem = document.getElementById(menuId);
        if (selectElem) { // ensures we don't crash if the element is not found
            selectElem.addEventListener('change', updateFieldSelection);
        }
    });
});





/*
$(document).ready(function () {
    $.ajax({
        url: 'back/search.php', // not sure it's the right php file to call, will see later
        method: 'GET',
        dataType: 'json',
        success: function (brands) {
            // "<option value=\"$marque\">$marque</option>";
            console.log(brands); // Debugging: log the brands data to console
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
            console.error('Error : Couldn\'t load brands from MySQL database');
        }
    });
});*/

/*
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
*/