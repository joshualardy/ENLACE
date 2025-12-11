<?php
/**
 * Template Name: User Profil
 */

get_header();

// Get user profile data - check if user_id is provided in URL
$profile_user_id = null;
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $profile_user_id = intval($_GET['user_id']);
    // Verify user exists
    $user = get_userdata($profile_user_id);
    if (!$user) {
        // If viewing another user's profile and user doesn't exist, redirect to home
        wp_redirect(home_url());
        exit;
    }
} else {
    // If no user_id provided, check if user is logged in
    if (!is_user_logged_in()) {
        wp_redirect(home_url('/login'));
        exit;
    }
}

// Get user profile data
$profile_data = get_user_profile_data($profile_user_id);

if (!$profile_data) {
    echo '<div class="container"><p>Erreur lors du chargement du profil.</p></div>';
    get_footer();
    exit;
}

// Show success/error messages
$update_message = '';
if (isset($_GET['profile_updated'])) {
    if ($_GET['profile_updated'] === 'success') {
        $update_message = '<div class="profile-update-message success-message">Profil mis à jour avec succès !</div>';
    } elseif ($_GET['profile_updated'] === 'error') {
        $update_message = '<div class="profile-update-message error-message">Erreur lors de la mise à jour du profil.</div>';
    }
}
?>

<div class="user-profil-container">
    <!-- Header Section: Large Image Background with Overlay -->
    <div class="profile-header">
        <div class="profile-header-background" style="<?php echo $profile_data['profile_photo_url'] ? 'background-image: url(' . esc_url($profile_data['profile_photo_url']) . ');' : 'background: linear-gradient(135deg, #1A2332 0%, #2a3a4f 100%);'; ?>">
            <div class="profile-header-overlay"></div>
        </div>
        
        <div class="profile-header-content">
            <div class="container">
                <div class="profile-header-info">
                    <!-- Rating Stars -->
                    <div class="profile-rating">
                        <span class="profile-stars">★★★★★</span>
                    </div>
                    
                    <!-- User Name -->
                    <h1 class="profile-name"><?php echo esc_html($profile_data['full_name']); ?></h1>
                    
                    <!-- Biography/Description -->
                    <?php if (!empty($profile_data['biographie'])) : ?>
                        <p class="profile-description"><?php echo esc_html($profile_data['biographie']); ?></p>
                    <?php endif; ?>
                    
                    <!-- Edit Button -->
                    <div class="profile-edit-btn-wrapper">
                        <button class="btn profile-edit-btn" id="edit-profile-btn">Modifier le profil</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Content Section -->
    <div class="profile-content">
        <div class="container">
            <?php echo $update_message; ?>
            
            <!-- Edit Profile Form (Hidden by default) -->
            <div class="profile-edit-form-wrapper" id="profile-edit-form-wrapper" style="display: none;">
                <div class="profile-edit-form-container">
                    <div class="profile-edit-form-header">
                        <h2 class="profile-edit-form-title">Modifier le profil</h2>
                        <button class="profile-edit-form-close" id="close-edit-form">×</button>
                    </div>
                    
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="profile-edit-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('profile_update_action', 'profile_update_nonce'); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_first_name" class="form-label profile-edit-label">Prénom</label>
                                    <input type="text" class="form-control profile-edit-input" name="first_name" id="edit_first_name" value="<?php echo esc_attr($profile_data['first_name']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_last_name" class="form-label profile-edit-label">Nom</label>
                                    <input type="text" class="form-control profile-edit-input" name="last_name" id="edit_last_name" value="<?php echo esc_attr($profile_data['last_name']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_email" class="form-label profile-edit-label">Email</label>
                                    <input type="email" class="form-control profile-edit-input" name="user_email" id="edit_email" value="<?php echo esc_attr($profile_data['email']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_phone" class="form-label profile-edit-label">Téléphone</label>
                                    <input type="tel" class="form-control profile-edit-input" name="phone" id="edit_phone" value="<?php echo esc_attr($profile_data['phone']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_ville" class="form-label profile-edit-label">Ville</label>
                                    <input type="text" class="form-control profile-edit-input" name="ville" id="edit_ville" value="<?php echo esc_attr($profile_data['ville']); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_genre" class="form-label profile-edit-label">Genre musical</label>
                                    <input type="text" class="form-control profile-edit-input" name="genre" id="edit_genre" value="<?php echo esc_attr($profile_data['genre']); ?>" placeholder="Ex: Rock, Blues, Indie">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_biographie" class="form-label profile-edit-label">Biographie</label>
                            <textarea class="form-control profile-edit-input" name="biographie" id="edit_biographie" rows="4" placeholder="Décrivez-vous..."><?php echo esc_textarea($profile_data['biographie']); ?></textarea>
                        </div>
                        
                        <!-- Photo Upload -->
                        <div class="mb-3">
                            <label class="form-label profile-edit-label">Photo de profil</label>
                            <div class="profile-edit-photo-section">
                                <?php if (!empty($profile_data['profile_photo_url'])) : ?>
                                    <div class="profile-edit-current-photo">
                                        <img src="<?php echo esc_url($profile_data['profile_photo_url']); ?>" alt="Photo actuelle" class="profile-edit-photo-preview">
                                        <p class="profile-edit-photo-note">Photo actuelle</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="profile_photo" id="edit_profile_photo" accept="image/*" class="form-control profile-edit-input">
                                <small class="profile-edit-help-text">Formats acceptés : JPG, PNG, GIF, WEBP (max 5MB)</small>
                            </div>
