<?php

/**
 * Template Name: Login Template
 */

// Éviter les redirections en boucle
if (is_user_logged_in() && !isset($_POST['login_submit'])) {
    wp_safe_redirect(home_url('/userprofil'));
    exit;
}

get_header();
?>

<!-- Vanta.TRUNK background pour toute la page -->
<div id="vanta-background" class="vanta-fullpage-bg"></div>

<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-form-wrapper">
                    <div class="login-logo text-center mb-4">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Logos/logo_blanc.svg'); ?>" alt="ENLACE Logo" class="login-logo-img">
                    </div>
                    <?php
                    // Display alert messages using unified helper function
                    if (isset($_GET['login']) && $_GET['login'] == 'failed') {
                        echo display_alert_message('error', 'Nom d\'utilisateur ou mot de passe invalide.', 'Erreur :');
                    }
                    if (isset($_GET['login']) && $_GET['login'] == 'empty') {
                        echo display_alert_message('warning', 'Veuillez remplir tous les champs.', 'Attention :');
                    }
                    if (is_user_logged_in()) {
                        $logout_link = '<a href="' . esc_url(wp_logout_url(home_url())) . '" class="alert-link">Se déconnecter</a>';
                        echo display_alert_message('info', 'Vous êtes déjà connecté. ' . $logout_link);
                    } else {
                    ?>

                        <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="login-form">
                            <?php wp_nonce_field('login_action', 'login_nonce'); ?>

                            <div class="mb-3">
                                <label for="user_login" class="form-label">Nom d'utilisateur ou E-mail</label>
                                <input type="text" class="form-control" name="log" id="user_login" autocomplete="username" placeholder="nom d'utilisateur ou email" required>
                            </div>

                            <div class="mb-3">
                                <label for="user_pass" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" name="pwd" id="user_pass" autocomplete="current-password" placeholder="mot de passe" required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="rememberme" id="rememberme" value="forever">
                                <label class="form-check-label" for="rememberme">
                                    Se souvenir de moi
                                </label>
                            </div>

                            <button type="submit" name="login_submit" class="btn btn-login w-100" aria-label="Se connecter">Connexion</button>
                        </form>

                        <p class="register-link text-center mt-3">Vous n'avez pas de compte ? <a href="<?php echo esc_url(home_url('/signup')); ?>">S'inscrire</a></p>

                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
?>