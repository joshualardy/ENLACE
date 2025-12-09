<?php

/**
 * Template Name: Offering Service Template
 */

// Start session if not already started (must be before get_header())
if (!session_id()) {
    session_start();
}

get_header();

// Check if user has registration data in session
if (!isset($_SESSION['registration_data'])) {
    wp_redirect(home_url('/signup'));
    exit;
}

// Show error message if registration failed
if (isset($_GET['registration']) && $_GET['registration'] == 'error') {
    echo '<div class="error-message" style="position: fixed; top: 100px; left: 50%; transform: translateX(-50%); z-index: 9999; background: rgba(248, 215, 218, 0.95); color: #721c24; padding: 1rem; border-radius: 8px; margin: 1rem;">L\'inscription a échoué. Veuillez réessayer.</div>';
}
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
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="service-form">
                        <?php wp_nonce_field('offering_action', 'offering_nonce'); ?>

                        <!-- Photo Upload Section -->
                        <div class="service-photo-section mb-4">
                            <div class="service-photo-icon">
                                <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="40" cy="40" r="40" fill="#1A2332"/>
                                    <circle cx="40" cy="32" r="12" fill="#fff" opacity="0.3"/>
                                    <path d="M25 55C25 50 30 45 40 45C50 45 55 50 55 55V60H25V55Z" fill="#fff" opacity="0.3"/>
                                </svg>
                            </div>
                            <label class="service-photo-label">Photo</label>
                        </div>

                        <!-- Biographie Field -->
                        <div class="mb-4">
                            <label for="biographie" class="form-label service-label">Biographie</label>
                            <textarea class="form-control service-input" name="biographie" id="biographie" rows="4" placeholder="Value"></textarea>
                        </div>

                        <!-- Genre Field -->
                        <div class="mb-4">
                            <label for="genre" class="form-label service-label">Genre</label>
                            <input type="text" class="form-control service-input" name="genre" id="genre" placeholder="Value">
                        </div>

                        <!-- Filtres Section -->
                        <div class="mb-4">
                            <label for="filtres" class="form-label service-label">Filtres</label>
                            <input type="text" class="form-control service-input mb-3" name="filtres" id="filtres" placeholder="Value">
                            
                            <!-- Filter Options Grid -->
                            <div class="service-filters-grid">
                                <div class="service-filter-item">
                                    <input type="checkbox" class="service-filter-checkbox" name="filters[]" id="filter-beatmaker" value="beatmaker">
                                    <label for="filter-beatmaker" class="service-filter-label">
                                        <span class="service-filter-star">☆</span>
                                        Beatmaker / Producteur
                                    </label>
                                </div>
                                <div class="service-filter-item">
                                    <input type="checkbox" class="service-filter-checkbox" name="filters[]" id="filter-chanteur" value="chanteur">
                                    <label for="filter-chanteur" class="service-filter-label">
                                        <span class="service-filter-star">☆</span>
                                        Chanteur / Chanteuse
                                    </label>
                                </div>
                                <div class="service-filter-item">
                                    <input type="checkbox" class="service-filter-checkbox" name="filters[]" id="filter-organisateur" value="organisateur">
                                    <label for="filter-organisateur" class="service-filter-label">
                                        <span class="service-filter-star">☆</span>
                                        Organisateur d'événements
                                    </label>
                                </div>
                                <div class="service-filter-item">
                                    <input type="checkbox" class="service-filter-checkbox" name="filters[]" id="filter-dj" value="dj">
                                    <label for="filter-dj" class="service-filter-label">
                                        <span class="service-filter-star">☆</span>
                                        DJ
                                    </label>
                                </div>
                                <div class="service-filter-item">
                                    <input type="checkbox" class="service-filter-checkbox" name="filters[]" id="filter-ingenieur" value="ingenieur">
                                    <label for="filter-ingenieur" class="service-filter-label">
                                        <span class="service-filter-star">☆</span>
                                        Ingénieur son
                                    </label>
                                </div>
                                <div class="service-filter-item">
                                    <input type="checkbox" class="service-filter-checkbox" name="filters[]" id="filter-compositeur" value="compositeur">
                                    <label for="filter-compositeur" class="service-filter-label">
                                        <span class="service-filter-star">☆</span>
                                        Compositeur
                                    </label>
                                </div>
                                <div class="service-filter-item">
                                    <input type="checkbox" class="service-filter-checkbox" name="filters[]" id="filter-musicien" value="musicien">
                                    <label for="filter-musicien" class="service-filter-label">
                                        <span class="service-filter-star">☆</span>
                                        Musicien
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="service-submit-section">
                            <button type="submit" name="offering_submit" class="btn service-submit-btn">suivant</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
