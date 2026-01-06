<?php

/**
 * Template Name: Seeking Service Template
 */

// Process form submission BEFORE header
if (isset($_POST['seeking_submit'])) {
    // Ensure session is started
    if (!session_id()) {
        session_start();
    }
    
    // Verify nonce
    if (!isset($_POST['seeking_nonce'])) {
        wp_die('Erreur: Nonce manquant. Veuillez réessayer.');
    }
    
    if (!wp_verify_nonce($_POST['seeking_nonce'], 'seeking_action')) {
        wp_safe_redirect(home_url('/seeking-service?registration=error&message=nonce_failed'));
        exit;
    }
    
    // Check session
    if (!isset($_SESSION['registration_data']) || !isset($_SESSION['registration_data']['step2_completed'])) {
        wp_safe_redirect(home_url('/signup?registration=error&message=session_expired'));
        exit;
    }

    // Validate required fields
    $errors = array();
    
    if (empty($_POST['biographie']) || trim($_POST['biographie']) === '') {
        $errors[] = 'biographie';
    }
    
    if (empty($_POST['genre']) || trim($_POST['genre']) === '') {
        $errors[] = 'genre';
    }
    
    if (empty($_POST['music_genres']) || !is_array($_POST['music_genres']) || count($_POST['music_genres']) === 0) {
        $errors[] = 'music_genres';
    }
    
    // If validation errors, redirect back with error message
    if (!empty($errors)) {
        $error_params = 'registration=error&fields=' . implode(',', $errors);
        wp_safe_redirect(home_url('/seeking-service?' . $error_params));
        exit;
    }

    $reg_data = $_SESSION['registration_data'];
    
    // Check if user already exists
    if (username_exists($reg_data['user_login']) || email_exists($reg_data['user_email'])) {
        wp_safe_redirect(home_url('/seeking-service?registration=error&message=user_already_exists'));
        exit;
    }
    
    // Create user with all data from session
    $user_id = create_user_with_meta($reg_data['user_login'], $reg_data['user_pass'], $reg_data['user_email'], array(
        'first_name' => $reg_data['first_name'],
        'last_name' => $reg_data['last_name'],
        'phone' => $reg_data['phone'],
        'ville' => $reg_data['ville'],
        'service_type' => $reg_data['service_type']
    ));

    if (!$user_id) {
        wp_safe_redirect(home_url('/seeking-service?registration=error&message=user_creation_failed'));
        exit;
    }

    // Handle profile photo upload
    $photo_result = handle_profile_photo_upload($user_id);
    if ($photo_result === 'size_error') {
        wp_safe_redirect(home_url('/seeking-service?registration=error&message=photo_too_large'));
        exit;
    } elseif ($photo_result === 'type_error') {
        wp_safe_redirect(home_url('/seeking-service?registration=error&message=photo_invalid_type'));
        exit;
    }

    // Save form data
    if (isset($_POST['biographie'])) {
        update_user_meta($user_id, 'biographie', sanitize_textarea_field($_POST['biographie']));
    }
    if (isset($_POST['genre'])) {
        update_user_meta($user_id, 'genre', sanitize_text_field($_POST['genre']));
    }
    
    if (isset($_POST['music_genres']) && is_array($_POST['music_genres'])) {
        update_user_meta($user_id, 'music_genres', array_map('sanitize_text_field', $_POST['music_genres']));
    }

    // Auto-login user
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    
    // Clear session data
    unset($_SESSION['registration_data']);
    
    // Redirect to profile
    wp_safe_redirect(home_url('/userprofil?registration=success'));
    exit;
}

get_header();

// Check if user has registration data in session and correct service type
check_registration_session('seek');

// Show error/success messages
display_registration_error_message();
?>

