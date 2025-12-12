<?php
get_header();
?>

<section class="hero-section">
    <div class="hero-background"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-tagline-wrapper">
            <p class="hero-tagline">Connecte ton énergie, crée tes opportunités</p>
            <?php if (!is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(home_url('/signup')); ?>" class="hero-inscription-btn">Inscription</a>
            <?php endif; ?>
        </div>
        <h1 class="hero-title" id="hero-title-enlace">ENLACE</h1>
    </div>
</section>

<section class="carousel-section">
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
                        Top Gear
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="container">
        <div class="row">
            <div class="col-12 about-left">
                <h3 class="about-subtitle">L'ESSENTIEL DES CONNEXIONS</h3>
                <p>Rejoins ENLACE et connecte-toi à celles et ceux qui font bouger la scène : artistes, beatmakers, managers, créatifs...</p>
                <p>Un espace fluide où tu trouves les bonnes connexions, l'énergie juste et les opportunités qui donnent de l'élan à tes projets.</p>
            </div>
            <div class="col-12 about-right">
                <h3 class="about-subtitle">LÀ OÙ TOUT COMMENCE</h3>
                <p>ENLACE, c'est l'endroit où les idées, les projets et les opportunités se rencontrent.</p>
                <p>Un espace simple, urbain, inspiré, pensé pour celles et ceux qui bougent et créent.</p>
                <p>Ici, tout est fluide, clair, et à portée de main.</p>
            </div>
        </div>
</div>
</section>

<?php
get_footer();
?>