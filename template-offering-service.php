<?php

/**
 * Template Name: Offering Service Template
 */

// Process form submission BEFORE header
if (isset($_POST['offering_submit'])) {
    // Ensure session is started
    if (!session_id()) {
        session_start();
    }
    
    // Verify nonce
    if (!isset($_POST['offering_nonce'])) {
        wp_die('Erreur: Nonce manquant. Veuillez réessayer.');
    }
    
    if (!wp_verify_nonce($_POST['offering_nonce'], 'offering_action')) {
        wp_safe_redirect(home_url('/offering-service?registration=error&message=nonce_failed'));
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
    
    if (empty($_POST['filters']) || !is_array($_POST['filters']) || count($_POST['filters']) === 0) {
        $errors[] = 'filters';
    }
    
    // If validation errors, redirect back with error message
    if (!empty($errors)) {
        $error_params = 'registration=error&fields=' . implode(',', $errors);
        wp_safe_redirect(home_url('/offering-service?' . $error_params));
        exit;
    }

    $reg_data = $_SESSION['registration_data'];
    
    // Check if user already exists
    if (username_exists($reg_data['user_login']) || email_exists($reg_data['user_email'])) {
        wp_safe_redirect(home_url('/offering-service?registration=error&message=user_already_exists'));
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
        wp_safe_redirect(home_url('/offering-service?registration=error&message=user_creation_failed'));
        exit;
    }

    // Handle profile photo upload
    $photo_result = handle_profile_photo_upload($user_id);
    if ($photo_result === 'size_error') {
        wp_safe_redirect(home_url('/offering-service?registration=error&message=photo_too_large'));
        exit;
    } elseif ($photo_result === 'type_error') {
        wp_safe_redirect(home_url('/offering-service?registration=error&message=photo_invalid_type'));
        exit;
    }

    // Save form data
    if (isset($_POST['biographie'])) {
        update_user_meta($user_id, 'biographie', sanitize_textarea_field($_POST['biographie']));
    }
    if (isset($_POST['genre'])) {
        update_user_meta($user_id, 'genre', sanitize_text_field($_POST['genre']));
    }
    
    if (isset($_POST['filters']) && is_array($_POST['filters'])) {
        update_user_meta($user_id, 'filters', array_map('sanitize_text_field', $_POST['filters']));
    }

    // Handle productions if provided
    if (isset($_POST['productions']) && is_array($_POST['productions'])) {
        $productions = array();
        
        foreach ($_POST['productions'] as $index => $production_data) {
            // Skip if title is empty (optional field)
            if (empty($production_data['title']) || trim($production_data['title']) === '') {
                continue;
            }
            
            $production = array(
                'title' => sanitize_text_field($production_data['title']),
                'genre' => isset($production_data['genre']) ? sanitize_text_field($production_data['genre']) : '',
                'description' => isset($production_data['description']) ? sanitize_textarea_field($production_data['description']) : '',
                'rating' => 5,
                'audio_file' => '',
                'video_file' => '',
                'soundcloud_url' => '',
                'spotify_url' => '',
                'youtube_url' => ''
            );
            
            // Handle audio file upload
            if (isset($_FILES['production_audio_' . $index]) && !empty($_FILES['production_audio_' . $index]['name'])) {
                $audio_result = handle_production_media_upload($user_id, 'production_audio_' . $index, array('audio'));
                if ($audio_result && $audio_result !== 'size_error' && $audio_result !== 'type_error') {
                    $production['audio_file'] = $audio_result;
                }
            }
            
            // Handle video file upload
            if (isset($_FILES['production_video_' . $index]) && !empty($_FILES['production_video_' . $index]['name'])) {
                $video_result = handle_production_media_upload($user_id, 'production_video_' . $index, array('video'));
                if ($video_result && $video_result !== 'size_error' && $video_result !== 'type_error') {
                    $production['video_file'] = $video_result;
                }
            }
            
            // Validate and save external links
            if (isset($production_data['soundcloud_url']) && !empty($production_data['soundcloud_url'])) {
                if (validate_platform_url($production_data['soundcloud_url'], 'soundcloud')) {
                    $production['soundcloud_url'] = esc_url_raw($production_data['soundcloud_url']);
                }
            }
            
            if (isset($production_data['spotify_url']) && !empty($production_data['spotify_url'])) {
                if (validate_platform_url($production_data['spotify_url'], 'spotify')) {
                    $production['spotify_url'] = esc_url_raw($production_data['spotify_url']);
                }
            }
            
            if (isset($production_data['youtube_url']) && !empty($production_data['youtube_url'])) {
                if (validate_platform_url($production_data['youtube_url'], 'youtube')) {
                    $production['youtube_url'] = esc_url_raw($production_data['youtube_url']);
                }
            }
            
            // Add production using the function
            add_user_production($user_id, $production);
        }
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

// Check if user has registration data in session
check_registration_session();

// Show error/success messages
display_registration_error_message();
?>

<div class="service-container">
    <div class="container-fluid">
        <div class="row g-0">
            <!-- Left Panel: Quote Section (2/5 width) -->
            <div class="col-md-5 service-quote-panel">
                <div class="service-quote-content">
                    <p class="service-quote-text">mettre son talent au service de ceux qui créent</p>
                </div>
            </div>

            <!-- Right Panel: Form Section (3/5 width) -->
            <div class="col-md-7 service-form-panel">
                <div class="service-form-wrapper">
                    <!-- Stepper -->
                    <div class="registration-stepper" role="progressbar" aria-valuenow="3" aria-valuemin="1" aria-valuemax="4" aria-label="Progression de l'inscription">
                        <div class="stepper-step completed">
                            <div class="stepper-step-number">1</div>
                            <div class="stepper-step-label">Identité</div>
                        </div>
                        <div class="stepper-step completed">
                            <div class="stepper-step-number">2</div>
                            <div class="stepper-step-label">Contact</div>
                        </div>
                        <div class="stepper-step active" data-step="3">
                            <div class="stepper-step-number">3</div>
                            <div class="stepper-step-label">Profil</div>
                        </div>
                        <div class="stepper-step" data-step="4">
                            <div class="stepper-step-number">4</div>
                            <div class="stepper-step-label">Productions</div>
                        </div>
                    </div>

                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="service-form" id="offering-form" enctype="multipart/form-data" novalidate>
                        <?php wp_nonce_field('offering_action', 'offering_nonce'); ?>
                        
                        <!-- Step 3: Profil -->
                        <div class="form-step active" id="step-3" data-step="3">
                            <h2 class="form-step-title">Profil</h2>
                            <p class="form-step-description">Présentez-vous et votre activité</p>

                            <!-- Photo Upload Section -->
                        <div class="service-photo-section mb-4">
                            <div class="service-photo-upload-wrapper">
                                <input type="file" name="profile_photo" id="profile_photo" accept="image/*" class="service-photo-input" style="display: none;">
                                <label for="profile_photo" class="service-photo-label-wrapper">
                                    <div class="service-photo-preview" id="photo-preview">
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
                            <textarea class="form-control service-input" name="biographie" id="biographie" rows="4" placeholder="Parlez-nous de vous, de votre parcours et de vos compétences..." required aria-describedby="biographie_error"></textarea>
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

                        <!-- Filtres Section -->
                        <div class="mb-4">
                            <label for="filter-beatmaker" class="form-label service-label mb-3">Filtres <span class="required">*</span></label>
                            
                            <!-- Filter Options Grid - Bootstrap -->
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="filters[]" id="filter-beatmaker" value="beatmaker">
                                        <label for="filter-beatmaker" class="form-check-label service-filter-label service-filter-button">
                                            <span class="service-filter-button-content">
                                                <span class="service-filter-icon">★</span>
                                                <span class="service-filter-text">Beatmaker / Producteur</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="filters[]" id="filter-chanteur" value="chanteur">
                                        <label for="filter-chanteur" class="form-check-label service-filter-label service-filter-button">
                                            <span class="service-filter-button-content">
                                                <span class="service-filter-icon">★</span>
                                                <span class="service-filter-text">Chanteur / Chanteuse</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="filters[]" id="filter-organisateur" value="organisateur">
                                        <label for="filter-organisateur" class="form-check-label service-filter-label service-filter-button">
                                            <span class="service-filter-button-content">
                                                <span class="service-filter-icon">★</span>
                                                <span class="service-filter-text">Organisateur d'événements</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="filters[]" id="filter-dj" value="dj">
                                        <label for="filter-dj" class="form-check-label service-filter-label service-filter-button">
                                            <span class="service-filter-button-content">
                                                <span class="service-filter-icon">★</span>
                                                <span class="service-filter-text">DJ</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="filters[]" id="filter-ingenieur" value="ingenieur">
                                        <label for="filter-ingenieur" class="form-check-label service-filter-label service-filter-button">
                                            <span class="service-filter-button-content">
                                                <span class="service-filter-icon">★</span>
                                                <span class="service-filter-text">Ingénieur son</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="filters[]" id="filter-compositeur" value="compositeur">
                                        <label for="filter-compositeur" class="form-check-label service-filter-label service-filter-button">
                                            <span class="service-filter-button-content">
                                                <span class="service-filter-icon">★</span>
                                                <span class="service-filter-text">Compositeur</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check service-filter-checkbox-wrapper">
                                        <input type="checkbox" class="form-check-input service-filter-checkbox" name="filters[]" id="filter-musicien" value="musicien">
                                        <label for="filter-musicien" class="form-check-label service-filter-label service-filter-button">
                                            <span class="service-filter-button-content">
                                                <span class="service-filter-icon">★</span>
                                                <span class="service-filter-text">Musicien</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <span class="field-error mt-2" id="filters_error" role="alert" aria-live="polite"></span>
                        </div>

                        <!-- Navigation Step 3 -->
                        <div class="form-navigation mt-4">
                            <a href="<?php echo home_url('/signup-step2'); ?>" class="btn btn-previous">Précédent</a>
                            <button type="button" class="btn btn-next" id="btn-next-step-3">Suivant</button>
                        </div>
                        </div>

                        <!-- Step 4: Productions (Optional) -->
                        <div class="form-step" id="step-4" data-step="4">
                            <h2 class="form-step-title">Productions</h2>
                            <p class="form-step-description">Ajoutez vos productions pour montrer votre travail (optionnel)</p>

                            <!-- Productions Section -->
                            <div class="mb-4 productions-registration-section">
                            <label for="production_title_0" class="form-label service-label mb-3">Productions (optionnel)</label>
                            <p class="form-text text-muted mb-3">Ajoutez une production pour montrer votre travail. Vous pourrez en ajouter d'autres plus tard.</p>
                            
                            <div class="production-form-item" data-production-index="0">
                                <div class="mb-3">
                                    <label for="production_title_0" class="form-label">Titre de la production</label>
                                    <input type="text" class="form-control service-input" name="productions[0][title]" id="production_title_0" autocomplete="off" placeholder="Ex: Mon premier single">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="production_genre_0" class="form-label">Genre</label>
                                    <input type="text" class="form-control service-input" name="productions[0][genre]" id="production_genre_0" autocomplete="off" placeholder="Ex: Indie Rock, Rock Alternatif">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="production_description_0" class="form-label">Description</label>
                                    <textarea class="form-control service-input" name="productions[0][description]" id="production_description_0" rows="3" autocomplete="off" placeholder="Décrivez votre production..."></textarea>
                                </div>
                                
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="production_audio_0" class="form-label">Fichier audio (MP3, WAV, OGG)</label>
                                        <input type="file" class="form-control service-input" name="production_audio_0" id="production_audio_0" accept="audio/*">
                                        <small class="form-text text-muted">Max 50MB</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="production_video_0" class="form-label">Fichier vidéo (MP4, WebM)</label>
                                        <input type="file" class="form-control service-input" name="production_video_0" id="production_video_0" accept="video/*">
                                        <small class="form-text text-muted">Max 50MB</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="production_soundcloud_0" class="form-label">Lien SoundCloud (optionnel)</label>
                                    <input type="url" class="form-control service-input" name="productions[0][soundcloud_url]" id="production_soundcloud_0" autocomplete="url" placeholder="https://soundcloud.com/...">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="production_spotify_0" class="form-label">Lien Spotify (optionnel)</label>
                                    <input type="url" class="form-control service-input" name="productions[0][spotify_url]" id="production_spotify_0" autocomplete="url" placeholder="https://open.spotify.com/...">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="production_youtube_0" class="form-label">Lien YouTube (optionnel)</label>
                                    <input type="url" class="form-control service-input" name="productions[0][youtube_url]" id="production_youtube_0" autocomplete="url" placeholder="https://www.youtube.com/...">
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="add-another-production" style="display: none;">
                                + Ajouter une autre production
                            </button>
                            </div>

                            <!-- Navigation Step 4 -->
                            <div class="form-navigation mt-4">
                                <button type="button" class="btn btn-previous" id="btn-previous-step-4">Précédent</button>
                                <button type="button" class="btn btn-skip" id="btn-skip-productions">Passer cette étape</button>
                                <button type="submit" name="offering_submit" class="btn btn-submit" id="offering-submit-btn">
                                    <span class="btn-text">Terminer l'inscription</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-live="polite"></span>
                                </button>
                            </div>
                        </div>
                        
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            let productionIndex = 1;
                            const addProductionBtn = document.getElementById('add-another-production');
                            const productionsSection = document.querySelector('.productions-registration-section');
                            
                            if (addProductionBtn && productionsSection) {
                                // Check if first production has content
                                const firstTitle = document.getElementById('production_title_0');
                                if (firstTitle && firstTitle.value.trim() !== '') {
                                    addProductionBtn.style.display = 'block';
                                }
                                
                                // Show button when first production is filled
                                if (firstTitle) {
                                    firstTitle.addEventListener('input', function() {
                                        if (this.value.trim() !== '') {
                                            addProductionBtn.style.display = 'block';
                                        }
                                    });
                                }
                                
                                addProductionBtn.addEventListener('click', function() {
                                    const newProduction = document.querySelector('.production-form-item').cloneNode(true);
                                    newProduction.setAttribute('data-production-index', productionIndex);
                                    
                                    // Update all IDs and names
                                    const inputs = newProduction.querySelectorAll('input, textarea, select, label');
                                    inputs.forEach(function(input) {
                                        if (input.id) {
                                            input.id = input.id.replace('_0', '_' + productionIndex);
                                        }
                                        if (input.name) {
                                            input.name = input.name.replace('[0]', '[' + productionIndex + ']');
                                        }
                                        if (input.htmlFor) {
                                            input.htmlFor = input.htmlFor.replace('_0', '_' + productionIndex);
                                        }
                                    });
                                    
                                    // Clear values
                                    newProduction.querySelectorAll('input[type="text"], input[type="url"], textarea').forEach(function(input) {
                                        input.value = '';
                                    });
                                    newProduction.querySelectorAll('input[type="file"]').forEach(function(input) {
                                        input.value = '';
                                    });
                                    
                                    // Insert before button
                                    addProductionBtn.parentNode.insertBefore(newProduction, addProductionBtn);
                                    productionIndex++;
                                });
                            }
                        });
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('offering-form');
    const submitBtn = document.getElementById('offering-submit-btn');
    
    // Navigation entre les étapes
    let currentStep = 3;
    const steps = {
        3: document.getElementById('step-3'),
        4: document.getElementById('step-4')
    };
    
    function showStep(stepNumber) {
        // Cacher toutes les étapes
        Object.keys(steps).forEach(step => {
            if (steps[step]) {
                steps[step].classList.remove('active');
            }
        });
        
        // Afficher l'étape actuelle
        if (steps[stepNumber]) {
            steps[stepNumber].classList.add('active');
        }
        
        // Mettre à jour le stepper
        const stepperSteps = document.querySelectorAll('.stepper-step[data-step]');
        stepperSteps.forEach(step => {
            const stepNum = parseInt(step.getAttribute('data-step'));
            step.classList.remove('active', 'completed');
            
            if (stepNum < stepNumber) {
                step.classList.add('completed');
            } else if (stepNum === stepNumber) {
                step.classList.add('active');
            }
        });
        
        // Mettre à jour aria-valuenow
        const stepper = document.querySelector('.registration-stepper');
        if (stepper) {
            stepper.setAttribute('aria-valuenow', stepNumber);
        }
        
        currentStep = stepNumber;
        
        // Scroll en haut du formulaire
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    
    // Bouton Suivant - Étape 3
    const btnNextStep3 = document.getElementById('btn-next-step-3');
    if (btnNextStep3) {
        btnNextStep3.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateStep3()) {
                showStep(4);
            }
        });
    }
    
    // Bouton Précédent - Étape 4
    const btnPreviousStep4 = document.getElementById('btn-previous-step-4');
    if (btnPreviousStep4) {
        btnPreviousStep4.addEventListener('click', function(e) {
            e.preventDefault();
            showStep(3);
        });
    }
    
    // Bouton Passer - Étape 4 (skip)
    const btnSkipProductions = document.getElementById('btn-skip-productions');
    if (btnSkipProductions) {
        btnSkipProductions.addEventListener('click', function(e) {
            e.preventDefault();
            // Soumettre le formulaire sans valider les productions
            form.submit();
        });
    }
    
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
        
        // Filtres
        const filters = form.querySelectorAll('input[name="filters[]"]:checked');
        if (filters.length === 0) {
            errors.filters = 'Veuillez sélectionner au moins un service que vous offrez.';
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
        
        // Pour les filtres, mettre en évidence visuellement
        if (errors.filters) {
            const filterCheckboxes = form.querySelectorAll('input[name="filters[]"]');
            filterCheckboxes.forEach(cb => {
                cb.classList.add('is-invalid');
            });
        } else {
            const filterCheckboxes = form.querySelectorAll('input[name="filters[]"]');
            filterCheckboxes.forEach(cb => {
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
    
    // Validation des filtres
    const filterCheckboxes = form.querySelectorAll('input[name="filters[]"]');
    filterCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const filters = form.querySelectorAll('input[name="filters[]"]:checked');
            const errorElement = document.getElementById('filters_error');
            if (filters.length > 0) {
                errorElement.textContent = '';
                filterCheckboxes.forEach(c => c.classList.remove('is-invalid'));
            } else {
                validateStep3();
            }
        });
    });
    
    // Gestion de la soumission
    form.addEventListener('submit', function(e) {
        // Si on est sur l'étape 3, valider avant de soumettre
        if (currentStep === 3) {
            if (!validateStep3()) {
                e.preventDefault();
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.focus();
                }
                return false;
            }
            // Si l'étape 3 est valide et qu'on est encore dessus, aller à l'étape 4
            e.preventDefault();
            showStep(4);
            return false;
        }
        
        // Si on est sur l'étape 4, valider l'étape 3 d'abord (obligatoire)
        if (currentStep === 4) {
            if (!validateStep3()) {
                e.preventDefault();
                showStep(3);
                const firstError = form.querySelector('.is-invalid');
                if (firstError) {
                    firstError.focus();
                }
                return false;
            }
            // L'étape 4 est optionnelle, on peut soumettre
            return true;
        }
    });
});
</script>

<?php get_footer(); ?>
