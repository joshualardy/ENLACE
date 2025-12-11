<?php
/**
 * REFACTORING EXAMPLES
 * 
 * Ce fichier contient des exemples de code refactorisé pour résoudre
 * les problèmes identifiés dans l'audit technique.
 * 
 * ⚠️ NE PAS INCLURE CE FICHIER DANS functions.php
 * Ces exemples sont à utiliser comme référence pour refactorer le code existant.
 */

// ============================================================================
// 1. CONSTANTES POUR ÉLIMINER LES MAGIC STRINGS
// ============================================================================

if (!class_exists('ENLACE_Constants')) {
    class ENLACE_Constants {
        // Service types
        const SERVICE_TYPE_OFFER = 'offer';
        const SERVICE_TYPE_SEEK = 'seek';
        
        // Session keys
        const SESSION_REGISTRATION_DATA = 'registration_data';
        
        // User meta keys
        const USER_META_SERVICE_TYPE = 'service_type';
        const USER_META_FILTERS = 'filters';
        const USER_META_MUSIC_GENRES = 'music_genres';
        const USER_META_FIRST_NAME = 'first_name';
        const USER_META_LAST_NAME = 'last_name';
        const USER_META_PHONE = 'phone';
        const USER_META_VILLE = 'ville';
        const USER_META_BIOGRAPHIE = 'biographie';
        const USER_META_GENRE = 'genre';
        const USER_META_PROFILE_PHOTO = 'profile_photo_url';
        const USER_META_PRODUCTIONS = 'productions';
        
        // File upload
        const MAX_FILE_SIZE = 5242880; // 5MB
        const ALLOWED_IMAGE_TYPES = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
        
        // Error codes
        const ERROR_SIZE = 'size_error';
        const ERROR_TYPE = 'type_error';
        const ERROR_UPLOAD = 'upload_error';
    }
}

// ============================================================================
// 2. HELPERS POUR SESSIONS
// ============================================================================

/**
 * Get session data safely
 */
