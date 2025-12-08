<?php
get_header();
?>

<section class="hero-section">
    <div class="hero-background"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-tagline-wrapper">
            <p class="hero-tagline">Connecte ton énergie, crée tes opportunités</p>
            <a href="<?php echo esc_url(home_url('/signup')); ?>" class="hero-inscription-btn">Inscription</a>
        </div>
        <h1 class="hero-title" id="hero-title-enlace">ENLACE</h1>
    </div>
</section>

<section class="carousel-section">
    <div class="container-fluid">
        <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="carousel-panel"></div>
                </div>
                <div class="carousel-item">
                    <div class="carousel-panel"></div>
                </div>
                <div class="carousel-item">
                    <div class="carousel-panel"></div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<?php
get_footer();
?>