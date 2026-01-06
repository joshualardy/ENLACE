<?php

/**
 * Template Name: Register Template
 */
get_header();
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
                    
                    if (!empty($error_message)) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                        echo '<strong>Erreur :</strong> ' . esc_html($error_message);
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                        echo '</div>';
                    } elseif (!empty($error_fields)) {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
                        echo '<strong>Attention :</strong> Veuillez corriger les erreurs dans les champs indiqués ci-dessous.';
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                        echo '<strong>Erreur :</strong> L\'inscription a échoué. Veuillez réessayer.';
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                        echo '</div>';
                    }
                }
                if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                    echo '<strong>Succès !</strong> Inscription réussie ! Vous pouvez maintenant vous connecter.';
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                }
                if (is_user_logged_in()) {
                    echo '<div class="alert alert-info alert-dismissible fade show" role="alert">';
                    echo 'Vous êtes déjà connecté. <a href="' . wp_logout_url(home_url()) . '" class="alert-link">Se déconnecter</a>';
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                } else {
                ?>

                <!-- Stepper -->
                <div class="registration-stepper" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3" aria-label="Progression de l'inscription">
                    <div class="stepper-step active">
                        <div class="stepper-step-number">1</div>
                        <div class="stepper-step-label">Identité</div>
                    </div>
                    <div class="stepper-step">
                        <div class="stepper-step-number">2</div>
                        <div class="stepper-step-label">Contact</div>
                    </div>
                    <div class="stepper-step">
                        <div class="stepper-step-number">3</div>
                        <div class="stepper-step-label">Profil</div>
                    </div>
                </div>

                <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="register-form" id="registration-form" novalidate>
                    <?php wp_nonce_field('register_step1_action', 'register_step1_nonce'); ?>
                    <input type="hidden" name="registration_step" value="1">

                    <!-- Étape 1: Identité et compte -->
                    <div class="form-step active" id="step-1" data-step="1">
                        <h2 class="form-step-title">Identité</h2>
                        <p class="form-step-description">Créez vos identifiants de connexion</p>

                        <?php
                        // Messages d'erreur spécifiques par champ
                        $field_errors = array(
                            'first_name' => 'Le prénom est requis.',
                            'last_name' => 'Le nom est requis.',
                            'user_email' => 'L\'email est requis ou invalide.',
                            'user_email_exists' => 'Cet email est déjà utilisé. Veuillez en choisir un autre.',
                            'user_pass' => 'Le mot de passe est requis et doit contenir au moins 8 caractères.',
                            'user_pass_confirm' => 'Les mots de passe ne correspondent pas.'
                        );
                        ?>
                        <?php
                        // Récupérer les valeurs depuis POST ou session (après erreur)
                        if (!session_id()) {
                            session_start();
                        }
                        $first_name_value = '';
                        $last_name_value = '';
                        $user_email_value = '';
                        
                        if (isset($_SESSION['registration_step1_post_data'])) {
                            $first_name_value = esc_attr($_SESSION['registration_step1_post_data']['first_name']);
                            $last_name_value = esc_attr($_SESSION['registration_step1_post_data']['last_name']);
                            $user_email_value = esc_attr($_SESSION['registration_step1_post_data']['user_email']);
                            // Nettoyer la session après utilisation
                            unset($_SESSION['registration_step1_post_data']);
                        } elseif (isset($_POST['first_name'])) {
                            $first_name_value = esc_attr($_POST['first_name']);
                            $last_name_value = esc_attr($_POST['last_name']);
                            $user_email_value = esc_attr($_POST['user_email']);
                        }
                        ?>
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Prénom <span class="required">*</span></label>
                            <input type="text" class="form-control <?php echo in_array('first_name', $error_fields) ? 'is-invalid' : ''; ?>" name="first_name" id="first_name" autocomplete="given-name" value="<?php echo $first_name_value; ?>" placeholder="Votre prénom" required aria-describedby="first_name_error" aria-invalid="<?php echo in_array('first_name', $error_fields) ? 'true' : 'false'; ?>">
                            <span class="field-error <?php echo in_array('first_name', $error_fields) ? 'has-error' : ''; ?>" id="first_name_error" role="alert" aria-live="polite" style="<?php echo in_array('first_name', $error_fields) ? 'display: block;' : 'display: none;'; ?>"><?php echo in_array('first_name', $error_fields) ? esc_html($field_errors['first_name']) : ''; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Nom <span class="required">*</span></label>
                            <input type="text" class="form-control <?php echo in_array('last_name', $error_fields) ? 'is-invalid' : ''; ?>" name="last_name" id="last_name" autocomplete="family-name" value="<?php echo $last_name_value; ?>" placeholder="Votre nom" required aria-describedby="last_name_error" aria-invalid="<?php echo in_array('last_name', $error_fields) ? 'true' : 'false'; ?>">
                            <span class="field-error <?php echo in_array('last_name', $error_fields) ? 'has-error' : ''; ?>" id="last_name_error" role="alert" aria-live="polite" style="<?php echo in_array('last_name', $error_fields) ? 'display: block;' : 'display: none;'; ?>"><?php echo in_array('last_name', $error_fields) ? esc_html($field_errors['last_name']) : ''; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="user_email" class="form-label">Email <span class="required">*</span></label>
                            <input type="email" class="form-control <?php echo (in_array('user_email', $error_fields) || in_array('user_email_exists', $error_fields)) ? 'is-invalid' : ''; ?>" name="user_email" id="user_email" autocomplete="email" value="<?php echo $user_email_value; ?>" placeholder="votre.email@exemple.com" required aria-describedby="user_email_error" aria-invalid="<?php echo (in_array('user_email', $error_fields) || in_array('user_email_exists', $error_fields)) ? 'true' : 'false'; ?>">
                            <span class="field-error <?php echo (in_array('user_email', $error_fields) || in_array('user_email_exists', $error_fields)) ? 'has-error' : ''; ?>" id="user_email_error" role="alert" aria-live="polite" style="<?php echo (in_array('user_email', $error_fields) || in_array('user_email_exists', $error_fields)) ? 'display: block;' : 'display: none;'; ?>"><?php 
                                if (in_array('user_email_exists', $error_fields)) {
                                    echo esc_html($field_errors['user_email_exists']);
                                } elseif (in_array('user_email', $error_fields)) {
                                    echo esc_html($field_errors['user_email']);
                                }
                            ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="user_pass" class="form-label">Mot de passe <span class="required">*</span></label>
                            <input type="password" class="form-control <?php echo in_array('user_pass', $error_fields) ? 'is-invalid' : ''; ?>" name="user_pass" id="user_pass" autocomplete="new-password" placeholder="••••••••" required aria-describedby="user_pass_error" aria-invalid="<?php echo in_array('user_pass', $error_fields) ? 'true' : 'false'; ?>">
                            <span class="field-error <?php echo in_array('user_pass', $error_fields) ? 'has-error' : ''; ?>" id="user_pass_error" role="alert" aria-live="polite" style="<?php echo in_array('user_pass', $error_fields) ? 'display: block;' : 'display: none;'; ?>"><?php echo in_array('user_pass', $error_fields) ? esc_html($field_errors['user_pass']) : ''; ?></span>
                        </div>

                        <div class="mb-3">
                            <label for="user_pass_confirm" class="form-label">Confirmer le mot de passe <span class="required">*</span></label>
                            <input type="password" class="form-control <?php echo in_array('user_pass_confirm', $error_fields) ? 'is-invalid' : ''; ?>" name="user_pass_confirm" id="user_pass_confirm" autocomplete="new-password" placeholder="••••••••" required aria-describedby="user_pass_confirm_error" aria-invalid="<?php echo in_array('user_pass_confirm', $error_fields) ? 'true' : 'false'; ?>">
                            <span class="field-error <?php echo in_array('user_pass_confirm', $error_fields) ? 'has-error' : ''; ?>" id="user_pass_confirm_error" role="alert" aria-live="polite" style="<?php echo in_array('user_pass_confirm', $error_fields) ? 'display: block;' : 'display: none;'; ?>"><?php echo in_array('user_pass_confirm', $error_fields) ? esc_html($field_errors['user_pass_confirm']) : ''; ?></span>
                        </div>

                        <div class="form-navigation">
                            <div></div>
                            <button type="button" class="btn btn-next" id="step1-next">Suivant</button>
                        </div>
                    </div>
                </form>

                <?php } ?>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registration-form');
    const step1Next = document.getElementById('step1-next');
    
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
    // Focus sur le premier champ en erreur
    const firstErrorField = document.querySelector('.is-invalid');
    if (firstErrorField) {
        firstErrorField.focus();
    }
    <?php endif; ?>
    
    // Validation de l'étape 1
    function validateStep1() {
        let isValid = true;
        const errors = {};
        
        // Prénom
        const firstName = document.getElementById('first_name').value.trim();
        if (!firstName) {
            errors.first_name = 'Le prénom est requis.';
            isValid = false;
        }
        
        // Nom
        const lastName = document.getElementById('last_name').value.trim();
        if (!lastName) {
            errors.last_name = 'Le nom est requis.';
            isValid = false;
        }
        
        // Email
        const email = document.getElementById('user_email').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            errors.user_email = 'L\'email est requis.';
            isValid = false;
        } else if (!emailRegex.test(email)) {
            errors.user_email = 'L\'email n\'est pas valide.';
            isValid = false;
        }
        
        // Mot de passe
        const password = document.getElementById('user_pass').value;
        if (!password) {
            errors.user_pass = 'Le mot de passe est requis.';
            isValid = false;
        } else if (password.length < 8) {
            errors.user_pass = 'Le mot de passe doit contenir au moins 8 caractères.';
            isValid = false;
        }
        
        // Confirmation mot de passe
        const passwordConfirm = document.getElementById('user_pass_confirm').value;
        if (!passwordConfirm) {
            errors.user_pass_confirm = 'La confirmation du mot de passe est requise.';
            isValid = false;
        } else if (password !== passwordConfirm) {
            errors.user_pass_confirm = 'Les mots de passe ne correspondent pas.';
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
                    inputElement.classList.add('is-invalid');
                    inputElement.setAttribute('aria-invalid', 'true');
                } else {
                    errorElement.textContent = '';
                    errorElement.classList.remove('has-error');
                    errorElement.style.display = 'none';
                    inputElement.classList.remove('is-invalid');
                    inputElement.setAttribute('aria-invalid', 'false');
                }
            }
        });
        
        return isValid;
    }
    
    // Validation en temps réel
    ['first_name', 'last_name', 'user_email', 'user_pass', 'user_pass_confirm'].forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('blur', function() {
                const errorElement = document.getElementById(fieldId + '_error');
                
                if (fieldId === 'user_pass_confirm') {
                    const password = document.getElementById('user_pass').value;
                    const passwordConfirm = field.value;
                    if (passwordConfirm && password !== passwordConfirm) {
                        errorElement.textContent = 'Les mots de passe ne correspondent pas.';
                        errorElement.classList.add('has-error');
                        errorElement.style.display = 'block';
                        field.classList.add('is-invalid');
                        field.setAttribute('aria-invalid', 'true');
                    } else if (passwordConfirm && password === passwordConfirm) {
                        errorElement.textContent = '';
                        errorElement.classList.remove('has-error');
                        errorElement.style.display = 'none';
                        field.classList.remove('is-invalid');
                        field.setAttribute('aria-invalid', 'false');
                    }
                } else if (fieldId === 'user_pass') {
                    const password = field.value;
                    const passwordConfirm = document.getElementById('user_pass_confirm').value;
                    if (password && password.length < 8) {
                        errorElement.textContent = 'Le mot de passe doit contenir au moins 8 caractères.';
                        errorElement.classList.add('has-error');
                        errorElement.style.display = 'block';
                        field.classList.add('is-invalid');
                        field.setAttribute('aria-invalid', 'true');
                    } else if (password && passwordConfirm && password !== passwordConfirm) {
                        const confirmError = document.getElementById('user_pass_confirm_error');
                        confirmError.textContent = 'Les mots de passe ne correspondent pas.';
                        confirmError.classList.add('has-error');
                        confirmError.style.display = 'block';
                        document.getElementById('user_pass_confirm').classList.add('is-invalid');
                    } else if (password && password.length >= 8) {
                        errorElement.textContent = '';
                        errorElement.classList.remove('has-error');
                        errorElement.style.display = 'none';
                        field.classList.remove('is-invalid');
                        field.setAttribute('aria-invalid', 'false');
                    }
                } else {
                    // Validation simple pour les autres champs
                    const value = field.value.trim();
                    if (!value) {
                        errorElement.textContent = 'Ce champ est requis.';
                        errorElement.classList.add('has-error');
                        errorElement.style.display = 'block';
                        field.classList.add('is-invalid');
                        field.setAttribute('aria-invalid', 'true');
                    } else if (fieldId === 'user_email') {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(value)) {
                            errorElement.textContent = 'L\'email n\'est pas valide.';
                            errorElement.classList.add('has-error');
                            errorElement.style.display = 'block';
                            field.classList.add('is-invalid');
                            field.setAttribute('aria-invalid', 'true');
                        } else {
                            errorElement.textContent = '';
                            errorElement.classList.remove('has-error');
                            errorElement.style.display = 'none';
                            field.classList.remove('is-invalid');
                            field.setAttribute('aria-invalid', 'false');
                        }
                    } else {
                        errorElement.textContent = '';
                        errorElement.classList.remove('has-error');
                        errorElement.style.display = 'none';
                        field.classList.remove('is-invalid');
                        field.setAttribute('aria-invalid', 'false');
                    }
                }
            });
        }
    });
    
    // Gestion du bouton Suivant
    step1Next.addEventListener('click', function(e) {
        e.preventDefault();
        if (validateStep1()) {
            // Soumettre le formulaire pour passer à l'étape 2
            form.submit();
        } else {
            // Focus sur le premier champ en erreur
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
            }
        }
    });
});
</script>

<?php
get_footer();
?>
