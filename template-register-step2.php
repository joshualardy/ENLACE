<?php

/**
 * Template Name: Register Step 2 Template
 */

// Process form submission BEFORE header
if (isset($_POST['register_step2_submit'])) {
    if (!session_id()) {
        session_start();
    }
    
    // Verify nonce
    if (!isset($_POST['register_step2_nonce']) || !wp_verify_nonce($_POST['register_step2_nonce'], 'register_step2_action')) {
        wp_safe_redirect(home_url('/signup?registration=error&message=nonce_failed'));
        exit;
    }
    
    // Check session
    if (!isset($_SESSION['registration_data']) || !isset($_SESSION['registration_data']['step1_completed'])) {
        wp_safe_redirect(home_url('/signup?registration=error&message=session_expired'));
        exit;
    }
    
    // Validate required fields
    $errors = array();
    
    if (empty($_POST['user_login']) || trim($_POST['user_login']) === '') {
        $errors[] = 'user_login';
    } elseif (username_exists(sanitize_user($_POST['user_login']))) {
        $errors[] = 'user_login_exists';
    }
    
    if (empty($_POST['phone']) || trim($_POST['phone']) === '') {
        $errors[] = 'phone';
    }
    
    if (empty($_POST['ville']) || trim($_POST['ville']) === '') {
        $errors[] = 'ville';
    }
    
    if (empty($_POST['service_type']) || !in_array($_POST['service_type'], array('offer', 'seek'))) {
        $errors[] = 'service_type';
    }
    
    // If validation errors, save POST data to session and redirect back with error message
    if (!empty($errors)) {
        // Sauvegarder les valeurs POST en session pour les réafficher
        $_SESSION['registration_step2_post_data'] = array(
            'user_login' => isset($_POST['user_login']) ? sanitize_text_field($_POST['user_login']) : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'ville' => isset($_POST['ville']) ? sanitize_text_field($_POST['ville']) : '',
            'service_type' => isset($_POST['service_type']) ? sanitize_text_field($_POST['service_type']) : ''
        );
        $error_params = 'registration=error&fields=' . implode(',', $errors);
        wp_safe_redirect(home_url('/signup-step2?' . $error_params));
        exit;
    }
    
    // Save step 2 data to session
    $_SESSION['registration_data']['user_login'] = sanitize_user($_POST['user_login']);
    $_SESSION['registration_data']['phone'] = sanitize_text_field($_POST['phone']);
    $_SESSION['registration_data']['ville'] = sanitize_text_field($_POST['ville']);
    $_SESSION['registration_data']['service_type'] = sanitize_text_field($_POST['service_type']);
    $_SESSION['registration_data']['step2_completed'] = true;
    
    // Nettoyer les données POST temporaires si elles existent
    if (isset($_SESSION['registration_step2_post_data'])) {
        unset($_SESSION['registration_step2_post_data']);
    }
    
    // Redirect to step 3 based on service type
    if ($_POST['service_type'] === 'offer') {
        wp_safe_redirect(home_url('/offering-service'));
    } else {
        wp_safe_redirect(home_url('/seeking-service'));
    }
    exit;
}

get_header();

// Check if user has registration data in session
if (!session_id()) {
    session_start();
}

if (!isset($_SESSION['registration_data']) || !isset($_SESSION['registration_data']['step1_completed'])) {
    wp_safe_redirect(home_url('/signup'));
    exit;
}

// Show error messages
display_registration_error_message();

// Récupérer les erreurs depuis l'URL
$error_fields = array();
$error_message = '';
if (isset($_GET['registration']) && $_GET['registration'] == 'error') {
    if (isset($_GET['fields'])) {
        $error_fields = explode(',', $_GET['fields']);
    }
    if (isset($_GET['message'])) {
        $error_messages = get_registration_error_messages();
        $error_message = isset($error_messages[$_GET['message']]) ? $error_messages[$_GET['message']] : '';
    }
}
?>

