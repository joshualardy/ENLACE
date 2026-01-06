<?php
/**
 * Template Name: Settings
 */

// Redirect if not logged in
if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/login'));
    exit;
}

// Ensure session is started
if (!session_id()) {
    session_start();
}

// Check if user is accessing from profile page
// Allow access if:
// 1. Referer contains '/userprofil' or 'userprofil'
// 2. Or if there's a valid settings access token in session (from profile page)
// 3. Or if coming from a form submission on settings page itself
$allowed_access = false;

// Check referer
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    // Allow if coming from profile page
    if (strpos($referer, '/userprofil') !== false || strpos($referer, 'userprofil') !== false) {
        $allowed_access = true;
        // Set session token for subsequent requests
        $_SESSION['settings_access_allowed'] = true;
        $_SESSION['settings_access_time'] = time();
    }
    // Allow if coming from settings page itself (form submissions, navigation)
    elseif (strpos($referer, '/settings') !== false || strpos($referer, 'settings') !== false) {
        $allowed_access = true;
        // Refresh session token
        $_SESSION['settings_access_allowed'] = true;
        $_SESSION['settings_access_time'] = time();
    }
}

// Check session token (valid for 10 minutes)
if (!$allowed_access) {
    if (isset($_SESSION['settings_access_allowed']) && $_SESSION['settings_access_allowed'] === true) {
        $access_time = isset($_SESSION['settings_access_time']) ? $_SESSION['settings_access_time'] : 0;
        // Token valid for 10 minutes
        if (time() - $access_time < 600) {
            $allowed_access = true;
            // Refresh token on valid access
            $_SESSION['settings_access_time'] = time();
        } else {
            // Token expired, clear it
            unset($_SESSION['settings_access_allowed']);
            unset($_SESSION['settings_access_time']);
        }
    }
}

// If direct access without valid referer or session, redirect to profile
if (!$allowed_access) {
    wp_safe_redirect(home_url('/userprofil'));
    exit;
}

get_header();

// Get current user profile data
$profile_data = get_user_profile_data(get_current_user_id());

if (!$profile_data) {
    echo '<div class="container"><p>Erreur lors du chargement du profil.</p></div>';
    get_footer();
    exit;
}

// Get user statistics
$user_stats = get_user_statistics($profile_data['id']);

// Show success/error messages
$update_message = '';
if (isset($_GET['profile_updated'])) {
    if ($_GET['profile_updated'] === 'success') {
        $update_message = '<div class="profile-update-message success-message">Profil mis à jour avec succès !</div>';
    } elseif ($_GET['profile_updated'] === 'error') {
        $update_message = '<div class="profile-update-message error-message">Erreur lors de la mise à jour du profil.</div>';
    }
}

// Show password change messages
if (isset($_GET['password_change'])) {
    if ($_GET['password_change'] === 'success') {
        $update_message = '<div class="profile-update-message success-message">Mot de passe changé avec succès !</div>';
    } elseif ($_GET['password_change'] === 'wrong_password') {
        $update_message = '<div class="profile-update-message error-message">Mot de passe actuel incorrect.</div>';
    } elseif ($_GET['password_change'] === 'too_short') {
        $update_message = '<div class="profile-update-message error-message">Le nouveau mot de passe doit contenir au moins 8 caractères.</div>';
    } elseif ($_GET['password_change'] === 'mismatch') {
        $update_message = '<div class="profile-update-message error-message">Les mots de passe ne correspondent pas.</div>';
    }
}

// Get active settings tab
$active_settings_tab = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'profile';
?>

