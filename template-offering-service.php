<?php

/**
 * Template Name: Offering Service Template
 */

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
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="service-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('offering_action', 'offering_nonce'); ?>

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
                            <textarea class="form-control service-input" name="biographie" id="biographie" rows="4" placeholder="Value" required></textarea>
                            <div class="error-message field-error" id="biographie-error" style="display: none;">Ce champ est requis.</div>
                        </div>

                        <!-- Genre Field -->
                        <div class="mb-4">
                            <label for="genre" class="form-label service-label">Genre <span class="required">*</span></label>
                            <input type="text" class="form-control service-input" name="genre" id="genre" placeholder="Value" required>
                            <div class="error-message field-error" id="genre-error" style="display: none;">Ce champ est requis.</div>
                        </div>

                        <!-- Filtres Section -->
                        <div class="mb-4">
                            <label class="form-label service-label">Filtres <span class="required">*</span></label>
                            
                            <!-- Filter Options Grid -->
                            <div class="service-filters-grid">
                                <div class="service-filter-item">
                                    <input type="checkbox" class="service-filter-checkbox" name="filters[]" id="filter-beatmaker" value="beatmaker" aria-label="Beatmaker / Producteur">
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

                        <!-- Error message for filters -->
                        <div class="error-message field-error" id="filters-error" style="display: none;">Veuillez sélectionner au moins un service.</div>

                        <!-- Submit Button -->
                        <div class="service-submit-section">
                            <button type="submit" name="offering_submit" class="btn service-submit-btn" id="offering-submit-btn">
                                <span class="btn-text">suivant</span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