<div class="register-container">
    <div class="container">
        <div class="register-split-layout">
        <!-- Left Panel: Quote -->
        <div class="register-quote-panel">
            <div class="register-quote">
                <p class="quote-line-1">La musique relie ce que les mots effleurent :</p>
                <p class="quote-line-2">elle crée des ponts là où tout semblait séparé</p>
            </div>
        </div>

        <!-- Right Panel: Form -->
        <div class="register-form-panel">
            <div class="register-form-wrapper">
                <?php
                // Afficher les messages d'erreur
                if (isset($_GET['registration']) && $_GET['registration'] == 'error') {
                    // Use unified alert function
                    if (!empty($error_message)) {
                        echo display_alert_message('error', esc_html($error_message), 'Erreur :');
                    } elseif (!empty($error_fields)) {
                        echo display_alert_message('warning', 'Veuillez corriger les erreurs dans les champs indiqués ci-dessous.', 'Attention :');
                    } else {
                        echo display_alert_message('error', 'L\'inscription a échoué. Veuillez réessayer.', 'Erreur :');
                    }
                }
                ?>
                <!-- Stepper -->
                <div class="registration-stepper" role="progressbar" aria-valuenow="2" aria-valuemin="1" aria-valuemax="3" aria-label="Progression de l'inscription">
                    <div class="stepper-step completed">
                        <div class="stepper-step-number">1</div>
                        <div class="stepper-step-label">Identité</div>
                    </div>
                    <div class="stepper-step active">
                        <div class="stepper-step-number">2</div>
                        <div class="stepper-step-label">Contact</div>
                    </div>
                    <div class="stepper-step">
                        <div class="stepper-step-number">3</div>
                        <div class="stepper-step-label">Profil</div>
                    </div>
                </div>

                <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="register-form" id="registration-step2-form" novalidate>
                    <?php wp_nonce_field('register_step2_action', 'register_step2_nonce'); ?>
                    <input type="hidden" name="registration_step" value="2">
                    <input type="hidden" name="register_step2_submit" id="register_step2_submit_hidden" value="">

                    <!-- Étape 2: Contact et localisation -->
                    <div class="form-step active" id="step-2" data-step="2">
                        <h2 class="form-step-title">Contact</h2>
                        <p class="form-step-description">Comment vous contacter et où vous trouvez ?</p>

                        <?php
                        // Messages d'erreur spécifiques par champ
                        $field_errors = array(
                            'user_login' => 'Le nom d\'utilisateur est requis.',
                            'user_login_exists' => 'Ce nom d\'utilisateur est déjà pris. Veuillez en choisir un autre.',
                            'phone' => 'Le numéro de téléphone est requis.',
                            'ville' => 'La ville est requise.',
                            'service_type' => 'Veuillez choisir si vous offrez ou cherchez un service.'
                        );
                        
                        // Récupérer les valeurs depuis POST ou session (après erreur)
                        if (!session_id()) {
                            session_start();
                        }
                        $user_login_value = '';
                        $phone_value = '';
                        $ville_value = '';
                        $service_type_value = '';
                        
                        if (isset($_SESSION['registration_step2_post_data'])) {
                            $user_login_value = esc_attr($_SESSION['registration_step2_post_data']['user_login']);
                            $phone_value = esc_attr($_SESSION['registration_step2_post_data']['phone']);
                            $ville_value = esc_attr($_SESSION['registration_step2_post_data']['ville']);
                            $service_type_value = esc_attr($_SESSION['registration_step2_post_data']['service_type']);
                            // Nettoyer la session après utilisation
                            unset($_SESSION['registration_step2_post_data']);
                        } elseif (isset($_POST['user_login'])) {
                            $user_login_value = esc_attr($_POST['user_login']);
                            $phone_value = esc_attr($_POST['phone']);
                            $ville_value = esc_attr($_POST['ville']);
                            $service_type_value = esc_attr($_POST['service_type']);
                        }
                        ?>

                        <div class="mb-3">
                            <label for="user_login" class="form-label">Nom d'utilisateur <span class="required">*</span></label>
                            <input type="text" class="form-control <?php echo (in_array('user_login', $error_fields) || in_array('user_login_exists', $error_fields)) ? 'is-invalid' : ''; ?>" name="user_login" id="user_login" autocomplete="username" value="<?php echo $user_login_value; ?>" placeholder="Choisissez un nom d'utilisateur unique" required aria-describedby="user_login_error user_login_help" aria-invalid="<?php echo (in_array('user_login', $error_fields) || in_array('user_login_exists', $error_fields)) ? 'true' : 'false'; ?>">
                            <small id="user_login_help" class="form-text text-muted">Ce nom sera visible sur votre profil</small>
                            <span class="field-error <?php echo (in_array('user_login', $error_fields) || in_array('user_login_exists', $error_fields)) ? 'has-error' : ''; ?>" id="user_login_error" role="alert" aria-live="polite" style="<?php echo (in_array('user_login', $error_fields) || in_array('user_login_exists', $error_fields)) ? 'display: block;' : 'display: none;'; ?>"><?php 
                                if (in_array('user_login_exists', $error_fields)) {
                                    echo esc_html($field_errors['user_login_exists']);
                                } elseif (in_array('user_login', $error_fields)) {
                                    echo esc_html($field_errors['user_login']);
                                }
                            ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">N° de téléphone <span class="required">*</span></label>
                            <input type="tel" class="form-control <?php echo in_array('phone', $error_fields) ? 'is-invalid' : ''; ?>" name="phone" id="phone" autocomplete="tel" value="<?php echo $phone_value; ?>" placeholder="+32 XXX XX XX XX" required aria-describedby="phone_error" aria-invalid="<?php echo in_array('phone', $error_fields) ? 'true' : 'false'; ?>">
                            <span class="field-error <?php echo in_array('phone', $error_fields) ? 'has-error' : ''; ?>" id="phone_error" role="alert" aria-live="polite" style="<?php echo in_array('phone', $error_fields) ? 'display: block;' : 'display: none;'; ?>"><?php echo in_array('phone', $error_fields) ? esc_html($field_errors['phone']) : ''; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="ville" class="form-label">Ville <span class="required">*</span></label>
                            <input type="text" class="form-control <?php echo in_array('ville', $error_fields) ? 'is-invalid' : ''; ?>" name="ville" id="ville" autocomplete="address-level2" value="<?php echo $ville_value; ?>" list="belgian-cities" placeholder="Sélectionnez votre ville" required aria-describedby="ville_error" aria-invalid="<?php echo in_array('ville', $error_fields) ? 'true' : 'false'; ?>">
                            <datalist id="belgian-cities">
                                <option value="Bruxelles (1000)">Bruxelles (1000)</option>
                                <option value="Anvers (2000)">Anvers (2000)</option>
                                <option value="Gand (9000)">Gand (9000)</option>
                                <option value="Charleroi (6000)">Charleroi (6000)</option>
                                <option value="Liège (4000)">Liège (4000)</option>
                                <option value="Bruges (8000)">Bruges (8000)</option>
                                <option value="Namur (5000)">Namur (5000)</option>
                                <option value="Louvain (3000)">Louvain (3000)</option>
                                <option value="Mons (7000)">Mons (7000)</option>
                                <option value="Malines (2800)">Malines (2800)</option>
                                <option value="Aalst (9300)">Aalst (9300)</option>
                                <option value="La Louvière (7100)">La Louvière (7100)</option>
                                <option value="Courtrai (8500)">Courtrai (8500)</option>
                                <option value="Hasselt (3500)">Hasselt (3500)</option>
                                <option value="Ostende (8400)">Ostende (8400)</option>
                                <option value="Sint-Niklaas (9100)">Sint-Niklaas (9100)</option>
                                <option value="Tournai (7500)">Tournai (7500)</option>
                                <option value="Genk (3600)">Genk (3600)</option>
                                <option value="Seraing (4100)">Seraing (4100)</option>
                                <option value="Roeselare (8800)">Roeselare (8800)</option>
                                <option value="Verviers (4800)">Verviers (4800)</option>
                                <option value="Mouscron (7700)">Mouscron (7700)</option>
                                <option value="Beveren (9120)">Beveren (9120)</option>
                                <option value="Dendermonde (9200)">Dendermonde (9200)</option>
                                <option value="Turnhout (2300)">Turnhout (2300)</option>
                                <option value="Dilbeek (1700)">Dilbeek (1700)</option>
                                <option value="Heist-op-den-Berg (2220)">Heist-op-den-Berg (2220)</option>
                                <option value="Sint-Truiden (3800)">Sint-Truiden (3800)</option>
                                <option value="Lokeren (9160)">Lokeren (9160)</option>
                                <option value="Geel (2440)">Geel (2440)</option>
                                <option value="Bilzen (3740)">Bilzen (3740)</option>
                                <option value="Lommel (3920)">Lommel (3920)</option>
                                <option value="Ieper (8900)">Ieper (8900)</option>
                                <option value="Waregem (8790)">Waregem (8790)</option>
                                <option value="Ninove (9400)">Ninove (9400)</option>
                                <option value="Châtelet (6200)">Châtelet (6200)</option>
                                <option value="Lier (2500)">Lier (2500)</option>
                                <option value="Schoten (2900)">Schoten (2900)</option>
                                <option value="Evergem (9940)">Evergem (9940)</option>
                                <option value="Houthalen-Helchteren (3530)">Houthalen-Helchteren (3530)</option>
                                <option value="Tongeren (3700)">Tongeren (3700)</option>
                                <option value="Wevelgem (8560)">Wevelgem (8560)</option>
                                <option value="Beringen (3580)">Beringen (3580)</option>
                                <option value="Tielt (8700)">Tielt (8700)</option>
                                <option value="Diest (3290)">Diest (3290)</option>
                                <option value="Herentals (2200)">Herentals (2200)</option>
                                <option value="Eeklo (9900)">Eeklo (9900)</option>
                                <option value="Aarschot (3200)">Aarschot (3200)</option>
                                <option value="Temse (9140)">Temse (9140)</option>
                                <option value="Zottegem (9620)">Zottegem (9620)</option>
                                <option value="Ronse (9600)">Ronse (9600)</option>
                                <option value="Duffel (2570)">Duffel (2570)</option>
                                <option value="Sint-Pieters-Leeuw (1600)">Sint-Pieters-Leeuw (1600)</option>
                                <option value="Mechelen (2800)">Mechelen (2800)</option>
                                <option value="Leuven (3000)">Leuven (3000)</option>
                                <option value="Antwerpen (2000)">Antwerpen (2000)</option>
                                <option value="Gent (9000)">Gent (9000)</option>
                                <option value="Brugge (8000)">Brugge (8000)</option>
                            </datalist>
                            <span class="field-error <?php echo in_array('ville', $error_fields) ? 'has-error' : ''; ?>" id="ville_error" role="alert" aria-live="polite" style="<?php echo in_array('ville', $error_fields) ? 'display: block;' : 'display: none;'; ?>"><?php echo in_array('ville', $error_fields) ? esc_html($field_errors['ville']) : ''; ?></span>
                        </div>

                        <!-- Choix du type de service -->
                        <div class="mb-4">
                            <label for="service_type" class="form-label">Que souhaitez-vous faire sur ENLACE ? <span class="required">*</span></label>
                            <div class="service-type-selection <?php echo in_array('service_type', $error_fields) ? 'has-error' : ''; ?>" role="radiogroup" aria-label="Type de service">
                                <button type="button" class="service-type-btn <?php echo ($service_type_value === 'offer') ? 'selected' : ''; ?>" data-service-type="offer" id="service-offer-btn" aria-pressed="<?php echo ($service_type_value === 'offer') ? 'true' : 'false'; ?>">
                                    J'offre mon service
                                </button>
                                <button type="button" class="service-type-btn <?php echo ($service_type_value === 'seek') ? 'selected' : ''; ?>" data-service-type="seek" id="service-seek-btn" aria-pressed="<?php echo ($service_type_value === 'seek') ? 'true' : 'false'; ?>">
                                    Je cherche un service
                                </button>
                            </div>
                            <input type="hidden" name="service_type" id="service_type" value="<?php echo $service_type_value; ?>" required>
                            <span class="field-error <?php echo in_array('service_type', $error_fields) ? 'has-error' : ''; ?>" id="service_type_error" role="alert" aria-live="polite" style="<?php echo in_array('service_type', $error_fields) ? 'display: block;' : 'display: none;'; ?>"><?php echo in_array('service_type', $error_fields) ? esc_html($field_errors['service_type']) : ''; ?></span>
                        </div>

                        <div class="form-navigation">
                            <a href="<?php echo home_url('/signup'); ?>" class="btn btn-previous">Précédent</a>
                            <button type="button" class="btn btn-next" id="step2-next" name="register_step2_submit">Suivant</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registration-step2-form');
    const step2Next = document.getElementById('step2-next');
    const serviceTypeInput = document.getElementById('service_type');
    const serviceOfferBtn = document.getElementById('service-offer-btn');
    const serviceSeekBtn = document.getElementById('service-seek-btn');
    
    // Afficher les erreurs au chargement si elles existent
    <?php if (!empty($error_fields)): ?>
    const errorFields = <?php echo json_encode($error_fields); ?>;
    errorFields.forEach(function(field) {
        const inputElement = document.getElementById(field);
        const errorElement = document.getElementById(field + '_error');
        if (inputElement && errorElement) {
            inputElement.classList.add('is-invalid');
            inputElement.setAttribute('aria-invalid', 'true');
            errorElement.classList.add('has-error');
            errorElement.style.display = 'block';
        }
    });
    // Pour service_type, mettre en évidence les boutons
    if (errorFields.includes('service_type')) {
        serviceOfferBtn.classList.add('is-invalid');
        serviceSeekBtn.classList.add('is-invalid');
    }
    // Focus sur le premier champ en erreur
    const firstErrorField = form.querySelector('.is-invalid');
    if (firstErrorField) {
        firstErrorField.focus();
    } else if (errorFields.includes('service_type')) {
        serviceOfferBtn.focus();
    }
    <?php endif; ?>
    
    // Gestion du choix du type de service
    [serviceOfferBtn, serviceSeekBtn].forEach(btn => {
        btn.addEventListener('click', function() {
            // Retirer la sélection des autres boutons
            [serviceOfferBtn, serviceSeekBtn].forEach(b => {
                b.classList.remove('selected', 'is-invalid');
                b.setAttribute('aria-pressed', 'false');
            });
            
            // Sélectionner le bouton cliqué
            this.classList.add('selected');
            this.setAttribute('aria-pressed', 'true');
            serviceTypeInput.value = this.getAttribute('data-service-type');
            
            // Retirer l'erreur si elle existe
            const errorElement = document.getElementById('service_type_error');
            errorElement.textContent = '';
            errorElement.classList.remove('has-error');
            errorElement.style.display = 'none';
        });
    });
    
    // Vérification de disponibilité du nom d'utilisateur
    let usernameCheckTimeout;
    const usernameInput = document.getElementById('user_login');
    usernameInput.addEventListener('input', function() {
        clearTimeout(usernameCheckTimeout);
        const username = this.value.trim();
        
        if (username.length < 3) {
            return;
        }
        
        usernameCheckTimeout = setTimeout(function() {
            // Vérification AJAX de disponibilité
            const formData = new FormData();
            formData.append('action', 'check_username');
            formData.append('username', username);
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const errorElement = document.getElementById('user_login_error');
                if (data.exists) {
                    errorElement.textContent = 'Ce nom d\'utilisateur est déjà pris. Veuillez en choisir un autre.';
                    usernameInput.classList.add('is-invalid');
                    usernameInput.setAttribute('aria-invalid', 'true');
                } else {
                    errorElement.textContent = '';
                    usernameInput.classList.remove('is-invalid');
                    usernameInput.setAttribute('aria-invalid', 'false');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la vérification:', error);
            });
        }, 500);
    });
    
    // Validation de l'étape 2
    function validateStep2() {
        let isValid = true;
        const errors = {};
        
        // Nom d'utilisateur
        const username = document.getElementById('user_login').value.trim();
        if (!username) {
            errors.user_login = 'Le nom d\'utilisateur est requis.';
            isValid = false;
        } else if (username.length < 3) {
            errors.user_login = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
            isValid = false;
        }
        
        // Téléphone
        const phone = document.getElementById('phone').value.trim();
        if (!phone) {
            errors.phone = 'Le numéro de téléphone est requis.';
            isValid = false;
        }
        
        // Ville
        const ville = document.getElementById('ville').value.trim();
        if (!ville) {
            errors.ville = 'La ville est requise.';
            isValid = false;
        }
        
        // Type de service
        const serviceType = serviceTypeInput.value;
        if (!serviceType) {
            errors.service_type = 'Veuillez choisir si vous offrez ou cherchez un service.';
            isValid = false;
        }
        
        // Afficher les erreurs
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(field + '_error');
            const inputElement = document.getElementById(field);
            if (errorElement) {
                if (errors[field]) {
                    errorElement.textContent = errors[field];
                    errorElement.classList.add('has-error');
                    errorElement.style.display = 'block';
                    if (inputElement) {
                        inputElement.classList.add('is-invalid');
                        inputElement.setAttribute('aria-invalid', 'true');
                    }
                    // Pour service_type, mettre en évidence les boutons
                    if (field === 'service_type') {
                        serviceOfferBtn.classList.add('is-invalid');
                        serviceSeekBtn.classList.add('is-invalid');
                    }
                } else {
                    errorElement.textContent = '';
                    errorElement.classList.remove('has-error');
                    errorElement.style.display = 'none';
                    if (inputElement) {
                        inputElement.classList.remove('is-invalid');
                        inputElement.setAttribute('aria-invalid', 'false');
                    }
                    if (field === 'service_type') {
                        serviceOfferBtn.classList.remove('is-invalid');
                        serviceSeekBtn.classList.remove('is-invalid');
                    }
                }
            }
        });
        
        return isValid;
    }
    
    // Validation en temps réel
    ['user_login', 'phone', 'ville'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', validateStep2);
        }
    });
    
    // Gestion du bouton Suivant
    step2Next.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (validateStep2()) {
            // Définir la valeur de l'input hidden pour déclencher la soumission côté serveur
            const submitInput = document.getElementById('register_step2_submit_hidden');
            if (submitInput) {
                submitInput.value = '1';
            }
            
            // Soumettre le formulaire
            form.submit();
        } else {
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
            } else if (!serviceTypeInput.value) {
                serviceOfferBtn.focus();
                // Mettre en évidence les boutons
                serviceOfferBtn.classList.add('is-invalid');
                serviceSeekBtn.classList.add('is-invalid');
            }
        }
    });
});
</script>

<?php
get_footer();
?>
