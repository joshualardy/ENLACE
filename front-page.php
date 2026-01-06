<?php
get_header();
?>

<!-- SECTION 1: HERO -->
<section class="hero-section">
    <div class="hero-background"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-tagline-wrapper">
            <p class="hero-tagline">Là où la musique rencontre ceux qui la font</p>
            <?php if (!is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(home_url('/signup')); ?>" class="hero-inscription-btn">Rejoindre ENLACE</a>
            <?php endif; ?>
        </div>
        <h1 class="hero-title" id="hero-title-enlace">ENLACE</h1>
    </div>
</section>

<!-- SECTION 2: MANIFESTE -->
<section class="manifeste-section bg-textured-light">
    <div class="container">
        <div class="manifeste-content">
            <h2 class="manifeste-title">LÀ OÙ TOUT COMMENCE</h2>
            <div class="manifeste-text">
                <p>ENLACE, c'est l'endroit où les idées prennent vie,<br>
                où les projets trouvent leur rythme,<br>
                où les opportunités se créent naturellement.</p>
                <p>Un espace pensé pour celles et ceux qui bougent,<br>
                qui créent, qui cherchent la connexion juste.</p>
                <p>Ici, tout est fluide. Tout est à portée de main.</p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: CARROUSEL -->
<section class="carousel-section bg-textured-light">
    <h3 class="carousel-title">L'UNIVERS ENLACE</h3>
    <div class="row">
        <div class="row__inner">
            <?php
            // Images du carrousel - à remplacer par vos images
            $carousel_images = array(
                'guitare.jpg',
                'micro.jpg',
                'piano.jpg',
                'guitare.jpg',
                'micro.jpg',
                'piano.jpg',
                'guitare.jpg',
                'micro.jpg',
                'piano.jpg',
                'guitare.jpg',
                'micro.jpg',
                'piano.jpg',
                'guitare.jpg',
                'micro.jpg',
                'piano.jpg',
                'guitare.jpg',
                'micro.jpg',
                'piano.jpg',
                'guitare.jpg'
            );
            
            foreach ($carousel_images as $image) :
                $image_url = get_template_directory_uri() . '/assets/images/' . $image;
            ?>
            <div class="tile">
                <div class="tile__media">
                    <img class="tile__img" src="<?php echo esc_url($image_url); ?>" alt="" />
                </div>
                <div class="tile__details">
                    <div class="tile__title">
                        Production
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- SECTION 4: POUR QUI -->
<section class="pour-qui-section bg-textured-blue">
    <div class="container">
        <div class="pour-qui-content">
            <h2 class="pour-qui-title">POUR CEUX QUI FONT LA MUSIQUE</h2>
            <p class="pour-qui-subtitle">Artistes, producteurs, musiciens, créatifs, managers, techniciens... ENLACE rassemble celles et ceux qui font bouger la scène musicale.</p>
            <div class="pour-qui-grid">
                <div class="pour-qui-card bg-textured-blue-subtle">
                    <div class="pour-qui-card-content">
                        <h3 class="pour-qui-card-title">Artistes & Performers</h3>
                    </div>
                </div>
                <div class="pour-qui-card bg-textured-blue-subtle">
                    <div class="pour-qui-card-content">
                        <h3 class="pour-qui-card-title">Producteurs & Beatmakers</h3>
                    </div>
                </div>
                <div class="pour-qui-card bg-textured-blue-subtle">
                    <div class="pour-qui-card-content">
                        <h3 class="pour-qui-card-title">Musiciens & Instrumentistes</h3>
                    </div>
                </div>
                <div class="pour-qui-card bg-textured-blue-subtle">
                    <div class="pour-qui-card-content">
                        <h3 class="pour-qui-card-title">Créatifs & Techniciens</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 5: L'ESSENTIEL DES CONNEXIONS -->
<section class="essentiel-section bg-textured-light">
    <div class="container">
        <div class="essentiel-content">
            <h2 class="essentiel-title">L'ESSENTIEL DES CONNEXIONS</h2>
            <div class="essentiel-text">
                <p>Sur ENLACE, tu rencontres celles et ceux qui partagent<br>
                ta vision, ton énergie, ton ambition.</p>
                <p>Artistes, beatmakers, managers, créatifs, techniciens...<br>
                Tous ceux qui font bouger la scène sont ici.</p>
                <p>Un espace fluide où les bonnes connexions se font naturellement,<br>
                où les opportunités prennent forme,<br>
                où tes projets trouvent l'élan qu'ils méritent.</p>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 6: COMMENT ÇA MARCHE -->
<section class="comment-section bg-textured-blue">
    <div class="container">
        <div class="comment-content">
            <h2 class="comment-title">SIMPLE. FLUIDE. EFFICACE.</h2>
            <div class="comment-steps">
                <div class="comment-step">
                    <div class="comment-step-number">1</div>
                    <h3 class="comment-step-title">Crée ton profil</h3>
                    <p class="comment-step-text">Présente-toi, partage ton univers,<br>définis ce que tu cherches.</p>
                </div>
                <div class="comment-step">
                    <div class="comment-step-number">2</div>
                    <h3 class="comment-step-title">Explore & Connecte</h3>
                    <p class="comment-step-text">Découvre les profils qui résonnent,<br>réponds aux annonces, crée les tiens.</p>
                </div>
                <div class="comment-step">
                    <div class="comment-step-number">3</div>
                    <h3 class="comment-step-title">Collabore & Crée</h3>
                    <p class="comment-step-text">Échange, construis, lance tes projets<br>avec les bonnes personnes.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 7: CTA FINAL -->
<?php if (!is_user_logged_in()) : ?>
<section class="cta-final-section bg-textured-light">
    <div class="container">
        <div class="cta-final-content">
            <h2 class="cta-final-title">PRÊT À REJOINDRE LA SCÈNE ?</h2>
            <p class="cta-final-text">Rejoins ENLACE et connecte-toi à celles et ceux qui font bouger la musique.</p>
            <a href="<?php echo esc_url(home_url('/signup')); ?>" class="cta-final-btn">Rejoindre ENLACE</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
get_footer();
?>