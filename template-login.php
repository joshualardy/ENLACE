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

<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="login-form-wrapper">
                    <div class="login-logo text-center mb-4">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Logos/logo_blanc.svg'); ?>" alt="ENLACE Logo" class="login-logo-img">
                    </div>
                    <?php
                    if (isset($_GET['login']) && $_GET['login'] == 'failed') {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                        echo '<strong>Erreur :</strong> Nom d\'utilisateur ou mot de passe invalide.';
                        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                        echo '</div>';
                    }
                    if (isset($_GET['login']) && $_GET['login'] == 'empty') {
                        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
                        echo '<strong>Attention :</strong> Veuillez remplir tous les champs.';
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

                        <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="login-form">
                            <?php wp_nonce_field('login_action', 'login_nonce'); ?>

                            <div class="mb-3">
                                <label for="user_login" class="form-label">Nom d'utilisateur ou E-mail</label>
                                <input type="text" class="form-control" name="log" id="user_login" placeholder="nom d'utilisateur ou email" required>
                            </div>

                            <div class="mb-3">
                                <label for="user_pass" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" name="pwd" id="user_pass" placeholder="mot de passe" required>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="rememberme" id="rememberme" value="forever">
                                <label class="form-check-label" for="rememberme">
                                    Se souvenir de moi
                                </label>
                            </div>

                            <button type="submit" name="login_submit" class="btn btn-login w-100">Connexion</button>
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