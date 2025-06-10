$(document).ready(function () {
    const params = new URLSearchParams(window.location.search);
    const installId = params.get('id');
    if (installId) {
        // Pré-remplissage pour modification
        $('#installId').val(installId);
        // Appel AJAX pour récupérer les données
        $.ajax({
            url: `./back/create.php?id=${installId}`,
            type: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (result) {
                if (result.success && result.data) {
                    // Remplis les champs du formulaire avec result.data
                    $('[name="installateur"]').val(result.data.install_nom || '');
                    $('[name="nb_panneaux"]').val(result.data.nb_pann || '');
                    $('[name="modele_panneaux"]').val(result.data.modele_pn || '');
                    $('[name="marque_panneaux"]').val(result.data.marque_pn || '');
                    $('[name="nb_onduleurs"]').val(result.data.nb_ond || '');
                    $('[name="modele_onduleurs"]').val(result.data.modele_ond || '');
                    $('[name="marque_onduleurs"]').val(result.data.marque_ond || '');
                    $('[name="surface"]').val(result.data.surface || '');
                    $('[name="puissance_crete"]').val(result.data.puissance_crete || '');
                    $('[name="production_pvgis"]').val(result.data.production_pvgis || '');
                    $('[name="commune"]').val(result.data.com_nom || '');
                    $('[name="departement"]').val(result.data.dep_nom || '');
                    $('[name="code_postal"]').val(result.data.code_postal || '');
                    $('[name="region"]').val(result.data.reg_nom || '');
                    $('[name="pays"]').val(result.data.pays_nom || '');
                    $('[name="coordonnees_gps"]').val(result.data.coordonnees_gps || '');
                    $('[name="date_installation"]').val(result.data.date_install || '');
                    $('[name="pente"]').val(result.data.pente || '');
                    $('[name="pente_opti"]').val(result.data.pente_opti || '');
                    $('[name="orientation"]').val(result.data.ori || '');
                    $('[name="orientation_opti"]').val(result.data.ori_opti || '');

                }
                // Affiche le bouton Modifier, cache le bouton Créer
                $('#createBtn').hide();
                $('#updateBtn').show();
            }
        });
    } else {
        // Création : bouton création visible, bouton modification caché
        $('#createBtn').show();
        $('#updateBtn').hide();
    }
    // Remove the form action to prevent default submission
    $('form').removeAttr('action');

    // Handle form submission
    $('form').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        // Get form data
        const formData = new FormData(this);

        // Show loading state
        const submitButton = $('button[name="create"]');
        const originalText = submitButton.text();
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Création en cours...');

        // Clear any existing messages
        $('.alert').remove();

        // Validate required fields
        let isValid = true;
        const requiredFields = ['installateur', 'nb_panneaux', 'commune'];

        requiredFields.forEach(field => {
            const input = $(`[name="${field}"]`);
            if (!input.val() || input.val().trim() === '') {
                isValid = false;
                input.addClass('is-invalid');
                if (!input.siblings('.invalid-feedback').length) {
                    input.after('<div class="invalid-feedback">Ce champ est obligatoire</div>');
                }
            } else {
                input.removeClass('is-invalid');
                input.siblings('.invalid-feedback').remove();
            }
        });

        // Validate GPS coordinates format if provided
        const coordsInput = $('[name="coordonnees_gps"]');
        if (coordsInput.val() && coordsInput.val().trim() !== '') {
            const coords = coordsInput.val().split(',');
            if (coords.length !== 2 || isNaN(parseFloat(coords[0])) || isNaN(parseFloat(coords[1]))) {
                isValid = false;
                coordsInput.addClass('is-invalid');
                if (!coordsInput.siblings('.invalid-feedback').length) {
                    coordsInput.after('<div class="invalid-feedback">Format: latitude,longitude (ex: 47.2184, -1.5536)</div>');
                }
            } else {
                coordsInput.removeClass('is-invalid');
                coordsInput.siblings('.invalid-feedback').remove();
            }
        }

        if (!isValid) {
            submitButton.prop('disabled', false).text(originalText);
            showMessage('Veuillez corriger les erreurs dans le formulaire.', 'danger');
            return;
        }

        // Make AJAX request
        $.ajax({
            url: './back/create.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showMessage(response.message, 'success');

                    // Reset form after successful creation
                    setTimeout(() => {
                        $('form')[0].reset();
                        $('.is-valid').removeClass('is-valid');

                        // Redirect to search page after 2 seconds
                        setTimeout(() => {
                            window.location.href = 'search.html';
                        }, 2000);
                    }, 1000);
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response Text:', xhr.responseText);

                let errorMessage = 'Une erreur est survenue lors de la création de l\'installation.';

                // Try to parse error response
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // If response is not JSON, check for common errors
                    if (xhr.status === 500) {
                        errorMessage = 'Erreur serveur. Veuillez vérifier vos données et réessayer.';
                    } else if (xhr.status === 0) {
                        errorMessage = 'Impossible de contacter le serveur. Vérifiez votre connexion.';
                    }
                }

                showMessage(errorMessage, 'danger');
            },
            complete: function () {
                // Reset button state
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });

    // Function to show messages
    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Insert message at the top of the form
        $('form').prepend(alertHtml);

        // Auto-dismiss success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                $('.alert-success').fadeOut();
            }, 5000);
        }

        // Scroll to top to show the message
        $('html, body').animate({ scrollTop: 0 }, 500);
    }

    // Real-time validation feedback
    $('input[required]').on('blur', function () {
        const input = $(this);
        if (input.val() && input.val().trim() !== '') {
            input.removeClass('is-invalid').addClass('is-valid');
            input.siblings('.invalid-feedback').remove();
        }
    });

    // GPS coordinates validation on input
    $('[name="coordonnees_gps"]').on('blur', function () {
        const input = $(this);
        const value = input.val().trim();

        if (value === '') return; // Optional field

        const coords = value.split(',');
        if (coords.length === 2 && !isNaN(parseFloat(coords[0])) && !isNaN(parseFloat(coords[1]))) {
            input.removeClass('is-invalid').addClass('is-valid');
            input.siblings('.invalid-feedback').remove();
        }
    });

    // Auto-format GPS coordinates
    $('[name="coordonnees_gps"]').on('input', function () {
        let value = $(this).val();
        // Remove extra spaces around comma
        value = value.replace(/\s*,\s*/, ',');
        $(this).val(value);
    });

    // Numeric input validation
    $('input[type="number"]').on('input', function () {
        const input = $(this);
        const value = parseFloat(input.val());

        // Check for negative values where they shouldn't be allowed
        if (['nb_panneaux', 'nb_onduleurs', 'surface', 'puissance_crete', 'production_pvgis'].includes(input.attr('name'))) {
            if (value < 0) {
                input.val(0);
            }
        }

        // Remove validation classes to reset state
        input.removeClass('is-invalid is-valid');
    });

    // Date validation (shouldn't be in the future)
    $('[name="date_installation"]').on('change', function () {
        const selectedDate = new Date($(this).val());
        const today = new Date();

        if (selectedDate > today) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">La date d\'installation ne peut pas être dans le futur</div>');
            }
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Tab functionality (keeping from original)
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            // Remove active class from all tabs
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');

            // Navigate to the page
            const href = this.getAttribute('href');
            if (href && href !== '#') {
                window.location.href = href;
            }
        });
    });
});