<?php

/**
 * Template Name: Register Template
 */
get_header();
?>

<div class="register-container">
    <div class="register-form-wrapper">
        <div class="register-logo">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo+ENLACE.svg'); ?>" alt="ENLACE Logo">
        </div>
        <h1>S'inscrire</h1>

        <?php
        if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
            echo '<div class="success-message">Inscription réussie ! Vous pouvez maintenant vous connecter.</div>';
        }
        if (isset($_GET['registration']) && $_GET['registration'] == 'error') {
            echo '<div class="error-message">L\'inscription a échoué. Veuillez réessayer.</div>';
        }
        if (is_user_logged_in()) {
            echo '<div class="success-message">Vous êtes déjà connecté. <a href="' . wp_logout_url(home_url()) . '">Se déconnecter</a></div>';
        } else {
        ?>

            <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="register-form">
                <?php wp_nonce_field('register_action', 'register_nonce'); ?>

                <div class="form-group">
                    <label for="last_name">Nom <span class="required">*</span></label>
                    <input type="text" name="last_name" id="last_name" required>
                </div>

                <div class="form-group">
                    <label for="first_name">Prénom <span class="required">*</span></label>
                    <input type="text" name="first_name" id="first_name" required>
                </div>

                <div class="form-group">
                    <label for="genre">Genre <span class="required">*</span></label>
                    <select name="genre" id="genre" required>
                        <option value="">Sélectionnez un genre</option>
                        <option value="homme">Homme</option>
                        <option value="femme">Femme</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="user_login">Nom d'utilisateur <span class="required">*</span></label>
                    <input type="text" name="user_login" id="user_login" required>
                </div>

                <div class="form-group">
                    <label for="user_email">Adresse mail <span class="required">*</span></label>
                    <input type="email" name="user_email" id="user_email" required>
                </div>

                <div class="form-group">
                    <label for="user_pass">Mot de passe <span class="required">*</span></label>
                    <input type="password" name="user_pass" id="user_pass" required>
                </div>

                <div class="form-group">
                    <label for="user_pass_confirm">Confirmer mot de passe <span class="required">*</span></label>
                    <input type="password" name="user_pass_confirm" id="user_pass_confirm" required>
                </div>

                <div class="form-group">
                    <label for="phone">N° de tel <span class="required">*</span></label>
                    <input type="tel" name="phone" id="phone" required>
                </div>

                <div class="form-group">
                    <label for="ville">Ville <span class="required">*</span></label>
                    <input type="text" name="ville" id="ville" list="belgian-cities" autocomplete="off" required>
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
                </div>

                <button type="submit" name="register_submit" class="submit-btn">S'inscrire</button>
            </form>

            <p class="login-link">Vous avez déjà un compte ? <a href="<?php echo esc_url(home_url('/login')); ?>">Se connecter</a></p>

        <?php } ?>
    </div>
</div>

<?php
get_footer();
?>