</div>

                        <!-- Services Filters (only for offer service type) -->
                        <?php if ($profile_data['service_type'] === 'offer') : ?>
                            <div class="mb-3">
                                <label class="form-label profile-edit-label">Services proposés</label>
                                <div class="profile-edit-filters-grid">
<?php
                                    $available_filters = array(
                                        'beatmaker' => 'Beatmaker / Producteur',
                                        'chanteur' => 'Chanteur / Chanteuse',
                                        'organisateur' => 'Organisateur d\'événements',
                                        'dj' => 'DJ',
                                        'ingenieur' => 'Ingénieur son',
                                        'compositeur' => 'Compositeur',
                                        'musicien' => 'Musicien'
                                    );
                                    $current_filters = is_array($profile_data['filters']) ? $profile_data['filters'] : array();
                                    ?>
                                    <?php foreach ($available_filters as $value => $label) : ?>
                                        <div class="profile-edit-filter-item">
                                            <input type="checkbox" class="profile-edit-filter-checkbox" name="filters[]" id="edit_filter_<?php echo esc_attr($value); ?>" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $current_filters)); ?>>
                                            <label for="edit_filter_<?php echo esc_attr($value); ?>" class="profile-edit-filter-label">
                                                <span class="profile-edit-filter-star">☆</span>
                                                <?php echo esc_html($label); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Music Genres (only for seek service type) -->
                        <?php if ($profile_data['service_type'] === 'seek') : ?>
                            <div class="mb-3">
                                <label class="form-label profile-edit-label">Genres musicaux préférés</label>
                                <div class="profile-edit-filters-grid">
