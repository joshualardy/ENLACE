<?php
/**
 * Template Name: User Profil
 */

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

get_header();

// Get user profile data
$profile_data = get_user_profile_data();

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
                        
                        <div class="profile-edit-form-actions">
                            <button type="submit" name="profile_update_submit" class="btn profile-edit-submit-btn">Enregistrer les modifications</button>
                            <button type="button" class="btn profile-edit-cancel-btn" id="cancel-edit-form">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6">
                    <div class="profile-section">
                        <h2 class="profile-section-title">Informations personnelles</h2>
                        <div class="profile-info-list">
                            <?php if (!empty($profile_data['first_name']) || !empty($profile_data['last_name'])) : ?>
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Nom complet :</span>
                                    <span class="profile-info-value"><?php echo esc_html($profile_data['full_name']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($profile_data['email'])) : ?>
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Email :</span>
                                    <span class="profile-info-value"><?php echo esc_html($profile_data['email']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($profile_data['phone'])) : ?>
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Téléphone :</span>
                                    <span class="profile-info-value"><?php echo esc_html($profile_data['phone']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($profile_data['ville'])) : ?>
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Ville :</span>
                                    <span class="profile-info-value"><?php echo esc_html($profile_data['ville']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($profile_data['genre'])) : ?>
                                <div class="profile-info-item">
                                    <span class="profile-info-label">Genre musical :</span>
                                    <span class="profile-info-value"><?php echo esc_html($profile_data['genre']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="profile-info-item">
                                <span class="profile-info-label">Type de service :</span>
                                <span class="profile-info-value">
                                    <?php echo $profile_data['service_type'] === 'offer' ? 'J\'offre mon service' : 'Je cherche un service'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Services Offered (if service_type = 'offer') -->
                <?php if ($profile_data['service_type'] === 'offer' && !empty($profile_data['filters_labels'])) : ?>
                    <div class="col-md-6">
                        <div class="profile-section">
                            <h2 class="profile-section-title">Services proposés</h2>
                            <div class="profile-services-list">
                                <?php foreach ($profile_data['filters_labels'] as $service) : ?>
                                    <div class="profile-service-item">
                                        <span class="profile-service-star">☆</span>
                                        <span class="profile-service-label"><?php echo esc_html($service); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>