<div class="service-container">
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Left Panel: Quote Section (2/5 width) -->
            <div class="col-md-5 service-quote-panel">
                <div class="service-quote-content">
                    <p class="service-quote-text">La bonne rencontre peut changer tout un projet</p>
                </div>
            </div>

            <!-- Right Panel: Form Section (3/5 width) -->
            <div class="col-md-7 service-form-panel">
                <div class="service-form-wrapper">
                    <!-- Stepper -->
                    <div class="registration-stepper" role="progressbar" aria-valuenow="3" aria-valuemin="1" aria-valuemax="3" aria-label="Progression de l'inscription">
                        <div class="stepper-step completed">
                            <div class="stepper-step-number">1</div>
                            <div class="stepper-step-label">Identité</div>
                        </div>
                        <div class="stepper-step completed">
                            <div class="stepper-step-number">2</div>
                            <div class="stepper-step-label">Contact</div>
                        </div>
                        <div class="stepper-step active">
                            <div class="stepper-step-number">3</div>
                            <div class="stepper-step-label">Profil</div>
                        </div>
                    </div>

                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="service-form" id="seeking-form" enctype="multipart/form-data" novalidate>
                        <?php wp_nonce_field('seeking_action', 'seeking_nonce'); ?>
                        
                        <h2 class="form-step-title">Profil</h2>
                        <p class="form-step-description">Présentez-vous et vos préférences</p>

                        <!-- Photo Upload Section -->
                        <div class="service-photo-section mb-4">
                            <div class="service-photo-upload-wrapper">
                                <input type="file" name="profile_photo" id="seeking_profile_photo" accept="image/*" class="service-photo-input" style="display: none;">
                                <label for="seeking_profile_photo" class="service-photo-label-wrapper">
                                    <div class="service-photo-preview" id="seeking-photo-preview">
                                        <div class="service-photo-placeholder">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="service-photo-upload-icon">
                                                <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" fill="currentColor"/>
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M1 12C1 5.92487 5.92487 1 12 1C18.0751 1 23 5.92487 23 12C23 18.0751 18.0751 23 12 23C5.92487 23 1 18.0751 1 12ZM12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3ZM12 7C9.79086 7 8 8.79086 8 11C8 13.2091 9.79086 15 12 15C14.2091 15 16 13.2091 16 11C16 8.79086 14.2091 7 12 7Z" fill="currentColor"/>
                                            </svg>
                                            <span class="service-photo-upload-text">Ajouter une photo</span>
                                        </div>
                                    </div>
                                    <span class="service-photo-label">Photo de profil (optionnel)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Biographie Field -->
                        <div class="mb-4">
                            <label for="biographie" class="form-label service-label">Biographie <span class="required">*</span></label>
                            <textarea class="form-control service-input" name="biographie" id="biographie" rows="4" placeholder="Parlez-nous de vous, de vos projets et de ce que vous recherchez..." required aria-describedby="biographie_error"></textarea>
                            <span class="field-error" id="biographie_error" role="alert" aria-live="polite"></span>
                        </div>

                        <!-- Genre Field -->
                        <div class="mb-4">
                            <label for="genre" class="form-label service-label">Genre <span class="required">*</span></label>
                            <select class="form-select service-input" name="genre" id="genre" required aria-describedby="genre_error">
                                <option value="">Sélectionnez votre genre</option>
                                <option value="homme">Homme</option>
                                <option value="femme">Femme</option>
                                <option value="autre">Autre</option>
                            </select>
                            <span class="field-error" id="genre_error" role="alert" aria-live="polite"></span>
                        </div>

                        <!-- Music Genres Section -->
                        <div class="mb-4">
                            <label for="genre-pop" class="form-label service-label mb-3">Genres musicaux préférés <span class="required">*</span></label>
                            
                            <!-- Music Genres Options Grid - Bootstrap -->
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="music_genres[]" id="genre-pop" value="Pop">
                                        <label for="genre-pop" class="form-check-label service-filter-label">
                                            <span class="service-filter-star">☆</span>
                                            Pop
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="music_genres[]" id="genre-rock" value="Rock">
                                        <label for="genre-rock" class="form-check-label service-filter-label">
                                            <span class="service-filter-star">☆</span>
                                            Rock
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="music_genres[]" id="genre-electro" value="Electro / House / Techno">
                                        <label for="genre-electro" class="form-check-label service-filter-label">
                                            <span class="service-filter-star">☆</span>
                                            Electro / House / Techno
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="music_genres[]" id="genre-classique" value="Classique">
                                        <label for="genre-classique" class="form-check-label service-filter-label">
                                            <span class="service-filter-star">☆</span>
                                            Classique
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="music_genres[]" id="genre-jazz" value="Jazz">
                                        <label for="genre-jazz" class="form-check-label service-filter-label">
                                            <span class="service-filter-star">☆</span>
                                            Jazz
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="music_genres[]" id="genre-metal" value="Metal">
                                        <label for="genre-metal" class="form-check-label service-filter-label">
                                            <span class="service-filter-star">☆</span>
                                            Metal
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="music_genres[]" id="genre-reggaeton" value="Reggaeton / Afro">
                                        <label for="genre-reggaeton" class="form-check-label service-filter-label">
                                            <span class="service-filter-star">☆</span>
                                            Reggaeton / Afro
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="music_genres[]" id="genre-autre" value="Autre">
                                        <label for="genre-autre" class="form-check-label service-filter-label">
                                            <span class="service-filter-star">☆</span>
                                            Autre
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <span class="field-error mt-2" id="music_genres_error" role="alert" aria-live="polite"></span>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-navigation">
                            <a href="<?php echo home_url('/signup-step2'); ?>" class="btn btn-previous">Précédent</a>
                            <button type="submit" name="seeking_submit" class="btn btn-submit" id="seeking-submit-btn">
                                <span class="btn-text">Terminer l'inscription</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('seeking-form');
    const submitBtn = document.getElementById('seeking-submit-btn');
    
    // Validation de l'étape 3
    function validateStep3() {
        let isValid = true;
        const errors = {};
        
        // Biographie
        const biographie = document.getElementById('biographie').value.trim();
        if (!biographie) {
            errors.biographie = 'La biographie est requise.';
            isValid = false;
        }
        
        // Genre
        const genre = document.getElementById('genre').value;
        if (!genre) {
            errors.genre = 'Le genre est requis.';
            isValid = false;
        }
        
        // Genres musicaux
        const musicGenres = form.querySelectorAll('input[name="music_genres[]"]:checked');
        if (musicGenres.length === 0) {
            errors.music_genres = 'Veuillez sélectionner au moins un genre musical.';
            isValid = false;
        }
        
        // Afficher les erreurs
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(field + '_error');
            const inputElement = document.getElementById(field);
            if (errorElement) {
                errorElement.textContent = errors[field] || '';
                if (errors[field]) {
                    if (inputElement) {
                        inputElement.classList.add('is-invalid');
                        inputElement.setAttribute('aria-invalid', 'true');
                    }
                } else {
                    if (inputElement) {
                        inputElement.classList.remove('is-invalid');
                        inputElement.setAttribute('aria-invalid', 'false');
                    }
                }
            }
        });
        
        // Pour les genres musicaux, mettre en évidence visuellement
        if (errors.music_genres) {
            const genreCheckboxes = form.querySelectorAll('input[name="music_genres[]"]');
            genreCheckboxes.forEach(cb => {
                cb.classList.add('is-invalid');
            });
        } else {
            const genreCheckboxes = form.querySelectorAll('input[name="music_genres[]"]');
            genreCheckboxes.forEach(cb => {
                cb.classList.remove('is-invalid');
            });
        }
        
        return isValid;
    }
    
    // Validation en temps réel
    ['biographie', 'genre'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', validateStep3);
        }
    });
    
    // Validation des genres musicaux
    const genreCheckboxes = form.querySelectorAll('input[name="music_genres[]"]');
    genreCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const genres = form.querySelectorAll('input[name="music_genres[]"]:checked');
            const errorElement = document.getElementById('music_genres_error');
            if (genres.length > 0) {
                errorElement.textContent = '';
                genreCheckboxes.forEach(c => c.classList.remove('is-invalid'));
            } else {
                validateStep3();
            }
        });
    });
    
    // Gestion de la soumission
    form.addEventListener('submit', function(e) {
        if (!validateStep3()) {
            e.preventDefault();
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
            }
        }
    });
});
</script>

<?php get_footer(); ?>