<?php
                                    $available_music_genres = array(
                                        'Pop' => 'Pop',
                                        'Rock' => 'Rock',
                                        'Electro / House / Techno' => 'Electro / House / Techno',
                                        'Classique' => 'Classique',
                                        'Jazz' => 'Jazz',
                                        'Metal' => 'Metal',
                                        'Reggaeton / Afro' => 'Reggaeton / Afro',
                                        'Autre' => 'Autre'
                                    );
                                    $current_music_genres = is_array($profile_data['music_genres']) ? $profile_data['music_genres'] : array();
                                    ?>
                                    <?php foreach ($available_music_genres as $value => $label) : ?>
                                        <div class="profile-edit-filter-item">
                                            <input type="checkbox" class="profile-edit-filter-checkbox" name="music_genres[]" id="edit_music_genre_<?php echo esc_attr($value); ?>" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $current_music_genres)); ?>>
                                            <label for="edit_music_genre_<?php echo esc_attr($value); ?>" class="profile-edit-filter-label">
                                                <span class="profile-edit-filter-star">☆</span>
                                                <?php echo esc_html($label); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="profile-edit-form-actions">
                            <button type="submit" name="profile_update_submit" class="btn profile-edit-submit-btn">Enregistrer les modifications</button>
                            <button type="button" class="btn profile-edit-cancel-btn" id="cancel-edit-form">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Modern Profile Info Section -->
            <div class="profile-info-modern">
                <!-- Contact Button (for other users) -->
                <?php if (get_current_user_id() != $profile_data['id']) : ?>
                    <div class="profile-contact-wrapper">
                        <button class="btn-profile-contact" id="contact-btn">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.5 6.66667L10 11.6667L17.5 6.66667M3.33333 15H16.6667C17.5871 15 18.3333 14.2538 18.3333 13.3333V6.66667C18.3333 5.74619 17.5871 5 16.6667 5H3.33333C2.41286 5 1.66667 5.74619 1.66667 6.66667V13.3333C1.66667 14.2538 2.41286 15 3.33333 15Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Contacter
                        </button>
                        
                        <!-- Contact Modal -->
                        <div class="contact-modal" id="contact-modal">
                            <div class="contact-modal-overlay" id="contact-modal-overlay"></div>
                            <div class="contact-modal-content">
                                <button class="contact-modal-close" id="contact-modal-close">×</button>
                                <h3 class="contact-modal-title">Informations de contact</h3>
                                <div class="contact-info-list">
                                    <?php if (!empty($profile_data['full_name'])) : ?>
                                        <div class="contact-info-item">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9 9C11.0711 9 12.75 7.32107 12.75 5.25C12.75 3.17893 11.0711 1.5 9 1.5C6.92893 1.5 5.25 3.17893 5.25 5.25C5.25 7.32107 6.92893 9 9 9Z" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M3.72754 15.75C3.72754 12.8522 6.10217 10.5 9 10.5C11.8978 10.5 14.2725 12.8522 14.2725 15.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            <span class="contact-info-label">Nom</span>
                                            <span class="contact-info-value"><?php echo esc_html($profile_data['full_name']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($profile_data['email'])) : ?>
                                        <div class="contact-info-item">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M2.25 6L9 10.5L15.75 6M3 13.5H15C15.4142 13.5 15.75 13.1642 15.75 12.75V5.25C15.75 4.83579 15.4142 4.5 15 4.5H3C2.58579 4.5 2.25 4.83579 2.25 5.25V12.75C2.25 13.1642 2.58579 13.5 3 13.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span class="contact-info-label">Email</span>
                                            <a href="mailto:<?php echo esc_attr($profile_data['email']); ?>" class="contact-info-value contact-link"><?php echo esc_html($profile_data['email']); ?></a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($profile_data['phone'])) : ?>
                                        <div class="contact-info-item">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3.75 3.75L7.4025 7.4025M14.25 14.25L10.5975 10.5975M10.5975 10.5975L12.75 6.75L8.25 2.25L4.4025 4.4025M10.5975 10.5975L7.4025 7.4025M7.4025 7.4025L9.75 5.25L13.5975 9.0975" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span class="contact-info-label">Téléphone</span>
                                            <a href="tel:<?php echo esc_attr($profile_data['phone']); ?>" class="contact-info-value contact-link"><?php echo esc_html($profile_data['phone']); ?></a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Main Info Cards -->
                <div class="profile-info-cards">
                    <!-- Ville Card -->
                    <?php if (!empty($profile_data['ville'])) : ?>
                        <div class="info-card">
                            <div class="info-card-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C8.13 2 5 5.13 5 9C5 14.25 12 22 12 22C12 22 19 14.25 19 9C19 5.13 15.87 2 12 2ZM12 11.5C10.62 11.5 9.5 10.38 9.5 9C9.5 7.62 10.62 6.5 12 6.5C13.38 6.5 14.5 7.62 14.5 9C14.5 10.38 13.38 11.5 12 11.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="info-card-content">
                                <span class="info-card-label">Localisation</span>
                                <span class="info-card-value"><?php echo esc_html($profile_data['ville']); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Bio Card -->
                    <?php if (!empty($profile_data['biographie'])) : ?>
                        <div class="info-card info-card-bio">
                            <div class="info-card-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 13H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M16 17H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M10 9H9H8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="info-card-content">
                                <span class="info-card-label">À propos</span>
                                <p class="info-card-bio-text"><?php echo esc_html($profile_data['biographie']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Services Card (only for offer service type) -->
                    <?php if ($profile_data['service_type'] === 'offer' && !empty($profile_data['filters_labels'])) : ?>
                        <div class="info-card info-card-services">
                            <div class="info-card-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="info-card-content">
                                <span class="info-card-label">Services proposés</span>
                                <div class="info-card-services-list">
                                    <?php foreach ($profile_data['filters_labels'] as $service) : ?>
                                        <span class="service-tag"><?php echo esc_html($service); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Music Genres Card (only for seek service type) -->
                    <?php if ($profile_data['service_type'] === 'seek' && !empty($profile_data['music_genres'])) : ?>
                        <div class="info-card info-card-services">
                            <div class="info-card-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 18V5L21 3V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    <circle cx="18" cy="16" r="3" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </div>
                            <div class="info-card-content">
                                <span class="info-card-label">Genres musicaux préférés</span>
                                <div class="info-card-services-list">
                                    <?php foreach ($profile_data['music_genres'] as $music_genre) : ?>
                                        <span class="service-tag"><?php echo esc_html($music_genre); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
            
            <!-- Productions Section (only for users offering services) -->
            <?php if ($profile_data['service_type'] === 'offer') : ?>
            <div class="productions-section">
                <div class="productions-header">
                    <h2 class="productions-title">Productions</h2>
                    <button class="btn btn-add-production" id="add-production-btn">+ Ajouter une production</button>
                </div>
                
                <!-- Add Production Form (Hidden by default) -->
                <div class="add-production-form-wrapper" id="add-production-form-wrapper" style="display: none;">
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="add-production-form">
                        <?php wp_nonce_field('production_action', 'production_nonce'); ?>
                        <input type="hidden" name="production_action" value="add">
                        
                        <div class="mb-3">
                            <label for="production_title" class="form-label">Titre de la production</label>
                            <input type="text" class="form-control" name="production_title" id="production_title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="production_genre" class="form-label">Genre</label>
                            <input type="text" class="form-control" name="production_genre" id="production_genre" placeholder="Ex: Indie Rock, Rock Alternatif, Neo-Blues" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="production_description" class="form-label">Description</label>
                            <textarea class="form-control" name="production_description" id="production_description" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="production_rating" class="form-label">Note (1-5)</label>
                            <select class="form-control" name="production_rating" id="production_rating">
                                <option value="5" selected>5 étoiles</option>
                                <option value="4">4 étoiles</option>
                                <option value="3">3 étoiles</option>
                                <option value="2">2 étoiles</option>
                                <option value="1">1 étoile</option>
                            </select>
                        </div>
                        
                        <div class="add-production-form-actions">
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                            <button type="button" class="btn btn-secondary" id="cancel-add-production">Annuler</button>
                        </div>
                    </form>
                </div>
                
                <!-- Productions List -->
                <div class="productions-list">
                    <?php 
                    $productions = isset($profile_data['productions']) ? $profile_data['productions'] : array();
                    
                    if (empty($productions)) : ?>
                        <p class="productions-empty">Aucune production pour le moment. Ajoutez votre première production pour montrer de quoi vous êtes capable !</p>
                    <?php else : ?>
                        <?php foreach ($productions as $production) : ?>
                            <div class="production-item">
                                <div class="production-icon">
                                    <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="30" cy="30" r="30" fill="#000"/>
                                        <circle cx="30" cy="30" r="20" fill="#fff" opacity="0.1"/>
                                        <circle cx="30" cy="30" r="8" fill="#000"/>
                                    </svg>
                                </div>
                                
                                <div class="production-content">
                                    <h3 class="production-title"><?php echo esc_html($production['title']); ?></h3>
                                    <p class="production-genre"><?php echo esc_html($production['genre']); ?>.</p>
                                    <p class="production-description"><?php echo esc_html($production['description']); ?></p>
                                </div>
                                
                                <div class="production-actions">
                                    <div class="production-rating">
                                        <?php 
                                        $rating = isset($production['rating']) ? intval($production['rating']) : 5;
                                        for ($i = 0; $i < 5; $i++) : 
                                            echo $i < $rating ? '★' : '☆';
                                        endfor; 
                                        ?>
                                    </div>
                                    <button class="production-favorite" aria-label="Ajouter aux favoris">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10 17.5L8.55 16.15C3.4 11.8 0 8.9 0 5.25C0 2.35 2.35 0 5.25 0C6.9 0 8.5 0.75 9.5 1.95L10 2.5L10.5 1.95C11.5 0.75 13.1 0 14.75 0C17.65 0 20 2.35 20 5.25C20 8.9 16.6 11.8 11.45 16.15L10 17.5Z" stroke="#fff" stroke-width="1.5" fill="none"/>
                                        </svg>
                                    </button>
                                    <?php if (get_current_user_id() == $profile_data['id']) : ?>
                                        <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="delete-production-form" style="display: inline;">
                                            <?php wp_nonce_field('production_action', 'production_nonce'); ?>
                                            <input type="hidden" name="production_action" value="delete">
                                            <input type="hidden" name="production_id" value="<?php echo esc_attr($production['id']); ?>">
                                            <button type="submit" class="production-delete" aria-label="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette production ?');">×</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Contact Button -->
                <?php if (!empty($productions) && get_current_user_id() != $profile_data['id']) : ?>
                    <div class="productions-contact">
                        <a href="mailto:<?php echo esc_attr($profile_data['email']); ?>" class="btn btn-contact">contacter</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>