function enlace_get_session($key, $default = null) {
    if (!session_id()) {
        session_start();
    }
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Set session data safely
 */
function enlace_set_session($key, $value) {
    if (!session_id()) {
        session_start();
    }
    $_SESSION[$key] = $value;
}

/**
 * Clear session data
 */
function enlace_clear_session($key) {
    if (session_id() && isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

// ============================================================================
// 3. HELPERS POUR REDIRECTIONS
// ============================================================================

/**
 * Safe redirect with query args
 */
function enlace_redirect($path, $query_args = array()) {
    $url = home_url($path);
    if (!empty($query_args)) {
        $url = add_query_arg($query_args, $url);
    }
    wp_safe_redirect($url);
    exit;
}

/**
 * Redirect with error message
 */
function enlace_redirect_error($path, $error_code, $fields = array()) {
    $args = array('registration' => 'error', 'message' => $error_code);
    if (!empty($fields)) {
        $args['fields'] = implode(',', $fields);
    }
    enlace_redirect($path, $args);
}

/**
 * Redirect with success message
 */
function enlace_redirect_success($path, $message = 'success') {
    enlace_redirect($path, array('registration' => $message));
}

// ============================================================================
// 4. VALIDATION DES DONNÉES
// ============================================================================

/**
 * Validate registration data
 */
function enlace_validate_registration($data) {
    $errors = array();
    
    // Username
    if (empty($data['user_login'])) {
        $errors['user_login'] = 'Le nom d\'utilisateur est requis.';
    } elseif (strlen($data['user_login']) < 3) {
        $errors['user_login'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
    } elseif (!validate_username($data['user_login'])) {
        $errors['user_login'] = 'Le nom d\'utilisateur contient des caractères invalides.';
    } elseif (username_exists($data['user_login'])) {
        $errors['user_login'] = 'Ce nom d\'utilisateur existe déjà.';
    }
    
    // Email
    if (empty($data['user_email'])) {
        $errors['user_email'] = 'L\'email est requis.';
    } elseif (!is_email($data['user_email'])) {
        $errors['user_email'] = 'Format d\'email invalide.';
    } elseif (email_exists($data['user_email'])) {
        $errors['user_email'] = 'Cet email est déjà utilisé.';
    }
    
    // Password
    if (empty($data['user_pass'])) {
        $errors['user_pass'] = 'Le mot de passe est requis.';
    } elseif (strlen($data['user_pass']) < 8) {
        $errors['user_pass'] = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($data['user_pass'] !== $data['user_pass_confirm']) {
        $errors['user_pass_confirm'] = 'Les mots de passe ne correspondent pas.';
    }
    
    // Required fields
    $required_fields = array('first_name', 'last_name', 'phone', 'ville');
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = sprintf('Le champ %s est requis.', $field);
        }
    }
    
    return array(
        'valid' => empty($errors),
        'errors' => $errors
    );
}

// ============================================================================
// 5. UPLOAD D'IMAGE SÉCURISÉ
// ============================================================================

/**
 * Validate and upload image with comprehensive security checks
 */
function enlace_validate_and_upload_image($file, $max_size = null) {
    if ($max_size === null) {
        $max_size = ENLACE_Constants::MAX_FILE_SIZE;
    }
    
    // Check if file was uploaded
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return array(
            'success' => false,
            'error' => 'no_file',
            'message' => 'Aucun fichier uploadé.'
        );
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return array(
            'success' => false,
            'error' => ENLACE_Constants::ERROR_SIZE,
            'message' => sprintf('Le fichier est trop volumineux. Taille maximum : %dMB.', $max_size / 1024 / 1024)
        );
    }
    
    // Validate MIME type
    $allowed_types = ENLACE_Constants::ALLOWED_IMAGE_TYPES;
    $file_type = wp_check_filetype($file['name']);
    $mime_type = $file['type'];
    
    if (!in_array($mime_type, $allowed_types) && !in_array($file_type['type'], $allowed_types)) {
        return array(
            'success' => false,
            'error' => ENLACE_Constants::ERROR_TYPE,
            'message' => 'Type de fichier non autorisé.'
        );
    }
    
    // Validate actual file content (prevent fake extensions)
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return array(
            'success' => false,
            'error' => 'invalid_image',
            'message' => 'Le fichier n\'est pas une image valide.'
        );
    }
    
    // Validate dimensions (optional)
    $max_width = 4000;
    $max_height = 4000;
    if ($image_info[0] > $max_width || $image_info[1] > $max_height) {
        return array(
            'success' => false,
            'error' => 'image_too_large',
            'message' => sprintf('Les dimensions de l\'image sont trop grandes. Maximum : %dx%dpx.', $max_width, $max_height)
        );
    }
    
    return array('success' => true, 'error' => false, 'file_info' => $image_info);
}

/**
 * Handle profile photo upload with standardized return
 */
function enlace_handle_profile_photo_upload($user_id) {
    if (empty($_FILES['profile_photo']['name'])) {
        return array('success' => false, 'error' => false, 'message' => 'No file'); // Optional field
    }
    
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    
    // Validate
    $validation = enlace_validate_and_upload_image($_FILES['profile_photo']);
    if (!$validation['success']) {
        return $validation;
    }
    
    // Handle upload
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($_FILES['profile_photo'], $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        update_user_meta($user_id, ENLACE_Constants::USER_META_PROFILE_PHOTO, esc_url_raw($movefile['url']));
        return array('success' => true, 'error' => false, 'url' => $movefile['url']);
    }
    
    return array(
        'success' => false,
        'error' => ENLACE_Constants::ERROR_UPLOAD,
        'message' => $movefile['error'] ?? 'Erreur lors de l\'upload.'
    );
}

// ============================================================================
// 6. CRÉATION UTILISATEUR OPTIMISÉE
// ============================================================================

/**
 * Create user with comprehensive validation and error handling
 */
function enlace_create_user_with_meta($username, $password, $email, $meta_data = array()) {
    // Validate input
    if (empty($username) || empty($password) || empty($email)) {
        return array(
            'success' => false,
            'error' => 'missing_fields',
            'user_id' => false
        );
    }
    
    // Check if user already exists
    if (username_exists($username)) {
        return array(
            'success' => false,
            'error' => 'username_exists',
            'user_id' => false
        );
    }
    
    if (email_exists($email)) {
        return array(
            'success' => false,
            'error' => 'email_exists',
            'user_id' => false
        );
    }
    
    // Create user
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        return array(
            'success' => false,
            'error' => 'creation_failed',
            'message' => $user_id->get_error_message(),
            'user_id' => false
        );
    }
    
    // Save meta fields
    enlace_save_user_meta_fields($user_id, $meta_data);
    
    // Update display name
    $first_name = isset($meta_data[ENLACE_Constants::USER_META_FIRST_NAME]) ? $meta_data[ENLACE_Constants::USER_META_FIRST_NAME] : '';
    $last_name = isset($meta_data[ENLACE_Constants::USER_META_LAST_NAME]) ? $meta_data[ENLACE_Constants::USER_META_LAST_NAME] : '';
    enlace_update_user_display_name($user_id, $first_name, $last_name);
    
    return array(
        'success' => true,
        'error' => false,
        'user_id' => $user_id
    );
}

/**
 * Save user meta fields using constants
 */
