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
                <!-- Contact and Favorite Buttons (for other users) -->
                <?php if (is_user_logged_in() && get_current_user_id() != $profile_data['id']) : 
                    $is_favorited = is_favorited(get_current_user_id(), 'user', $profile_data['id']);
                ?>
                    <div class="profile-contact-wrapper">
                        <a href="<?php echo esc_url(home_url('/messagerie?user_id=' . $profile_data['id'])); ?>" class="btn-profile-contact">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Contacter
                        </a>
                        <button class="btn-profile-favorite <?php echo $is_favorited ? 'favorited' : ''; ?>" 
                                data-item-type="user" 
                                data-item-id="<?php echo esc_attr($profile_data['id']); ?>" 
                                aria-label="<?php echo $is_favorited ? 'Retirer des favoris' : 'Ajouter aux favoris'; ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $is_favorited ? 'currentColor' : 'none'; ?>" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19 21L12 16L5 21V5C5 4.46957 5.21071 3.96086 5.58579 3.58579C5.96086 3.21071 6.46957 3 7 3H17C17.5304 3 18.0391 3.21071 18.4142 3.58579C18.7893 3.96086 19 4.46957 19 5V21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
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
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="add-production-form" enctype="multipart/form-data">
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
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="production_audio" class="form-label">Fichier audio (MP3, WAV, OGG)</label>
                                <input type="file" class="form-control" name="production_audio" id="production_audio" accept="audio/*">
                                <small class="form-text text-muted">Max 50MB</small>
                            </div>
                            <div class="col-md-6">
                                <label for="production_video" class="form-label">Fichier vidéo (MP4, WebM)</label>
                                <input type="file" class="form-control" name="production_video" id="production_video" accept="video/*">
                                <small class="form-text text-muted">Max 50MB</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="production_soundcloud" class="form-label">Lien SoundCloud (optionnel)</label>
                            <input type="url" class="form-control" name="production_soundcloud_url" id="production_soundcloud" placeholder="https://soundcloud.com/...">
                        </div>
                        
                        <div class="mb-3">
                            <label for="production_spotify" class="form-label">Lien Spotify (optionnel)</label>
                            <input type="url" class="form-control" name="production_spotify_url" id="production_spotify" placeholder="https://open.spotify.com/...">
                        </div>
                        
                        <div class="mb-3">
                            <label for="production_youtube" class="form-label">Lien YouTube (optionnel)</label>
                            <input type="url" class="form-control" name="production_youtube_url" id="production_youtube" placeholder="https://www.youtube.com/...">
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
                        <?php foreach ($productions as $production) : 
                            $comment_count = get_production_comment_count($production['id']);
                            $comments = get_production_comments($production['id'], 10, 0);
                        ?>
                            <div class="production-item" data-production-id="<?php echo esc_attr($production['id']); ?>">
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
                                    
                                    <!-- Production Media -->
                                    <div class="production-media">
                                        <?php if (!empty($production['audio_file'])) : ?>
                                            <div class="production-media-item">
                                                <audio controls class="production-audio-player">
                                                    <source src="<?php echo esc_url($production['audio_file']); ?>" type="audio/mpeg">
                                                    Votre navigateur ne supporte pas l'élément audio.
                                                </audio>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($production['video_file'])) : ?>
                                            <div class="production-media-item">
                                                <video controls class="production-video-player">
                                                    <source src="<?php echo esc_url($production['video_file']); ?>" type="video/mp4">
                                                    Votre navigateur ne supporte pas l'élément vidéo.
                                                </video>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($production['youtube_url'])) : 
                                            // Extract YouTube video ID
                                            $youtube_id = '';
                                            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $production['youtube_url'], $matches)) {
                                                $youtube_id = $matches[1];
                                            }
                                        ?>
                                            <?php if ($youtube_id) : ?>
                                                <div class="production-media-item production-youtube-embed">
                                                    <iframe 
                                                        width="100%" 
                                                        height="315" 
                                                        src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>" 
                                                        frameborder="0" 
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                        allowfullscreen>
                                                    </iframe>
                                                </div>
                                            <?php else : ?>
                                                <div class="production-media-item">
                                                    <a href="<?php echo esc_url($production['youtube_url']); ?>" target="_blank" rel="noopener noreferrer" class="production-external-link">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M18 13V19A2 2 0 0 1 16 21H5A2 2 0 0 1 3 19V8A2 2 0 0 1 5 6H11M15 3H21M21 3V9M21 3L10 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        Voir sur YouTube
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- External Links -->
                                    <?php if (!empty($production['soundcloud_url']) || !empty($production['spotify_url']) || !empty($production['youtube_url'])) : ?>
                                        <div class="production-external-links">
                                            <?php if (!empty($production['soundcloud_url'])) : ?>
                                                <a href="<?php echo esc_url($production['soundcloud_url']); ?>" target="_blank" rel="noopener noreferrer" class="production-platform-link production-soundcloud">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.568 17.5c-.169 0-.312-.143-.312-.312v-1.25c0-.169.143-.312.312-.312.169 0 .312.143.312.312v1.25c0 .169-.143.312-.312.312zm-1.25-2.5c-.169 0-.312-.143-.312-.312V8.312c0-.169.143-.312.312-.312.169 0 .312.143.312.312v6.376c0 .169-.143.312-.312.312zm-1.25-3.75c-.169 0-.312-.143-.312-.312V8.312c0-.169.143-.312.312-.312.169 0 .312.143.312.312v2.626c0 .169-.143.312-.312.312zm-1.25-2.5c-.169 0-.312-.143-.312-.312V8.312c0-.169.143-.312.312-.312.169 0 .312.143.312.312v.626c0 .169-.143.312-.312.312zm-1.25-1.25c-.169 0-.312-.143-.312-.312v-.313c0-.169.143-.312.312-.312.169 0 .312.143.312.312v.313c0 .169-.143.312-.312.312z"/>
                                                    </svg>
                                                    SoundCloud
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($production['spotify_url'])) : ?>
                                                <a href="<?php echo esc_url($production['spotify_url']); ?>" target="_blank" rel="noopener noreferrer" class="production-platform-link production-spotify">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.84-.179-.84-.66 0-.419.24-.66.54-.84 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.24 1.021zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
                                                    </svg>
                                                    Spotify
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($production['youtube_url']) && empty($production['video_file'])) : 
                                                // Only show link if not embedded
                                                $youtube_id = '';
                                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $production['youtube_url'], $matches)) {
                                                    $youtube_id = $matches[1];
                                                }
                                                if (!$youtube_id) :
                                            ?>
                                                <a href="<?php echo esc_url($production['youtube_url']); ?>" target="_blank" rel="noopener noreferrer" class="production-platform-link production-youtube">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                    </svg>
                                                    YouTube
                                                </a>
                                            <?php endif; endif; ?>
                                        </div>
                                    <?php endif; ?>
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
                                        <button type="button" class="production-edit" data-production-id="<?php echo esc_attr($production['id']); ?>" aria-label="Modifier">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13M18.5 2.5C18.8978 2.10217 19.4374 1.87868 20 1.87868C20.5626 1.87868 21.1022 2.10217 21.5 2.5C21.8978 2.89782 22.1213 3.43739 22.1213 4C22.1213 4.56261 21.8978 5.10217 21.5 5.5L12 15L8 16L9 12L18.5 2.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="delete-production-form" style="display: inline;">
                                            <?php wp_nonce_field('production_action', 'production_nonce'); ?>
                                            <input type="hidden" name="production_action" value="delete">
                                            <input type="hidden" name="production_id" value="<?php echo esc_attr($production['id']); ?>">
                                            <button type="submit" class="production-delete" aria-label="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette production ?');">×</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Edit Production Form (Hidden by default) -->
                                <?php if (get_current_user_id() == $profile_data['id']) : ?>
                                    <div class="edit-production-form-wrapper" id="edit-production-<?php echo esc_attr($production['id']); ?>" style="display: none;">
                                        <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="edit-production-form" enctype="multipart/form-data">
                                            <?php wp_nonce_field('production_action', 'production_nonce'); ?>
                                            <input type="hidden" name="production_action" value="edit">
                                            <input type="hidden" name="production_id" value="<?php echo esc_attr($production['id']); ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Titre de la production</label>
                                                <input type="text" class="form-control" name="production_title" value="<?php echo esc_attr($production['title']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Genre</label>
                                                <input type="text" class="form-control" name="production_genre" value="<?php echo esc_attr($production['genre']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="production_description" rows="3" required><?php echo esc_textarea($production['description']); ?></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Note (1-5)</label>
                                                <select class="form-control" name="production_rating">
                                                    <?php for ($i = 5; $i >= 1; $i--) : ?>
                                                        <option value="<?php echo $i; ?>" <?php selected(isset($production['rating']) ? $production['rating'] : 5, $i); ?>><?php echo $i; ?> étoile<?php echo $i > 1 ? 's' : ''; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Fichier audio (remplacer)</label>
                                                    <input type="file" class="form-control" name="production_audio" accept="audio/*">
                                                    <?php if (!empty($production['audio_file'])) : ?>
                                                        <small class="form-text text-muted">Fichier actuel: <a href="<?php echo esc_url($production['audio_file']); ?>" target="_blank">Écouter</a></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Fichier vidéo (remplacer)</label>
                                                    <input type="file" class="form-control" name="production_video" accept="video/*">
                                                    <?php if (!empty($production['video_file'])) : ?>
                                                        <small class="form-text text-muted">Fichier actuel: <a href="<?php echo esc_url($production['video_file']); ?>" target="_blank">Voir</a></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Lien SoundCloud</label>
                                                <input type="url" class="form-control" name="production_soundcloud_url" value="<?php echo esc_attr(isset($production['soundcloud_url']) ? $production['soundcloud_url'] : ''); ?>" placeholder="https://soundcloud.com/...">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Lien Spotify</label>
                                                <input type="url" class="form-control" name="production_spotify_url" value="<?php echo esc_attr(isset($production['spotify_url']) ? $production['spotify_url'] : ''); ?>" placeholder="https://open.spotify.com/...">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Lien YouTube</label>
                                                <input type="url" class="form-control" name="production_youtube_url" value="<?php echo esc_attr(isset($production['youtube_url']) ? $production['youtube_url'] : ''); ?>" placeholder="https://www.youtube.com/...">
                                            </div>
                                            
                                            <div class="edit-production-form-actions">
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                <button type="button" class="btn btn-secondary cancel-edit-production" data-production-id="<?php echo esc_attr($production['id']); ?>">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Comments Section -->
                                <div class="production-comments-section">
                                    <button class="production-comments-toggle" data-production-id="<?php echo esc_attr($production['id']); ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span class="comments-count"><?php echo $comment_count; ?></span>
                                        <span class="comments-label"><?php echo $comment_count == 1 ? 'commentaire' : 'commentaires'; ?></span>
                                    </button>
                                    
                                    <div class="production-comments-container" id="comments-<?php echo esc_attr($production['id']); ?>" style="display: none;">
                                        <div class="production-comments-list" id="comments-list-<?php echo esc_attr($production['id']); ?>">
                                            <?php if (!empty($comments)) : ?>
                                                <?php foreach ($comments as $comment) : ?>
                                                    <div class="production-comment-item" data-comment-id="<?php echo esc_attr($comment['id']); ?>">
                                                        <div class="production-comment-avatar">
                                                            <?php if (!empty($comment['user_photo'])) : ?>
                                                                <img src="<?php echo esc_url($comment['user_photo']); ?>" alt="<?php echo esc_attr($comment['user_name']); ?>">
                                                            <?php else : ?>
                                                                <div class="production-comment-avatar-placeholder">
                                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    </svg>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="production-comment-content">
                                                            <div class="production-comment-header">
                                                                <span class="production-comment-author"><?php echo esc_html($comment['user_name']); ?></span>
                                                                <span class="production-comment-date"><?php echo human_time_diff(strtotime($comment['created_at']), current_time('timestamp')); ?></span>
                                                                <?php if ($comment['is_own'] || get_current_user_id() == $profile_data['id']) : ?>
                                                                    <button class="production-comment-delete" data-comment-id="<?php echo esc_attr($comment['id']); ?>" aria-label="Supprimer">
                                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        </svg>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                            <p class="production-comment-text"><?php echo nl2br(esc_html($comment['comment'])); ?></p>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <p class="production-comments-empty">Aucun commentaire pour le moment.</p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <?php if (is_user_logged_in()) : ?>
                                            <div class="production-comment-form">
                                                <form class="add-production-comment-form" data-production-id="<?php echo esc_attr($production['id']); ?>" data-production-owner-id="<?php echo esc_attr($profile_data['id']); ?>">
                                                    <div class="production-comment-input-wrapper">
                                                        <textarea 
                                                            class="production-comment-input" 
                                                            placeholder="Ajouter un commentaire..." 
                                                            rows="2"
                                                            required></textarea>
                                                        <button type="submit" class="production-comment-submit">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M22 2L11 13M22 2L15 22L11 13M22 2L2 9L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        <?php else : ?>
                                            <p class="production-comments-login-required">Connectez-vous pour commenter.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Contact Button -->
                <?php if (!empty($productions) && get_current_user_id() != $profile_data['id']) : ?>
                    <div class="productions-contact">
                        <a href="<?php echo esc_url(home_url('/messagerie?user_id=' . $profile_data['id'])); ?>" class="btn btn-contact">contacter</a>
                    </div>
                <?php endif; ?>
                
                <!-- Favorites Section (only for own profile) -->
                <?php if (is_user_logged_in() && get_current_user_id() == $profile_data['id']) : 
                    $favorites = get_user_favorites(get_current_user_id());
                    $user_favorites = array();
                    $annonce_favorites = array();
                    
                    foreach ($favorites as $fav) {
                        if ($fav->item_type === 'user') {
                            $user = get_userdata($fav->item_id);
                            if ($user) {
                                $user_profile = get_user_profile_data($fav->item_id);
                                if ($user_profile) {
                                    $user_favorites[] = array(
                                        'id' => $fav->item_id,
                                        'profile' => $user_profile,
                                        'created_at' => $fav->created_at
                                    );
                                }
                            }
                        } elseif ($fav->item_type === 'annonce') {
                            $post = get_post($fav->item_id);
                            if ($post) {
                                $annonce_favorites[] = array(
                                    'id' => $fav->item_id,
                                    'post' => $post,
                                    'created_at' => $fav->created_at
                                );
                            }
                        }
                    }
                ?>
                    <div class="profile-favorites-section">
                        <h2 class="profile-section-title">Mes Favoris</h2>
                        
                        <?php if (empty($favorites)) : ?>
                            <p class="favorites-empty">Aucun favori pour le moment. Ajoutez des utilisateurs ou des annonces à vos favoris !</p>
                        <?php else : ?>
                            <!-- User Favorites -->
                            <?php if (!empty($user_favorites)) : ?>
                                <div class="favorites-users">
                                    <h3 class="favorites-subtitle">Utilisateurs</h3>
                                    <div class="favorites-users-list">
                                        <?php foreach ($user_favorites as $fav_user) : ?>
                                            <div class="favorite-user-item">
                                                <a href="<?php echo esc_url(home_url('/userprofil?user_id=' . $fav_user['id'])); ?>" class="favorite-user-link">
                                                    <div class="favorite-user-avatar">
                                                        <?php if (!empty($fav_user['profile']['profile_photo_url'])) : ?>
                                                            <img src="<?php echo esc_url($fav_user['profile']['profile_photo_url']); ?>" alt="<?php echo esc_attr($fav_user['profile']['full_name']); ?>">
                                                        <?php else : ?>
                                                            <div class="favorite-user-avatar-placeholder">
                                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="favorite-user-info">
                                                        <h4 class="favorite-user-name"><?php echo esc_html($fav_user['profile']['full_name']); ?></h4>
                                                        <?php if (!empty($fav_user['profile']['ville'])) : ?>
                                                            <p class="favorite-user-location"><?php echo esc_html($fav_user['profile']['ville']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                                <button class="favorite-remove-btn" 
                                                        data-item-type="user" 
                                                        data-item-id="<?php echo esc_attr($fav_user['id']); ?>" 
                                                        aria-label="Retirer des favoris">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Annonce Favorites -->
                            <?php if (!empty($annonce_favorites)) : ?>
                                <div class="favorites-annonces">
                                    <h3 class="favorites-subtitle">Annonces</h3>
                                    <div class="favorites-annonces-list">
                                        <?php foreach ($annonce_favorites as $fav_annonce) : 
                                            $image = get_the_post_thumbnail_url($fav_annonce['id'], 'medium');
                                        ?>
                                            <div class="favorite-annonce-item">
                                                <a href="<?php echo esc_url(get_permalink($fav_annonce['id'])); ?>" class="favorite-annonce-link">
                                                    <?php if ($image) : ?>
                                                        <div class="favorite-annonce-image">
                                                            <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($fav_annonce['post']->post_title); ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="favorite-annonce-info">
                                                        <h4 class="favorite-annonce-title"><?php echo esc_html($fav_annonce['post']->post_title); ?></h4>
                                                        <p class="favorite-annonce-description"><?php echo esc_html(wp_trim_words($fav_annonce['post']->post_content, 15)); ?></p>
                                                    </div>
                                                </a>
                                                <button class="favorite-remove-btn" 
                                                        data-item-type="annonce" 
                                                        data-item-id="<?php echo esc_attr($fav_annonce['id']); ?>" 
                                                        aria-label="Retirer des favoris">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>