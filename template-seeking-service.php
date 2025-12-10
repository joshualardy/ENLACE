<?php

/**
 * Template Name: Seeking Service Template
 */

get_header();

// Check if user has registration data in session and correct service type
check_registration_session('seek');

// Show error/success messages
display_registration_error_message();
?>

<div class="seeking-service-container">
    <div class="container-fluid">
        <div class="row g-0 min-vh-100">
            <!-- Left Panel: Quote Section -->
            <div class="col-lg-5 seeking-quote-panel d-flex align-items-end justify-content-center">
                <div class="seeking-quote-content">
                    <p class="seeking-quote-text">La bonne rencontre peut changer tout un projet</p>
                </div>
            </div>

            <!-- Right Panel: Form Section -->
            <div class="col-lg-7 seeking-form-panel d-flex align-items-center justify-content-center">
                <div class="seeking-form-wrapper">
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="seeking-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('seeking_action', 'seeking_nonce'); ?>

                        <!-- Photo Upload Section -->
                        <div class="seeking-photo-section mb-4 text-center">
                            <input type="file" name="profile_photo" id="seeking_profile_photo" accept="image/*" class="d-none">
                            <label for="seeking_profile_photo" class="seeking-photo-label">
                                <div class="seeking-photo-preview" id="seeking-photo-preview">
                                    <div class="seeking-photo-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="seeking-photo-upload-icon">
                                            <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" fill="currentColor"/>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M1 12C1 5.92487 5.92487 1 12 1C18.0751 1 23 5.92487 23 12C23 18.0751 18.0751 23 12 23C5.92487 23 1 18.0751 1 12ZM12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3ZM12 7C9.79086 7 8 8.79086 8 11C8 13.2091 9.79086 15 12 15C14.2091 15 16 13.2091 16 11C16 8.79086 14.2091 7 12 7Z" fill="currentColor"/>
                                        </svg>
                                        <span class="seeking-photo-upload-text">Ajouter une photo</span>
                                    </div>
                                </div>
                                <span class="seeking-photo-text">Photo de profil (optionnel)</span>
                            </label>
                        </div>

                        <!-- Biographie Field -->
                        <div class="mb-4">
                            <label for="biographie" class="form-label seeking-label">Biographie <span class="required">*</span></label>
                            <textarea class="form-control seeking-input" name="biographie" id="biographie" rows="4" placeholder="Value" required></textarea>
                            <div class="error-message field-error" id="biographie-error" style="display: none;">Ce champ est requis.</div>
                        </div>

                        <!-- Genre Field -->
                        <div class="mb-4">
                            <label for="genre" class="form-label seeking-label">Genre <span class="required">*</span></label>
                            <input type="text" class="form-control seeking-input" name="genre" id="genre" placeholder="Value" required>
                            <div class="error-message field-error" id="genre-error" style="display: none;">Ce champ est requis.</div>
                        </div>

                        <!-- Filtres Section -->
                        <div class="mb-4">
                            <label class="form-label seeking-label">Filtres <span class="required">*</span></label>
                            
                            <!-- Music Genres Grid -->
                            <div class="seeking-filters-grid">
                                <div class="seeking-filter-item">
                                    <input type="checkbox" class="form-check-input seeking-filter-checkbox" name="music_genres[]" id="genre-pop" value="Pop" aria-label="Pop">
                                    <label for="genre-pop" class="form-check-label seeking-filter-label">
                                        <span class="seeking-filter-star">★</span>
                                        Pop
                                    </label>
                                </div>
                                <div class="seeking-filter-item">
                                    <input type="checkbox" class="form-check-input seeking-filter-checkbox" name="music_genres[]" id="genre-rock" value="Rock">
                                    <label for="genre-rock" class="form-check-label seeking-filter-label">
                                        <span class="seeking-filter-star">★</span>
                                        Rock
                                    </label>
                                </div>
                                <div class="seeking-filter-item">
                                    <input type="checkbox" class="form-check-input seeking-filter-checkbox" name="music_genres[]" id="genre-electro" value="Electro / House / Techno">
                                    <label for="genre-electro" class="form-check-label seeking-filter-label">
                                        <span class="seeking-filter-star">★</span>
                                        Électro / House / Techno
                                    </label>
                                </div>
                                <div class="seeking-filter-item">
                                    <input type="checkbox" class="form-check-input seeking-filter-checkbox" name="music_genres[]" id="genre-classique" value="Classique">
                                    <label for="genre-classique" class="form-check-label seeking-filter-label">
                                        <span class="seeking-filter-star">★</span>
                                        Classique
                                    </label>
                                </div>
                                <div class="seeking-filter-item">
                                    <input type="checkbox" class="form-check-input seeking-filter-checkbox" name="music_genres[]" id="genre-jazz" value="Jazz">
                                    <label for="genre-jazz" class="form-check-label seeking-filter-label">
                                        <span class="seeking-filter-star">★</span>
                                        Jazz
                                    </label>
                                </div>
                                <div class="seeking-filter-item">
                                    <input type="checkbox" class="form-check-input seeking-filter-checkbox" name="music_genres[]" id="genre-metal" value="Metal">
                                    <label for="genre-metal" class="form-check-label seeking-filter-label">
                                        <span class="seeking-filter-star">★</span>
                                        Metal
                                    </label>
                                </div>
                                <div class="seeking-filter-item">
                                    <input type="checkbox" class="form-check-input seeking-filter-checkbox" name="music_genres[]" id="genre-reggaeton" value="Reggaeton / Afro">
                                    <label for="genre-reggaeton" class="form-check-label seeking-filter-label">
                                        <span class="seeking-filter-star">★</span>
                                        Reggaeton / Afro
                                    </label>
                                </div>
                                <div class="seeking-filter-item">
                                    <input type="checkbox" class="form-check-input seeking-filter-checkbox" name="music_genres[]" id="genre-autre" value="Autre">
                                    <label for="genre-autre" class="form-check-label seeking-filter-label">
                                        <span class="seeking-filter-star">★</span>
                                        Autre
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Error message for music genres -->
                        <div class="error-message field-error" id="music_genres-error" style="display: none;">Veuillez sélectionner au moins un genre musical.</div>

                        <!-- Submit Button -->
                        <div class="seeking-submit-section text-center">
                            <button type="submit" name="seeking_submit" class="btn seeking-submit-btn" id="seeking-submit-btn">
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