function enlace_save_user_meta_fields($user_id, $data) {
    $fields = array(
        ENLACE_Constants::USER_META_FIRST_NAME,
        ENLACE_Constants::USER_META_LAST_NAME,
        ENLACE_Constants::USER_META_PHONE,
        ENLACE_Constants::USER_META_VILLE,
        ENLACE_Constants::USER_META_SERVICE_TYPE
    );
    
    foreach ($fields as $field) {
        if (isset($data[$field]) && !empty($data[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($data[$field]));
        }
    }
}

/**
 * Update user display name
 */
function enlace_update_user_display_name($user_id, $first_name, $last_name) {
    if (empty($first_name) && empty($last_name)) {
        return;
    }
    
    $display_name = trim($first_name . ' ' . $last_name);
    wp_update_user(array(
        'ID' => $user_id,
        'display_name' => $display_name,
        'first_name' => $first_name,
        'last_name' => $last_name
    ));
}

// ============================================================================
// 7. MAPPINGS CENTRALISÉS
// ============================================================================

/**
 * Get filter labels mapping
 */
function enlace_get_filter_labels_map() {
    return array(
        'beatmaker' => 'Beatmaker / Producteur',
        'chanteur' => 'Chanteur / Chanteuse',
        'organisateur' => 'Organisateur d\'événements',
        'dj' => 'DJ',
        'ingenieur' => 'Ingénieur son',
        'compositeur' => 'Compositeur',
        'musicien' => 'Musicien'
    );
}

/**
 * Get genre labels mapping
 */
function enlace_get_genre_labels_map() {
    return array(
        'homme' => 'Homme',
        'femme' => 'Femme',
        'autre' => 'Autre'
    );
}

/**
 * Map filter values to labels
 */
function enlace_map_filter_labels($filters) {
    $map = enlace_get_filter_labels_map();
    
    if (!is_array($filters) || empty($filters)) {
        return array();
    }
    
    return array_filter(array_map(function($filter) use ($map) {
        return isset($map[$filter]) ? $map[$filter] : null;
    }, $filters));
}

// ============================================================================
// 8. LOGGING
// ============================================================================

/**
 * Log error with context
 */
function enlace_log_error($message, $context = array()) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $log_message = sprintf(
            '[ENLACE] %s',
            $message
        );
        
        if (!empty($context)) {
            $log_message .= ' | Context: ' . json_encode($context);
        }
        
        error_log($log_message);
    }
}

/**
 * Log info message
 */
function enlace_log_info($message, $context = array()) {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        enlace_log_error($message, $context);
    }
}

// ============================================================================
// 9. OPTIMISATION REQUÊTES USER META
// ============================================================================

/**
 * Get all user meta in one query (optimized)
 */
function enlace_get_user_all_meta($user_id) {
    $meta_keys = array(
        ENLACE_Constants::USER_META_FIRST_NAME,
        ENLACE_Constants::USER_META_LAST_NAME,
        ENLACE_Constants::USER_META_PHONE,
        ENLACE_Constants::USER_META_VILLE,
        ENLACE_Constants::USER_META_SERVICE_TYPE,
        ENLACE_Constants::USER_META_PROFILE_PHOTO,
        ENLACE_Constants::USER_META_BIOGRAPHIE,
        ENLACE_Constants::USER_META_GENRE,
        ENLACE_Constants::USER_META_FILTERS,
        ENLACE_Constants::USER_META_MUSIC_GENRES
    );
    
    // Use get_user_meta with single call for multiple keys (WordPress optimization)
    $meta = array();
    foreach ($meta_keys as $key) {
        $meta[$key] = get_user_meta($user_id, $key, true);
    }
    
    return $meta;
}

// ============================================================================
// 10. EXEMPLE D'UTILISATION DANS UNE FONCTION REFACTORISÉE
// ============================================================================

/**
 * Example: handle_user_registration refactored
 */
function enlace_handle_user_registration_refactored() {
    // Early return if not a registration request
    if (!isset($_POST['register_submit']) || !isset($_POST['register_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['register_nonce'], 'register_action')) {
        enlace_log_error('Registration nonce verification failed');
        enlace_redirect_error('/signup', 'nonce_failed');
        return;
    }
    
    // Sanitize and prepare data
    $registration_data = array(
        'user_login' => sanitize_user($_POST['user_login'] ?? ''),
        'user_email' => sanitize_email($_POST['user_email'] ?? ''),
        'user_pass' => $_POST['user_pass'] ?? '',
        'user_pass_confirm' => $_POST['user_pass_confirm'] ?? '',
        ENLACE_Constants::USER_META_FIRST_NAME => sanitize_text_field($_POST['first_name'] ?? ''),
        ENLACE_Constants::USER_META_LAST_NAME => sanitize_text_field($_POST['last_name'] ?? ''),
        ENLACE_Constants::USER_META_PHONE => sanitize_text_field($_POST['phone'] ?? ''),
        ENLACE_Constants::USER_META_VILLE => sanitize_text_field($_POST['ville'] ?? ''),
        ENLACE_Constants::USER_META_SERVICE_TYPE => ($_POST['register_submit'] === ENLACE_Constants::SERVICE_TYPE_OFFER) 
            ? ENLACE_Constants::SERVICE_TYPE_OFFER 
            : ENLACE_Constants::SERVICE_TYPE_SEEK
    );
    
    // Validate
    $validation = enlace_validate_registration($registration_data);
    if (!$validation['valid']) {
        enlace_log_error('Registration validation failed', $validation['errors']);
        enlace_redirect_error('/signup', 'validation_failed', array_keys($validation['errors']));
        return;
    }
    
    // Store in session
    enlace_set_session(ENLACE_Constants::SESSION_REGISTRATION_DATA, $registration_data);
    
    // Redirect based on service type
    $redirect_path = ($registration_data[ENLACE_Constants::USER_META_SERVICE_TYPE] === ENLACE_Constants::SERVICE_TYPE_OFFER)
        ? '/offering-service'
        : '/seeking-service';
    
    enlace_redirect($redirect_path);
}