<div class="settings-page-container">
    <div class="container">
        <!-- Settings Header -->
        <div class="settings-header">
            <div class="settings-header-content">
                <h1 class="settings-page-title">Paramètres</h1>
                <p class="settings-page-subtitle">Gérez vos préférences et informations de compte</p>
            </div>
            <div class="settings-header-actions">
                <a href="<?php echo esc_url(home_url('/userprofil')); ?>" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 8px;">
                        <path d="M19 12H5M12 19L5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Retour au profil
                </a>
            </div>
        </div>
        
        <?php echo $update_message; ?>
        
        <div class="settings-section">
            <!-- Settings Navigation -->
            <div class="settings-nav">
                <button class="settings-nav-item <?php echo $active_settings_tab === 'profile' ? 'active' : ''; ?>" data-settings-tab="profile">Informations</button>
                <button class="settings-nav-item <?php echo $active_settings_tab === 'statistics' ? 'active' : ''; ?>" data-settings-tab="statistics">Statistiques</button>
                <button class="settings-nav-item <?php echo $active_settings_tab === 'privacy' ? 'active' : ''; ?>" data-settings-tab="privacy">Confidentialité</button>
                <button class="settings-nav-item <?php echo $active_settings_tab === 'security' ? 'active' : ''; ?>" data-settings-tab="security">Sécurité</button>
                <button class="settings-nav-item <?php echo $active_settings_tab === 'notifications' ? 'active' : ''; ?>" data-settings-tab="notifications">Notifications</button>
            </div>
            
            <!-- Settings Content -->
            <div class="settings-content">
                
                <!-- Profile Settings Tab -->
                <div class="settings-tab-content <?php echo $active_settings_tab === 'profile' ? 'active' : ''; ?>" data-settings-content="profile">
                    <h2 class="settings-section-title">Informations du profil</h2>
                    <p class="settings-section-description">Modifiez vos informations personnelles et professionnelles.</p>
                    
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="settings-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('profile_update_action', 'profile_update_nonce'); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                        
                        <div class="settings-form-section">
                            <h3 class="settings-form-section-title">Informations personnelles</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="settings_first_name" class="form-label">Prénom</label>
                                        <input type="text" class="form-control" name="first_name" id="settings_first_name" autocomplete="given-name" value="<?php echo esc_attr($profile_data['first_name']); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="settings_last_name" class="form-label">Nom</label>
                                        <input type="text" class="form-control" name="last_name" id="settings_last_name" autocomplete="family-name" value="<?php echo esc_attr($profile_data['last_name']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="settings_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" name="user_email" id="settings_email" autocomplete="email" value="<?php echo esc_attr($profile_data['email']); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="settings_phone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" name="phone" id="settings_phone" autocomplete="tel" value="<?php echo esc_attr($profile_data['phone']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="settings_ville" class="form-label">Ville</label>
                                        <input type="text" class="form-control" name="ville" id="settings_ville" autocomplete="address-level2" value="<?php echo esc_attr($profile_data['ville']); ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="settings_genre" class="form-label">Genre musical</label>
                                        <input type="text" class="form-control" name="genre" id="settings_genre" value="<?php echo esc_attr($profile_data['genre']); ?>" placeholder="Ex: Rock, Blues, Indie">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="settings_biographie" class="form-label">Biographie</label>
                                <textarea class="form-control" name="biographie" id="settings_biographie" rows="4" placeholder="Décrivez-vous..."><?php echo esc_textarea($profile_data['biographie']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="settings_profile_photo" class="form-label">Photo de profil</label>
                                <div class="settings-photo-section">
                                    <?php if (!empty($profile_data['profile_photo_url'])) : ?>
                                        <div class="settings-current-photo">
                                            <img src="<?php echo esc_url($profile_data['profile_photo_url']); ?>" alt="Photo actuelle" class="settings-photo-preview">
                                            <p class="settings-photo-note">Photo actuelle</p>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" name="profile_photo" id="settings_profile_photo" accept="image/*" class="form-control">
                                    <small class="form-text text-muted">Formats acceptés : JPG, PNG, GIF, WEBP (max 5MB)</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Services Filters (only for offer service type) -->
                        <?php if ($profile_data['service_type'] === 'offer') : ?>
                        <div class="settings-form-section">
                            <h3 class="settings-form-section-title">Services proposés</h3>
                            <div class="settings-filters-grid">
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
                                    <div class="settings-filter-item">
                                        <input type="checkbox" class="form-check-input" name="filters[]" id="settings_filter_<?php echo esc_attr($value); ?>" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $current_filters)); ?>>
                                        <label for="settings_filter_<?php echo esc_attr($value); ?>" class="form-check-label">
                                            <?php echo esc_html($label); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Music Genres (only for seek service type) -->
                        <?php if ($profile_data['service_type'] === 'seek') : ?>
                        <div class="settings-form-section">
                            <h3 class="settings-form-section-title">Genres musicaux préférés</h3>
                            <div class="settings-filters-grid">
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
                                    <div class="settings-filter-item">
                                        <input type="checkbox" class="form-check-input" name="music_genres[]" id="settings_music_genre_<?php echo esc_attr($value); ?>" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $current_music_genres)); ?>>
                                        <label for="settings_music_genre_<?php echo esc_attr($value); ?>" class="form-check-label">
                                            <?php echo esc_html($label); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="settings-form-actions">
                            <button type="submit" name="profile_update_submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
                
                <!-- Statistics Tab -->
                <div class="settings-tab-content <?php echo $active_settings_tab === 'statistics' ? 'active' : ''; ?>" data-settings-content="statistics">
                    <h2 class="settings-section-title">Statistiques</h2>
                    <p class="settings-section-description">Vue d'ensemble de votre activité et de votre profil.</p>
                    
                    <div class="statistics-grid">
                        <div class="statistic-card">
                            <div class="statistic-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                            <div class="statistic-content">
                                <div class="statistic-value"><?php echo esc_html($user_stats['profile_views']); ?></div>
                                <div class="statistic-label">Vues du profil</div>
                            </div>
                        </div>
                        
                        <div class="statistic-card">
                            <div class="statistic-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M19 21L12 16L5 21V5C5 4.46957 5.21071 3.96086 5.58579 3.58579C5.96086 3.21071 6.46957 3 7 3H17C17.5304 3 18.0391 3.21071 18.4142 3.58579C18.7893 3.96086 19 4.46957 19 5V21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="statistic-content">
                                <div class="statistic-value"><?php echo esc_html($user_stats['favorites_received']); ?></div>
                                <div class="statistic-label">Favoris reçus</div>
                            </div>
                        </div>
                        
                        <div class="statistic-card">
                            <div class="statistic-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="statistic-content">
                                <div class="statistic-value"><?php echo esc_html($user_stats['messages_received']); ?></div>
                                <div class="statistic-label">Messages reçus</div>
                            </div>
                        </div>
                        
                        <?php if ($profile_data['service_type'] === 'offer') : ?>
                        <div class="statistic-card">
                            <div class="statistic-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 18V5L21 3V16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="18" cy="16" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </div>
                            <div class="statistic-content">
                                <div class="statistic-value"><?php echo esc_html($user_stats['productions_count']); ?></div>
                                <div class="statistic-label">Productions</div>
                            </div>
                        </div>
                        
                        <div class="statistic-card">
                            <div class="statistic-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="statistic-content">
                                <div class="statistic-value"><?php echo esc_html($user_stats['productions_comments']); ?></div>
                                <div class="statistic-label">Commentaires</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="statistics-info">
                        <h3 class="statistics-info-title">Informations du compte</h3>
                        <div class="statistics-info-list">
                            <div class="statistics-info-item">
                                <span class="statistics-info-label">Date d'inscription :</span>
                                <span class="statistics-info-value"><?php echo esc_html($user_stats['registered_date']); ?></span>
                            </div>
                            <div class="statistics-info-item">
                                <span class="statistics-info-label">Dernière activité :</span>
                                <span class="statistics-info-value"><?php echo esc_html($user_stats['last_active']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Privacy Tab -->
                <div class="settings-tab-content <?php echo $active_settings_tab === 'privacy' ? 'active' : ''; ?>" data-settings-content="privacy">
                    <h2 class="settings-section-title">Confidentialité</h2>
                    <p class="settings-section-description">Gérez la visibilité de votre profil et vos informations.</p>
                    
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="settings-form">
                        <?php wp_nonce_field('profile_update_action', 'profile_update_nonce'); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                        
                        <div class="settings-form-section">
                            <div class="settings-toggle-item">
                                <div class="settings-toggle-info">
                                    <h4 class="settings-toggle-title">Afficher l'email</h4>
                                    <p class="settings-toggle-description">Permettre aux autres utilisateurs de voir votre adresse email</p>
                                </div>
                                <label class="settings-toggle-switch">
                                    <input type="checkbox" name="show_email" value="1" <?php checked(get_user_meta($profile_data['id'], 'show_email', true), '1'); ?>>
                                    <span class="settings-toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="settings-toggle-item">
                                <div class="settings-toggle-info">
                                    <h4 class="settings-toggle-title">Afficher le téléphone</h4>
                                    <p class="settings-toggle-description">Permettre aux autres utilisateurs de voir votre numéro de téléphone</p>
                                </div>
                                <label class="settings-toggle-switch">
                                    <input type="checkbox" name="show_phone" value="1" <?php checked(get_user_meta($profile_data['id'], 'show_phone', true), '1'); ?>>
                                    <span class="settings-toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="settings-form-actions">
                                <button type="submit" name="profile_update_submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Security Tab -->
                <div class="settings-tab-content <?php echo $active_settings_tab === 'security' ? 'active' : ''; ?>" data-settings-content="security">
                    <h2 class="settings-section-title">Sécurité</h2>
                    <p class="settings-section-description">Gérez votre mot de passe et la sécurité de votre compte.</p>
                    
                    <div class="settings-form-section">
                        <h3 class="settings-form-section-title">Changer le mot de passe</h3>
                        <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="settings-form">
                            <?php wp_nonce_field('password_change_action', 'password_change_nonce'); ?>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password" class="form-control" name="current_password" id="current_password" autocomplete="current-password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" name="new_password" id="new_password" autocomplete="new-password" required minlength="8">
                                <small class="form-text text-muted">Minimum 8 caractères</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" autocomplete="new-password" required>
                            </div>
                            
                            <div class="settings-form-actions">
                                <button type="submit" name="password_change_submit" class="btn btn-primary">Changer le mot de passe</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Notifications Tab -->
                <div class="settings-tab-content <?php echo $active_settings_tab === 'notifications' ? 'active' : ''; ?>" data-settings-content="notifications">
                    <h2 class="settings-section-title">Notifications</h2>
                    <p class="settings-section-description">Gérez vos préférences de notifications.</p>
                    
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="settings-form">
                        <?php wp_nonce_field('profile_update_action', 'profile_update_nonce'); ?>
                        <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                        
                        <div class="settings-form-section">
                            <h3 class="settings-form-section-title">Apparence</h3>
                            
                            <div class="settings-toggle-item">
                                <div class="settings-toggle-info">
                                    <h4 class="settings-toggle-title">Mode sombre</h4>
                                    <p class="settings-toggle-description">Activer le mode sombre pour l'interface</p>
                                </div>
                                <button type="button" class="theme-toggle-btn settings-theme-toggle" id="theme-toggle" aria-label="Basculer entre mode clair et sombre" title="Basculer le thème">
                                    <svg class="theme-icon theme-icon-light" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
                                        <path d="M12 1V3M12 21V23M4.22 4.22L5.64 5.64M18.36 18.36L19.78 19.78M1 12H3M21 12H23M4.22 19.78L5.64 18.36M18.36 5.64L19.78 4.22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <svg class="theme-icon theme-icon-dark" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <hr class="settings-section-divider">
                            
                            <h3 class="settings-form-section-title">Notifications</h3>
                            
                            <div class="settings-toggle-item">
                                <div class="settings-toggle-info">
                                    <h4 class="settings-toggle-title">Notifications par email</h4>
                                    <p class="settings-toggle-description">Recevoir des notifications par email</p>
                                </div>
                                <label class="settings-toggle-switch">
                                    <input type="checkbox" name="email_notifications" value="1" <?php checked(get_user_meta($profile_data['id'], 'email_notifications', true), '1'); ?>>
                                    <span class="settings-toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="settings-toggle-item">
                                <div class="settings-toggle-info">
                                    <h4 class="settings-toggle-title">Nouveaux messages</h4>
                                    <p class="settings-toggle-description">Être notifié des nouveaux messages</p>
                                </div>
                                <label class="settings-toggle-switch">
                                    <input type="checkbox" name="notify_messages" value="1" <?php checked(get_user_meta($profile_data['id'], 'notify_messages', true) !== '0', true); ?>>
                                    <span class="settings-toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="settings-toggle-item">
                                <div class="settings-toggle-info">
                                    <h4 class="settings-toggle-title">Nouveaux favoris</h4>
                                    <p class="settings-toggle-description">Être notifié quand quelqu'un vous ajoute en favori</p>
                                </div>
                                <label class="settings-toggle-switch">
                                    <input type="checkbox" name="notify_favorites" value="1" <?php checked(get_user_meta($profile_data['id'], 'notify_favorites', true) !== '0', true); ?>>
                                    <span class="settings-toggle-slider"></span>
                                </label>
                            </div>
                            
                            <?php if ($profile_data['service_type'] === 'offer') : ?>
                            <div class="settings-toggle-item">
                                <div class="settings-toggle-info">
                                    <h4 class="settings-toggle-title">Commentaires sur productions</h4>
                                    <p class="settings-toggle-description">Être notifié des nouveaux commentaires sur vos productions</p>
                                </div>
                                <label class="settings-toggle-switch">
                                    <input type="checkbox" name="notify_comments" value="1" <?php checked(get_user_meta($profile_data['id'], 'notify_comments', true) !== '0', true); ?>>
                                    <span class="settings-toggle-slider"></span>
                                </label>
                            </div>
                            <?php endif; ?>
                            
                            <div class="settings-form-actions">
                                <button type="submit" name="profile_update_submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>

