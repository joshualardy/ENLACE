<?php

/**
 * Theme Functions
 */

// Include icon helper functions
require_once get_template_directory() . '/functions-icons.php';

// Theme setup
function theme_setup()
{
    add_theme_support('post-thumbnails');
    register_nav_menus(array(
        'primary' => 'Menu Principal',
    ));
}
add_action('after_setup_theme', 'theme_setup');

// Helper: Get file version for cache busting
function get_theme_file_version($file_path) {
    $file = get_template_directory() . $file_path;
    return file_exists($file) ? filemtime($file) : '1.0.0';
}

// Enqueue styles and scripts
function theme_scripts()
{
    $theme_uri = get_template_directory_uri();
    
    // Bootstrap CSS (always loaded)
    wp_enqueue_style('bootstrap-css', $theme_uri . '/assets/css/bootstrap.min.css', array(), '5.3.8');
    
    // Main CSS file - Consolidé et optimisé (contient tous les styles)
    wp_enqueue_style('theme-main', $theme_uri . '/assets/css/main.css', array('bootstrap-css'), get_theme_file_version('/assets/css/main.css'));
    
    // Scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    wp_enqueue_script('theme-script', $theme_uri . '/assets/js/main.js', array('jquery'), get_theme_file_version('/assets/js/main.js'), true);
    
    // Vanta.TRUNK scripts (only on login and register pages)
    global $template;
    $template_name = basename($template);
    if ($template_name === 'template-login.php' || $template_name === 'template-register.php') {
        wp_enqueue_script('p5', 'https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.7.0/p5.min.js', array(), '1.7.0', false);
        wp_enqueue_script('vanta-trunk', 'https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.trunk.min.js', array('p5'), '0.5.42', false);
    }
    
    // Localize script for AJAX
    wp_localize_script('theme-script', 'enlaceAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('enlace_messaging'),
        'favorites_nonce' => wp_create_nonce('enlace_favorites'),
        'production_comments_nonce' => wp_create_nonce('enlace_production_comments')
    ));
}
add_action('wp_enqueue_scripts', 'theme_scripts');

// Start session for multi-step registration
function start_registration_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'start_registration_session');

// Helper: Get error messages for registration forms
function get_registration_error_messages() {
    return array(
        'session_expired' => 'Votre session a expiré. Veuillez recommencer l\'inscription.',
        'user_creation_failed' => 'Erreur lors de la création du compte. Veuillez réessayer.',
        'user_already_exists' => 'Un compte avec ce nom d\'utilisateur ou cet email existe déjà.',
        'nonce_failed' => 'Erreur de sécurité. Veuillez réessayer.',
        'photo_too_large' => 'La photo est trop grande. Taille maximum : 5MB.',
        'photo_invalid_type' => 'Format de photo non autorisé. Formats acceptés : JPEG, PNG, GIF, WebP.',
        'first_name' => 'Le prénom est requis.',
        'last_name' => 'Le nom est requis.',
        'user_email' => 'L\'email est requis ou invalide.',
        'user_email_exists' => 'Cet email est déjà utilisé. Veuillez en choisir un autre.',
        'user_pass' => 'Le mot de passe est requis et doit contenir au moins 8 caractères.',
        'user_pass_confirm' => 'Les mots de passe ne correspondent pas.',
        'user_login' => 'Le nom d\'utilisateur est requis.',
        'user_login_exists' => 'Ce nom d\'utilisateur est déjà pris. Veuillez en choisir un autre.',
        'phone' => 'Le numéro de téléphone est requis.',
        'ville' => 'La ville est requise.',
        'service_type' => 'Veuillez choisir si vous offrez ou cherchez un service.'
    );
}

// Helper: Display alert messages (unified function for all templates)
/**
 * Display Bootstrap alert message
 * 
 * @param string $type Alert type: 'success', 'error', 'warning', 'info'
 * @param string $message Alert message
 * @param string $title Optional alert title
 * @param bool $dismissible Whether the alert can be dismissed
 * @return string HTML alert markup
 */
function display_alert_message($type = 'info', $message = '', $title = '', $dismissible = true) {
    if (empty($message)) {
        return '';
    }
    
    // Map types to Bootstrap classes
    $type_map = array(
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    );
    
    $alert_class = isset($type_map[$type]) ? $type_map[$type] : 'alert-info';
    $dismissible_class = $dismissible ? ' alert-dismissible fade show' : '';
    $close_button = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';
    
    $output = '<div class="alert ' . esc_attr($alert_class) . $dismissible_class . '" role="alert">';
    
    if (!empty($title)) {
        $output .= '<strong>' . esc_html($title) . '</strong> ';
    }
    
    $output .= esc_html($message);
    $output .= $close_button;
    $output .= '</div>';
    
    return $output;
}

// Helper: Display registration error messages (backward compatibility)
function display_registration_error_message() {
    if (!isset($_GET['registration']) || $_GET['registration'] !== 'error') {
        return;
    }
    
    $error_messages = get_registration_error_messages();
    $message = isset($_GET['message']) && isset($error_messages[$_GET['message']]) 
        ? $error_messages[$_GET['message']] 
        : 'L\'inscription a échoué. Veuillez vérifier tous les champs requis.';
    
    $error_fields = isset($_GET['fields']) ? explode(',', $_GET['fields']) : array();
    
    // Use new unified function
    echo display_alert_message('error', $message, 'Erreur :');
    
    // Store error fields for JavaScript to highlight
    if (!empty($error_fields)) {
        echo '<script>var formErrors = ' . json_encode($error_fields) . ';</script>';
    }
}

// Helper: Check session and redirect if needed
function check_registration_session($required_service_type = null) {
    if (!isset($_SESSION['registration_data'])) {
        wp_redirect(home_url('/signup'));
        exit;
    }

    if ($required_service_type !== null && 
        (!isset($_SESSION['registration_data']['service_type']) || 
         $_SESSION['registration_data']['service_type'] !== $required_service_type)) {
        wp_redirect(home_url('/signup'));
        exit;
    }
}

// Helper: Save user meta fields
function save_user_meta_fields($user_id, $data) {
    $fields = array('first_name', 'last_name', 'phone', 'ville', 'service_type');
    foreach ($fields as $field) {
        if (isset($data[$field]) && !empty($data[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($data[$field]));
        }
    }
}

// Helper: Update user display name
function update_user_display_name($user_id, $first_name, $last_name) {
    if ($first_name || $last_name) {
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => trim($first_name . ' ' . $last_name),
            'first_name' => $first_name,
            'last_name' => $last_name
        ));
    }
}

// Helper: Handle profile photo upload with server-side validation
function handle_profile_photo_upload($user_id) {
    if (empty($_FILES['profile_photo']['name'])) {
        return false; // No file uploaded (optional field)
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    $uploadedfile = $_FILES['profile_photo'];
    
    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($uploadedfile['size'] > $max_size) {
        return 'size_error';
    }
    
    // Validate file type using WordPress function (checks real mime type)
    $file_type_data = wp_check_filetype_and_ext($uploadedfile['tmp_name'], $uploadedfile['name']);
    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    // Check both mime type and extension
    if (!$file_type_data || !in_array($file_type_data['type'], $allowed_types) || !in_array(strtolower($file_type_data['ext']), $allowed_extensions)) {
        return 'type_error';
    }
    
    // Get old photo URL before upload to delete it later
    $old_photo_url = get_user_meta($user_id, 'profile_photo_url', true);
    
    // Handle upload
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        // Delete old photo file if exists
        if (!empty($old_photo_url)) {
            $old_file_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $old_photo_url);
            if (file_exists($old_file_path) && is_file($old_file_path)) {
                @unlink($old_file_path); // Suppress errors if file doesn't exist
            }
        }
        
        update_user_meta($user_id, 'profile_photo_url', esc_url_raw($movefile['url']));
        return true;
    }
    
    return false;
}

// Helper: Create user and save data
function create_user_with_meta($username, $password, $email, $meta_data = array()) {
    // Check if user already exists
    if (username_exists($username)) {
        return false;
    }
    
    if (email_exists($email)) {
        return false;
    }
    
    $user_id = wp_create_user($username, $password, $email);
    
    if (is_wp_error($user_id)) {
        return false;
    }
    
    save_user_meta_fields($user_id, $meta_data);
    
    $first_name = isset($meta_data['first_name']) ? $meta_data['first_name'] : '';
    $last_name = isset($meta_data['last_name']) ? $meta_data['last_name'] : '';
    update_user_display_name($user_id, $first_name, $last_name);
    
    return $user_id;
}

// Helper: Auto-login user
function auto_login_user($user_id) {
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);
}

// Handle user registration step 1
function handle_user_registration_step1()
{
    if (!isset($_POST['registration_step']) || $_POST['registration_step'] != '1') {
        return;
    }
    
    if (!isset($_POST['register_step1_nonce']) || !wp_verify_nonce($_POST['register_step1_nonce'], 'register_step1_action')) {
        wp_safe_redirect(home_url('/signup?registration=error&message=nonce_failed'));
        exit;
    }

    if (!session_id()) {
        session_start();
    }

    $email = sanitize_email($_POST['user_email']);
    $password = $_POST['user_pass'];
    $password_confirm = $_POST['user_pass_confirm'];
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    // Validation
    $errors = array();
    
    if (empty($first_name)) {
        $errors[] = 'first_name';
    }
    
    if (empty($last_name)) {
        $errors[] = 'last_name';
    }
    
    if (empty($email) || !is_email($email)) {
        $errors[] = 'user_email';
    } elseif (email_exists($email)) {
        $errors[] = 'user_email_exists';
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors[] = 'user_pass';
    }
    
    if ($password !== $password_confirm) {
        $errors[] = 'user_pass_confirm';
    }

    if (!empty($errors)) {
        // Sauvegarder les valeurs POST en session pour les réafficher
        if (!session_id()) {
            session_start();
        }
        $_SESSION['registration_step1_post_data'] = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_email' => $email
        );
        $error_params = 'registration=error&fields=' . implode(',', $errors);
        wp_safe_redirect(home_url('/signup?' . $error_params));
        exit;
    }

    // Save step 1 data to session
    $registration_data = array(
        'first_name' => $first_name,
        'last_name' => $last_name,
        'user_email' => $email,
        'user_pass' => $password,
        'step1_completed' => true
    );

    $_SESSION['registration_data'] = $registration_data;
    
    // Nettoyer les données POST temporaires si elles existent
    if (isset($_SESSION['registration_step1_post_data'])) {
        unset($_SESSION['registration_step1_post_data']);
    }
    
    // Redirect to step 2
    wp_safe_redirect(home_url('/signup-step2'));
    exit;
}
add_action('template_redirect', 'handle_user_registration_step1');

// AJAX: Check username availability
function ajax_check_username() {
    $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
    
    if (empty($username)) {
        wp_send_json_error(array('message' => 'Nom d\'utilisateur requis.'));
        return;
    }
    
    if (strlen($username) < 3) {
        wp_send_json_success(array('exists' => false, 'message' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères.'));
        return;
    }
    
    $exists = username_exists($username);
    
    wp_send_json_success(array('exists' => $exists));
}
add_action('wp_ajax_check_username', 'ajax_check_username');
add_action('wp_ajax_nopriv_check_username', 'ajax_check_username');

// Note: Form processing is now handled directly in template-offering-service.php
// to avoid timing issues with template_redirect hook.

// Handle profile update
function handle_profile_update()
{
    if (!isset($_POST['profile_update_submit']) || !isset($_POST['profile_update_nonce']) || !wp_verify_nonce($_POST['profile_update_nonce'], 'profile_update_action')) {
        return;
    }

    if (!is_user_logged_in()) {
        wp_safe_redirect(home_url('/login'));
        exit;
    }

    $user_id = get_current_user_id();
    $update_data = array('ID' => $user_id);
    $errors = array();

    // Update first name and last name
    if (isset($_POST['first_name'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        update_user_meta($user_id, 'first_name', $first_name);
        $update_data['first_name'] = $first_name;
    }

    if (isset($_POST['last_name'])) {
        $last_name = sanitize_text_field($_POST['last_name']);
        update_user_meta($user_id, 'last_name', $last_name);
        $update_data['last_name'] = $last_name;
    }

    // Update display name
    if (isset($first_name) && isset($last_name)) {
        $update_data['display_name'] = trim($first_name . ' ' . $last_name);
    }

    // Update email if provided and different
    if (isset($_POST['user_email']) && !empty($_POST['user_email'])) {
        $new_email = sanitize_email($_POST['user_email']);
        $current_email = wp_get_current_user()->user_email;
        
        if ($new_email !== $current_email) {
            if (email_exists($new_email)) {
                $errors[] = 'Cet email est déjà utilisé.';
            } else {
                $update_data['user_email'] = $new_email;
            }
        }
    }

    // Execute user update
    if (!empty($update_data)) {
        $result = wp_update_user($update_data);
        if (is_wp_error($result)) {
            $errors[] = 'Erreur lors de la mise à jour des informations.';
        }
    }

    // Update custom fields
    $custom_fields = array('phone', 'ville', 'biographie', 'genre');
    foreach ($custom_fields as $field) {
        if (isset($_POST[$field])) {
            $sanitize_func = $field === 'biographie' ? 'sanitize_textarea_field' : 'sanitize_text_field';
            update_user_meta($user_id, $field, $sanitize_func($_POST[$field]));
        }
    }

    // Handle profile photo upload
    if (!handle_profile_photo_upload($user_id)) {
        if (!empty($_FILES['profile_photo']['name'])) {
            $errors[] = 'Erreur lors de l\'upload de la photo. Format non autorisé.';
        }
    }

    // Handle filters/music genres update based on service type
    $service_type = get_user_meta($user_id, 'service_type', true);
    if ($service_type === 'offer') {
        if (isset($_POST['filters']) && is_array($_POST['filters'])) {
            update_user_meta($user_id, 'filters', array_map('sanitize_text_field', $_POST['filters']));
        } else {
            update_user_meta($user_id, 'filters', array());
        }
    } elseif ($service_type === 'seek') {
        if (isset($_POST['music_genres']) && is_array($_POST['music_genres'])) {
            update_user_meta($user_id, 'music_genres', array_map('sanitize_text_field', $_POST['music_genres']));
        } else {
            update_user_meta($user_id, 'music_genres', array());
        }
    }

    // Redirect with success/error message - check if from settings page
    $redirect_url = home_url('/userprofil');
    $is_from_settings = false;
    
    if (isset($_POST['_wp_http_referer'])) {
        $referer = $_POST['_wp_http_referer'];
        // Check if request came from settings page
        if (strpos($referer, '/settings') !== false || strpos($referer, 'settings') !== false) {
            $redirect_url = home_url('/settings');
            $is_from_settings = true;
            // Preserve section parameter if exists
            $url_parts = parse_url($referer);
            parse_str($url_parts['query'] ?? '', $query_params);
            if (isset($query_params['section'])) {
                $redirect_url = add_query_arg('section', $query_params['section'], $redirect_url);
            }
        } elseif (strpos($referer, 'tab=') !== false) {
            // Preserve tab parameter for profile page
            $url_parts = parse_url($referer);
            parse_str($url_parts['query'] ?? '', $query_params);
            if (isset($query_params['tab'])) {
                $redirect_url = add_query_arg('tab', $query_params['tab'], $redirect_url);
            }
        }
    }
    
    if (empty($errors)) {
        // Save privacy and notification settings
        if (isset($_POST['show_email'])) {
            update_user_meta($user_id, 'show_email', '1');
        } else {
            update_user_meta($user_id, 'show_email', '0');
        }
        
        if (isset($_POST['show_phone'])) {
            update_user_meta($user_id, 'show_phone', '1');
        } else {
            update_user_meta($user_id, 'show_phone', '0');
        }
        
        if (isset($_POST['email_notifications'])) {
            update_user_meta($user_id, 'email_notifications', '1');
        } else {
            update_user_meta($user_id, 'email_notifications', '0');
        }
        
        if (isset($_POST['notify_messages'])) {
            update_user_meta($user_id, 'notify_messages', '1');
        } else {
            update_user_meta($user_id, 'notify_messages', '0');
        }
        
        if (isset($_POST['notify_favorites'])) {
            update_user_meta($user_id, 'notify_favorites', '1');
        } else {
            update_user_meta($user_id, 'notify_favorites', '0');
        }
        
        if (isset($_POST['notify_comments'])) {
            update_user_meta($user_id, 'notify_comments', '1');
        } else {
            update_user_meta($user_id, 'notify_comments', '0');
        }
        
        $redirect_url = add_query_arg('profile_updated', 'success', $redirect_url);
    } else {
        $redirect_url = add_query_arg('profile_updated', 'error', $redirect_url);
    }
    
    wp_redirect($redirect_url);
    exit;
}
add_action('template_redirect', 'handle_profile_update');

// Handle password change
function handle_password_change() {
    if (!isset($_POST['password_change_submit']) || !isset($_POST['password_change_nonce']) || !wp_verify_nonce($_POST['password_change_nonce'], 'password_change_action')) {
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_safe_redirect(home_url('/login'));
        exit;
    }
    
    $user_id = get_current_user_id();
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate current password
    $user = get_userdata($user_id);
    if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
        $redirect_url = home_url('/settings?section=security');
        $redirect_url = add_query_arg('password_change', 'wrong_password', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }
    
    // Validate new password
    if (strlen($new_password) < 8) {
        $redirect_url = home_url('/settings?section=security');
        $redirect_url = add_query_arg('password_change', 'too_short', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }
    
    // Validate password confirmation
    if ($new_password !== $confirm_password) {
        $redirect_url = home_url('/settings?section=security');
        $redirect_url = add_query_arg('password_change', 'mismatch', $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
    }
    
    // Update password
    wp_set_password($new_password, $user_id);
    
    // Update last active
    update_user_meta($user_id, 'last_active', current_time('mysql'));
    
    // Redirect to settings page
    $redirect_url = home_url('/settings?section=security');
    $redirect_url = add_query_arg('password_change', 'success', $redirect_url);
    wp_safe_redirect($redirect_url);
    exit;
}
add_action('template_redirect', 'handle_password_change');

// Handle user login
function handle_user_login()
{
    // Ne s'exécuter que si le formulaire est soumis
    if (!isset($_POST['login_submit']) || !isset($_POST['login_nonce'])) {
        return;
    }
    
    // Vérifier le nonce
    if (!wp_verify_nonce($_POST['login_nonce'], 'login_action')) {
        return;
    }

        $username = sanitize_user($_POST['log']);
        $password = $_POST['pwd'];
    $remember = isset($_POST['rememberme']);

        if (empty($username) || empty($password)) {
        wp_safe_redirect(home_url('/login?login=empty'));
            exit;
        }

        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );

        $user = wp_signon($creds, false);

        if (!is_wp_error($user)) {
        wp_safe_redirect(home_url('/userprofil'));
        } else {
        wp_safe_redirect(home_url('/login?login=failed'));
    }
    exit;
}
add_action('template_redirect', 'handle_user_login');

// Redirect after login (only for WordPress default login, not our custom form)
function redirect_after_login($redirect_to, $request, $user)
{
    // Ne rediriger que si ce n'est pas déjà une redirection personnalisée
    if (!is_wp_error($user) && empty($redirect_to)) {
        return home_url('/userprofil');
    }
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_after_login', 10, 3);

// Get user productions
function get_user_productions($user_id = null) {
    if (!$user_id) {
        if (!is_user_logged_in()) {
            return array();
        }
        $user_id = get_current_user_id();
    }
    
    $productions = get_user_meta($user_id, 'productions', true);
    
    if (!is_array($productions)) {
        return array();
    }
    
    return $productions;
}

// Handle audio/video file upload
function handle_production_media_upload($user_id, $file_key, $allowed_types = array('audio', 'video'), $old_file_url = '') {
    if (empty($_FILES[$file_key]['name'])) {
        return false;
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    
    $uploadedfile = $_FILES[$file_key];
    
    // Validate file size (max 50MB for audio/video)
    $max_size = 50 * 1024 * 1024; // 50MB
    if ($uploadedfile['size'] > $max_size) {
        return 'size_error';
    }
    
    // Validate file type using WordPress function (checks real mime type)
    $file_type_data = wp_check_filetype_and_ext($uploadedfile['tmp_name'], $uploadedfile['name']);
    $allowed_mime_types = array();
    $allowed_extensions = array();
    
    if (in_array('audio', $allowed_types)) {
        $allowed_mime_types = array_merge($allowed_mime_types, array(
            'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg', 'audio/aac', 'audio/flac'
        ));
        $allowed_extensions = array_merge($allowed_extensions, array('mp3', 'wav', 'ogg', 'aac', 'flac', 'mpeg'));
    }
    
    if (in_array('video', $allowed_types)) {
        $allowed_mime_types = array_merge($allowed_mime_types, array(
            'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-msvideo'
        ));
        $allowed_extensions = array_merge($allowed_extensions, array('mp4', 'webm', 'ogg', 'mov', 'avi'));
    }
    
    // Check both mime type and extension
    if (!$file_type_data || !in_array($file_type_data['type'], $allowed_mime_types) || !in_array(strtolower($file_type_data['ext']), $allowed_extensions)) {
        return 'type_error';
    }
    
    // Handle upload
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        // Delete old file if exists and provided
        if (!empty($old_file_url)) {
            $old_file_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $old_file_url);
            if (file_exists($old_file_path) && is_file($old_file_path)) {
                @unlink($old_file_path); // Suppress errors if file doesn't exist
            }
        }
        
        return esc_url_raw($movefile['url']);
    }
    
    return false;
}

// Validate external platform URL
function validate_platform_url($url, $platform) {
    if (empty($url)) {
        return true; // Optional field
    }
    
    $url = esc_url_raw($url);
    
    switch ($platform) {
        case 'soundcloud':
            return (strpos($url, 'soundcloud.com') !== false);
        case 'spotify':
            return (strpos($url, 'spotify.com') !== false || strpos($url, 'open.spotify.com') !== false);
        case 'youtube':
            return (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false);
        default:
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

// Add production to user
function add_user_production($user_id, $production_data) {
    $productions = get_user_productions($user_id);
    
    $new_production = array(
        'id' => uniqid('prod_'),
        'title' => sanitize_text_field($production_data['title']),
        'genre' => sanitize_text_field($production_data['genre']),
        'description' => sanitize_textarea_field($production_data['description']),
        'rating' => isset($production_data['rating']) ? intval($production_data['rating']) : 5,
        'audio_file' => isset($production_data['audio_file']) ? esc_url_raw($production_data['audio_file']) : '',
        'video_file' => isset($production_data['video_file']) ? esc_url_raw($production_data['video_file']) : '',
        'soundcloud_url' => isset($production_data['soundcloud_url']) ? esc_url_raw($production_data['soundcloud_url']) : '',
        'spotify_url' => isset($production_data['spotify_url']) ? esc_url_raw($production_data['spotify_url']) : '',
        'youtube_url' => isset($production_data['youtube_url']) ? esc_url_raw($production_data['youtube_url']) : '',
        'created_at' => current_time('mysql')
    );
    
    $productions[] = $new_production;
    
    update_user_meta($user_id, 'productions', $productions);
    
    return $new_production['id'];
}

// Update production
function update_user_production($user_id, $production_id, $production_data) {
    $productions = get_user_productions($user_id);
    
    foreach ($productions as &$prod) {
        if ($prod['id'] === $production_id) {
            $prod['title'] = sanitize_text_field($production_data['title']);
            $prod['genre'] = sanitize_text_field($production_data['genre']);
            $prod['description'] = sanitize_textarea_field($production_data['description']);
            $prod['rating'] = isset($production_data['rating']) ? intval($production_data['rating']) : $prod['rating'];
            
            if (isset($production_data['audio_file'])) {
                $prod['audio_file'] = esc_url_raw($production_data['audio_file']);
            }
            if (isset($production_data['video_file'])) {
                $prod['video_file'] = esc_url_raw($production_data['video_file']);
            }
            if (isset($production_data['soundcloud_url'])) {
                $prod['soundcloud_url'] = esc_url_raw($production_data['soundcloud_url']);
            }
            if (isset($production_data['spotify_url'])) {
                $prod['spotify_url'] = esc_url_raw($production_data['spotify_url']);
            }
            if (isset($production_data['youtube_url'])) {
                $prod['youtube_url'] = esc_url_raw($production_data['youtube_url']);
            }
            
            break;
        }
    }
    
    update_user_meta($user_id, 'productions', $productions);
    
    return true;
}

// Delete production
function delete_user_production($user_id, $production_id) {
    $productions = get_user_productions($user_id);
    
    $productions = array_filter($productions, function($prod) use ($production_id) {
        return $prod['id'] !== $production_id;
    });
    
    update_user_meta($user_id, 'productions', array_values($productions));
    
    return true;
}

// Handle production add/delete
function handle_production_action() {
    // Ne s'exécuter que si le formulaire est soumis
    if (!isset($_POST['production_action']) || !isset($_POST['production_nonce'])) {
        return;
    }
    
    // Vérifier le nonce d'abord
    if (!wp_verify_nonce($_POST['production_nonce'], 'production_action')) {
        return;
    }
    
    // Vérifier si l'utilisateur est connecté
    if (!is_user_logged_in()) {
        wp_safe_redirect(home_url('/login'));
        exit;
    }
    
    $user_id = get_current_user_id();
    
    // Vérifier que l'utilisateur offre un service (seuls ceux qui offrent peuvent avoir des productions)
    $service_type = get_user_meta($user_id, 'service_type', true);
    if ($service_type !== 'offer') {
        wp_safe_redirect(home_url('/userprofil?production_error=unauthorized'));
        exit;
    }
    
    $action = isset($_POST['production_action']) ? sanitize_text_field($_POST['production_action']) : '';
    
    if ($action === 'add') {
        if (isset($_POST['production_title']) && isset($_POST['production_genre']) && isset($_POST['production_description'])) {
            $production_data = array(
                'title' => sanitize_text_field($_POST['production_title']),
                'genre' => sanitize_text_field($_POST['production_genre']),
                'description' => sanitize_textarea_field($_POST['production_description']),
                'rating' => isset($_POST['production_rating']) ? intval($_POST['production_rating']) : 5,
                'audio_file' => '',
                'video_file' => '',
                'soundcloud_url' => '',
                'spotify_url' => '',
                'youtube_url' => ''
            );
            
            // Handle audio file upload
            if (isset($_FILES['production_audio']) && !empty($_FILES['production_audio']['name'])) {
                $audio_result = handle_production_media_upload($user_id, 'production_audio', array('audio'));
                if ($audio_result && $audio_result !== 'size_error' && $audio_result !== 'type_error') {
                    $production_data['audio_file'] = $audio_result;
                }
            }
            
            // Handle video file upload
            if (isset($_FILES['production_video']) && !empty($_FILES['production_video']['name'])) {
                $video_result = handle_production_media_upload($user_id, 'production_video', array('video'));
                if ($video_result && $video_result !== 'size_error' && $video_result !== 'type_error') {
                    $production_data['video_file'] = $video_result;
                }
            }
            
            // Validate and save external links
            if (isset($_POST['production_soundcloud_url']) && !empty($_POST['production_soundcloud_url'])) {
                if (validate_platform_url($_POST['production_soundcloud_url'], 'soundcloud')) {
                    $production_data['soundcloud_url'] = esc_url_raw($_POST['production_soundcloud_url']);
                }
            }
            
            if (isset($_POST['production_spotify_url']) && !empty($_POST['production_spotify_url'])) {
                if (validate_platform_url($_POST['production_spotify_url'], 'spotify')) {
                    $production_data['spotify_url'] = esc_url_raw($_POST['production_spotify_url']);
                }
            }
            
            if (isset($_POST['production_youtube_url']) && !empty($_POST['production_youtube_url'])) {
                if (validate_platform_url($_POST['production_youtube_url'], 'youtube')) {
                    $production_data['youtube_url'] = esc_url_raw($_POST['production_youtube_url']);
                }
            }
            
            add_user_production($user_id, $production_data);
            
            wp_redirect(home_url('/userprofil?production_added=success'));
        } else {
            wp_redirect(home_url('/userprofil?production_added=error'));
        }
    } elseif ($action === 'edit' && isset($_POST['production_id'])) {
        $production_id = sanitize_text_field($_POST['production_id']);
        $productions = get_user_productions($user_id);
        $existing_production = null;
        
        foreach ($productions as $prod) {
            if ($prod['id'] === $production_id) {
                $existing_production = $prod;
                break;
            }
        }
        
        if ($existing_production && isset($_POST['production_title']) && isset($_POST['production_genre']) && isset($_POST['production_description'])) {
            $production_data = array(
                'title' => sanitize_text_field($_POST['production_title']),
                'genre' => sanitize_text_field($_POST['production_genre']),
                'description' => sanitize_textarea_field($_POST['production_description']),
                'rating' => isset($_POST['production_rating']) ? intval($_POST['production_rating']) : $existing_production['rating'],
                'audio_file' => $existing_production['audio_file'],
                'video_file' => $existing_production['video_file'],
                'soundcloud_url' => isset($_POST['production_soundcloud_url']) ? esc_url_raw($_POST['production_soundcloud_url']) : '',
                'spotify_url' => isset($_POST['production_spotify_url']) ? esc_url_raw($_POST['production_spotify_url']) : '',
                'youtube_url' => isset($_POST['production_youtube_url']) ? esc_url_raw($_POST['production_youtube_url']) : ''
            );
            
            // Handle audio file upload (replace if new file uploaded)
            if (isset($_FILES['production_audio']) && !empty($_FILES['production_audio']['name'])) {
                $old_audio = $existing_production['audio_file'] ?? '';
                $audio_result = handle_production_media_upload($user_id, 'production_audio', array('audio'), $old_audio);
                if ($audio_result && $audio_result !== 'size_error' && $audio_result !== 'type_error') {
                    $production_data['audio_file'] = $audio_result;
                }
            }
            
            // Handle video file upload (replace if new file uploaded)
            if (isset($_FILES['production_video']) && !empty($_FILES['production_video']['name'])) {
                $old_video = $existing_production['video_file'] ?? '';
                $video_result = handle_production_media_upload($user_id, 'production_video', array('video'), $old_video);
                if ($video_result && $video_result !== 'size_error' && $video_result !== 'type_error') {
                    $production_data['video_file'] = $video_result;
                }
            }
            
            // Validate external links
            if (isset($_POST['production_soundcloud_url']) && !empty($_POST['production_soundcloud_url'])) {
                if (validate_platform_url($_POST['production_soundcloud_url'], 'soundcloud')) {
                    $production_data['soundcloud_url'] = esc_url_raw($_POST['production_soundcloud_url']);
                }
            }
            
            if (isset($_POST['production_spotify_url']) && !empty($_POST['production_spotify_url'])) {
                if (validate_platform_url($_POST['production_spotify_url'], 'spotify')) {
                    $production_data['spotify_url'] = esc_url_raw($_POST['production_spotify_url']);
                }
            }
            
            if (isset($_POST['production_youtube_url']) && !empty($_POST['production_youtube_url'])) {
                if (validate_platform_url($_POST['production_youtube_url'], 'youtube')) {
                    $production_data['youtube_url'] = esc_url_raw($_POST['production_youtube_url']);
                }
            }
            
            update_user_production($user_id, $production_id, $production_data);
            wp_redirect(home_url('/userprofil?production_updated=success'));
        } else {
            wp_redirect(home_url('/userprofil?production_updated=error'));
        }
    } elseif ($action === 'delete' && isset($_POST['production_id'])) {
        delete_user_production($user_id, sanitize_text_field($_POST['production_id']));
        wp_redirect(home_url('/userprofil?production_deleted=success'));
    }
    
    exit;
}
add_action('template_redirect', 'handle_production_action');

// Get complete user profile data
function get_user_profile_data($user_id = null)
{
    if (!$user_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        $user_id = get_current_user_id();
    }

    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }

    // Get custom fields
    $first_name = get_user_meta($user_id, 'first_name', true);
    $last_name = get_user_meta($user_id, 'last_name', true);
    $phone = get_user_meta($user_id, 'phone', true);
    $ville = get_user_meta($user_id, 'ville', true);
    $service_type = get_user_meta($user_id, 'service_type', true);
    $profile_photo_url = get_user_meta($user_id, 'profile_photo_url', true);
    $biographie = get_user_meta($user_id, 'biographie', true);
    $genre = get_user_meta($user_id, 'genre', true);
    $filters = get_user_meta($user_id, 'filters', true);
    $music_genres = get_user_meta($user_id, 'music_genres', true);

    // Build full name
    $full_name = trim($first_name . ' ' . $last_name);
    if (empty($full_name)) {
        $full_name = $user->display_name;
    }

    // Map filter values to labels
    $filter_labels_map = array(
        'beatmaker' => 'Beatmaker / Producteur',
        'chanteur' => 'Chanteur / Chanteuse',
        'organisateur' => 'Organisateur d\'événements',
        'dj' => 'DJ',
        'ingenieur' => 'Ingénieur son',
        'compositeur' => 'Compositeur',
        'musicien' => 'Musicien'
    );

    $filters_labels = array();
    if (is_array($filters) && !empty($filters)) {
        foreach ($filters as $filter) {
            if (isset($filter_labels_map[$filter])) {
                $filters_labels[] = $filter_labels_map[$filter];
            }
        }
    }

    // Get productions
    $productions = get_user_productions($user_id);

    return array(
        'id' => $user_id,
        'username' => $user->user_login,
        'email' => $user->user_email,
        'display_name' => $user->display_name,
        'registered_date' => date_i18n('d/m/Y', strtotime($user->user_registered)),
        'first_name' => $first_name,
        'last_name' => $last_name,
        'full_name' => $full_name,
        'phone' => $phone,
        'ville' => $ville,
        'service_type' => $service_type ? $service_type : 'offer',
        'profile_photo_url' => $profile_photo_url,
        'biographie' => $biographie,
        'genre' => $genre,
        'filters' => $filters,
        'filters_labels' => $filters_labels,
        'music_genres' => is_array($music_genres) ? $music_genres : array(),
        'productions' => $productions,
    );
}

// Get user statistics (views, favorites, messages)
function get_user_statistics($user_id = null) {
    if (!$user_id) {
        if (!is_user_logged_in()) {
            return array();
        }
        $user_id = get_current_user_id();
    }
    
    global $wpdb;
    
    $stats = array(
        'profile_views' => 0,
        'favorites_received' => 0,
        'messages_received' => 0,
        'productions_count' => 0,
        'productions_comments' => 0,
        'registered_date' => '',
        'last_active' => ''
    );
    
    // Get profile views (stored in user meta)
    $profile_views = get_user_meta($user_id, 'profile_views', true);
    $stats['profile_views'] = $profile_views ? intval($profile_views) : 0;
    
    // Get favorites received count
    verify_favorites_table();
    $favorites_table = $wpdb->prefix . 'enlace_favorites';
    $favorites_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $favorites_table WHERE item_type = 'user' AND item_id = %d",
        $user_id
    ));
    $stats['favorites_received'] = intval($favorites_count);
    
    // Get messages received count
    verify_messaging_tables();
    $messages_table = $wpdb->prefix . 'enlace_messages';
    $conversations_table = $wpdb->prefix . 'enlace_conversations';
    
    $messages_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $messages_table m
         INNER JOIN $conversations_table c ON m.conversation_id = c.id
         WHERE c.user2_id = %d AND m.sender_id != %d",
        $user_id, $user_id
    ));
    $stats['messages_received'] = intval($messages_count);
    
    // Get productions count
    $productions = get_user_productions($user_id);
    $stats['productions_count'] = count($productions);
    
    // Get productions comments count
    $productions_comments_count = 0;
    foreach ($productions as $production) {
        if (isset($production['comments']) && is_array($production['comments'])) {
            $productions_comments_count += count($production['comments']);
        }
    }
    $stats['productions_comments'] = $productions_comments_count;
    
    // Get registered date
    $user = get_userdata($user_id);
    if ($user) {
        $stats['registered_date'] = date_i18n('d/m/Y', strtotime($user->user_registered));
        $stats['last_active'] = get_user_meta($user_id, 'last_active', true);
        if (!$stats['last_active']) {
            $stats['last_active'] = 'Jamais';
        }
    }
    
    return $stats;
}

// Track profile view (can be called when viewing a profile)
function track_profile_view($user_id) {
    if (!$user_id || $user_id == get_current_user_id()) {
        return; // Don't track own views
    }
    
    $current_views = get_user_meta($user_id, 'profile_views', true);
    $new_views = $current_views ? intval($current_views) + 1 : 1;
    update_user_meta($user_id, 'profile_views', $new_views);
}

// Get user recommendations based on complementary service type, similar genres, and location
function get_user_recommendations($user_id = null, $limit = 20) {
    if (!$user_id) {
        if (!is_user_logged_in()) {
            return array();
        }
        $user_id = get_current_user_id();
    }
    
    // Get current user data
    $current_user_data = get_user_profile_data($user_id);
    if (!$current_user_data) {
        return array();
    }
    
    $current_service_type = $current_user_data['service_type'];
    $current_music_genres = $current_user_data['music_genres'];
    $current_ville = $current_user_data['ville'];
    
    // Determine complementary service type
    $target_service_type = ($current_service_type === 'offer') ? 'seek' : 'offer';
    
    // Get all users except current user
    $args = array(
        'exclude' => array($user_id),
        'number' => 200, // Get more to filter
        'meta_query' => array(
            array(
                'key' => 'service_type',
                'value' => $target_service_type,
                'compare' => '='
            )
        )
    );
    
    $users = get_users($args);
    $recommendations = array();
    $current_user_favorites = get_user_favorites($user_id, 'user');
    $favorited_user_ids = array();
    
    foreach ($current_user_favorites as $fav) {
        $favorited_user_ids[] = $fav->item_id;
    }
    
    foreach ($users as $user) {
        // Skip if already favorited
        if (in_array($user->ID, $favorited_user_ids)) {
            continue;
        }
        
        $user_data = get_user_profile_data($user->ID);
        if (!$user_data) {
            continue;
        }
        
        $score = 0;
        $matching_genres = array();
        
        // Calculate relevance score
        // 1. Match by music genres (if available)
        if (!empty($current_music_genres) && !empty($user_data['music_genres'])) {
            $common_genres = array_intersect($current_music_genres, $user_data['music_genres']);
            $matching_genres = $common_genres;
            $score += count($common_genres) * 10; // 10 points per matching genre
        }
        
        // 2. Match by location (bonus points)
        if (!empty($current_ville) && !empty($user_data['ville'])) {
            if (strtolower($current_ville) === strtolower($user_data['ville'])) {
                $score += 20; // 20 bonus points for same city
            }
        }
        
        // 3. Has profile photo (bonus)
        if (!empty($user_data['profile_photo_url'])) {
            $score += 5;
        }
        
        // 4. Has biography (bonus)
        if (!empty($user_data['biographie'])) {
            $score += 3;
        }
        
        // Only include users with some relevance
        if ($score > 0 || empty($current_music_genres)) {
            $recommendations[] = array(
                'user_data' => $user_data,
                'score' => $score,
                'matching_genres' => $matching_genres
            );
        }
    }
    
    // Sort by score (highest first)
    usort($recommendations, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    // Limit results
    $recommendations = array_slice($recommendations, 0, $limit);
    
    return $recommendations;
}

// Add custom fields to user profile in admin
function add_custom_user_profile_fields($user)
{
?>
    <h3>Informations supplémentaires</h3>
    <table class="form-table">
        <tr>
            <th><label for="genre">Genre</label></th>
            <td>
                <select name="genre" id="genre" class="regular-text">
                    <option value="">Sélectionnez un genre</option>
                    <option value="homme" <?php selected(get_user_meta($user->ID, 'genre', true), 'homme'); ?>>Homme</option>
                    <option value="femme" <?php selected(get_user_meta($user->ID, 'genre', true), 'femme'); ?>>Femme</option>
                    <option value="autre" <?php selected(get_user_meta($user->ID, 'genre', true), 'autre'); ?>>Autre</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="phone">N° de tel</label></th>
            <td>
                <input type="tel" name="phone" id="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="ville">Ville</label></th>
            <td>
                <input type="text" name="ville" id="ville" value="<?php echo esc_attr(get_user_meta($user->ID, 'ville', true)); ?>" class="regular-text" />
            </td>
        </tr>
    </table>
<?php
}
add_action('show_user_profile', 'add_custom_user_profile_fields');
add_action('edit_user_profile', 'add_custom_user_profile_fields');

// Save custom fields in admin
function save_custom_user_profile_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    $fields = array('genre', 'phone', 'ville');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('personal_options_update', 'save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'save_custom_user_profile_fields');

// Add custom columns to users list table
function add_custom_user_columns($columns)
{
    $columns['genre'] = 'Genre';
    $columns['phone'] = 'Téléphone';
    $columns['ville'] = 'Ville';
    return $columns;
}
add_filter('manage_users_columns', 'add_custom_user_columns');

// Display custom column data in users list
function show_custom_user_column_data($value, $column_name, $user_id)
{
    $genres = array(
        'homme' => 'Homme',
        'femme' => 'Femme',
        'autre' => 'Autre'
    );

    switch ($column_name) {
        case 'genre':
            $genre = get_user_meta($user_id, 'genre', true);
            return isset($genres[$genre]) ? $genres[$genre] : ($genre ?: '—');
        case 'phone':
        return get_user_meta($user_id, 'phone', true) ?: '—';
        case 'ville':
            return get_user_meta($user_id, 'ville', true) ?: '—';
    }
    return $value;
}
add_filter('manage_users_custom_column', 'show_custom_user_column_data', 10, 3);

// Register Custom Post Type for Announcements
function register_annonces_post_type() {
    $labels = array(
        'name'                  => 'Annonces',
        'singular_name'         => 'Annonce',
        'menu_name'             => 'Annonces',
        'add_new'               => 'Ajouter une annonce',
        'add_new_item'          => 'Ajouter une nouvelle annonce',
        'edit_item'             => 'Modifier l\'annonce',
        'new_item'              => 'Nouvelle annonce',
        'view_item'             => 'Voir l\'annonce',
        'search_items'          => 'Rechercher des annonces',
        'not_found'             => 'Aucune annonce trouvée',
        'not_found_in_trash'    => 'Aucune annonce trouvée dans la corbeille',
    );

    $args = array(
        'label'                 => 'Annonces',
        'description'           => 'Annonces pour offrir ou chercher des services',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'author'),
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-megaphone',
        'query_var'             => true,
        'rewrite'               => array('slug' => 'annonces'),
        'capability_type'       => 'post',
        'has_archive'           => false,
        'hierarchical'          => false,
        'menu_position'         => 5,
        'show_in_rest'          => false,
    );

    register_post_type('annonce', $args);
}
add_action('init', 'register_annonces_post_type');

// Add custom meta fields for announcements
function add_annonce_meta_boxes() {
    add_meta_box(
        'annonce_details',
        'Détails de l\'annonce',
        'annonce_details_callback',
        'annonce',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_annonce_meta_boxes');

function annonce_details_callback($post) {
    wp_nonce_field('save_annonce_details', 'annonce_details_nonce');
    
    $localisation = get_post_meta($post->ID, '_annonce_localisation', true);
    $service_type = get_post_meta($post->ID, '_annonce_service_type', true);
    
    echo '<table class="form-table">';
    echo '<tr><th><label for="annonce_localisation">Localisation</label></th>';
    echo '<td><input type="text" id="annonce_localisation" name="annonce_localisation" value="' . esc_attr($localisation) . '" class="regular-text" /></td></tr>';
    echo '<tr><th><label for="annonce_service_type">Type de service</label></th>';
    echo '<td><select id="annonce_service_type" name="annonce_service_type">';
    echo '<option value="offer"' . selected($service_type, 'offer', false) . '>J\'offre un service</option>';
    echo '<option value="seek"' . selected($service_type, 'seek', false) . '>Je cherche un service</option>';
    echo '</select></td></tr>';
    echo '</table>';
}

function save_annonce_details($post_id) {
    if (!isset($_POST['annonce_details_nonce']) || !wp_verify_nonce($_POST['annonce_details_nonce'], 'save_annonce_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['annonce_localisation'])) {
        update_post_meta($post_id, '_annonce_localisation', sanitize_text_field($_POST['annonce_localisation']));
    }

    if (isset($_POST['annonce_service_type'])) {
        update_post_meta($post_id, '_annonce_service_type', sanitize_text_field($_POST['annonce_service_type']));
    }
}
add_action('save_post', 'save_annonce_details');

// Handle announcement creation from frontend
function handle_annonce_creation() {
    if (!isset($_POST['create_annonce_submit']) || !isset($_POST['create_annonce_nonce']) || !wp_verify_nonce($_POST['create_annonce_nonce'], 'create_annonce_action')) {
        return;
    }

    if (!is_user_logged_in()) {
        wp_safe_redirect(home_url('/annonces?error=not_logged_in'));
        exit;
    }

    $title = isset($_POST['annonce_title']) ? sanitize_text_field($_POST['annonce_title']) : '';
    $content = isset($_POST['annonce_content']) ? sanitize_textarea_field($_POST['annonce_content']) : '';
    $localisation = isset($_POST['annonce_localisation']) ? sanitize_text_field($_POST['annonce_localisation']) : '';
    $service_type = isset($_POST['annonce_service_type']) ? sanitize_text_field($_POST['annonce_service_type']) : 'offer';

    if (empty($title) || empty($content)) {
        wp_safe_redirect(home_url('/annonces?error=missing_fields'));
        exit;
    }

    $post_data = array(
        'post_title'    => $title,
        'post_content'  => $content,
        'post_status'   => 'publish',
        'post_type'     => 'annonce',
        'post_author'   => get_current_user_id(),
    );

    $post_id = wp_insert_post($post_data);

    if ($post_id && !is_wp_error($post_id)) {
        if (!empty($localisation)) {
            update_post_meta($post_id, '_annonce_localisation', $localisation);
        }
        update_post_meta($post_id, '_annonce_service_type', $service_type);

        // Handle featured image upload
        if (!empty($_FILES['annonce_image']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $uploadedfile = $_FILES['annonce_image'];
            $upload_overrides = array('test_form' => false);
            $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

            if ($movefile && !isset($movefile['error'])) {
                $filename = $movefile['file'];
                $wp_filetype = wp_check_filetype($filename, null);
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
                $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($post_id, $attach_id);
            }
        }

        wp_safe_redirect(home_url('/annonces?success=annonce_created'));
        exit;
    } else {
        wp_safe_redirect(home_url('/annonces?error=creation_failed'));
        exit;
    }
}
add_action('template_redirect', 'handle_annonce_creation');

// Create default pages on theme activation
function create_default_pages() {
    $pages = array(
        'decouvrir' => array(
            'title' => 'Découvrir',
            'template' => 'template-decouvrir.php'
        ),
        'annonces' => array(
            'title' => 'Annonces',
            'template' => 'template-annonces.php'
        ),
        'messagerie' => array(
            'title' => 'Messagerie',
            'template' => 'template-messagerie.php'
        )
    );

    foreach ($pages as $slug => $page_data) {
        if (!get_page_by_path($slug)) {
            $page_id = wp_insert_post(array(
                'post_title'    => $page_data['title'],
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => $slug
            ));
            if ($page_id) {
                update_post_meta($page_id, '_wp_page_template', $page_data['template']);
            }
        }
    }
}
add_action('after_switch_theme', 'create_default_pages');

// ============================================
// MESSAGERIE SYSTEM
// ============================================

// Create messages table on theme activation and verify on init
function create_messages_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enlace_messages';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        conversation_id bigint(20) UNSIGNED NOT NULL,
        sender_id bigint(20) UNSIGNED NOT NULL,
        recipient_id bigint(20) UNSIGNED NOT NULL,
        message text NOT NULL,
        is_read tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY conversation_id (conversation_id),
        KEY sender_id (sender_id),
        KEY recipient_id (recipient_id),
        KEY is_read (is_read),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Create conversations table
    $conversations_table = $wpdb->prefix . 'enlace_conversations';
    $sql_conversations = "CREATE TABLE IF NOT EXISTS $conversations_table (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user1_id bigint(20) UNSIGNED NOT NULL,
        user2_id bigint(20) UNSIGNED NOT NULL,
        last_message_id bigint(20) UNSIGNED DEFAULT NULL,
        last_message_at datetime DEFAULT NULL,
        user1_unread_count int(11) DEFAULT 0,
        user2_unread_count int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_pair (user1_id, user2_id),
        KEY user1_id (user1_id),
        KEY user2_id (user2_id),
        KEY last_message_at (last_message_at)
    ) $charset_collate;";

    dbDelta($sql_conversations);
}
add_action('after_switch_theme', 'create_messages_table');

// Verify tables exist on init (for safety)
function verify_messaging_tables() {
    global $wpdb;
    $messages_table = $wpdb->prefix . 'enlace_messages';
    $conversations_table = $wpdb->prefix . 'enlace_conversations';
    
    // Check if tables exist using prepared statement
    $messages_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $messages_table)) == $messages_table;
    $conversations_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $conversations_table)) == $conversations_table;
    
    if (!$messages_exists || !$conversations_exists) {
        create_messages_table();
    }
}
add_action('init', 'verify_messaging_tables', 1);

// Get or create conversation between two users
function get_or_create_conversation($user1_id, $user2_id) {
    global $wpdb;
    
    // Verify tables exist
    verify_messaging_tables();
    
    $table = $wpdb->prefix . 'enlace_conversations';
    
    // Ensure user1_id < user2_id for consistency
    $normalized_user1 = min($user1_id, $user2_id);
    $normalized_user2 = max($user1_id, $user2_id);
    
    // First, try to find existing conversation
    $conversation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE user1_id = %d AND user2_id = %d",
        $normalized_user1, $normalized_user2
    ));
    
    // If conversation doesn't exist, create it
    if (!$conversation) {
        $result = $wpdb->insert(
            $table,
            array(
                'user1_id' => $normalized_user1,
                'user2_id' => $normalized_user2,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s')
        );
        
        if ($result !== false && $wpdb->insert_id) {
            $conversation_id = $wpdb->insert_id;
            // Retrieve the newly created conversation
            $conversation = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE id = %d",
                $conversation_id
            ));
        }
    }
    
    return $conversation ? $conversation : null;
}

// Send a message
function send_message($sender_id, $recipient_id, $message) {
    global $wpdb;
    
    if (empty($message) || !is_user_logged_in() || $sender_id != get_current_user_id()) {
        return false;
    }
    
    // Get or create conversation
    $conversation = get_or_create_conversation($sender_id, $recipient_id);
    if (!$conversation || !isset($conversation->id)) {
        return false;
    }
    $conversation_id = $conversation->id;
    
    // Insert message
    $messages_table = $wpdb->prefix . 'enlace_messages';
    $result = $wpdb->insert(
        $messages_table,
        array(
            'conversation_id' => $conversation_id,
            'sender_id' => $sender_id,
            'recipient_id' => $recipient_id,
            'message' => sanitize_textarea_field($message),
            'is_read' => 0,
            'created_at' => current_time('mysql')
        ),
        array('%d', '%d', '%d', '%s', '%d', '%s')
    );
    
    if ($result) {
        $message_id = $wpdb->insert_id;
        
        // Update conversation
        $conversations_table = $wpdb->prefix . 'enlace_conversations';
        $is_user1 = ($conversation->user1_id == $sender_id);
        
        $wpdb->update(
            $conversations_table,
            array(
                'last_message_id' => $message_id,
                'last_message_at' => current_time('mysql'),
                ($is_user1 ? 'user2_unread_count' : 'user1_unread_count') => $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $messages_table WHERE conversation_id = %d AND recipient_id = %d AND is_read = 0",
                    $conversation_id,
                    $recipient_id
                ))
            ),
            array('id' => $conversation_id),
            array('%d', '%s', '%d'),
            array('%d')
        );
        
        return $message_id;
    }
    
    return false;
}

// Get messages for a conversation
function get_conversation_messages($conversation_id, $user_id, $limit = 50, $offset = 0) {
    global $wpdb;
    $table = $wpdb->prefix . 'enlace_messages';
    
    // Verify user is part of conversation
    $conversations_table = $wpdb->prefix . 'enlace_conversations';
    $conversation = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $conversations_table WHERE id = %d AND (user1_id = %d OR user2_id = %d)",
        $conversation_id, $user_id, $user_id
    ));
    
    if (!$conversation) {
        return array();
    }
    
    // Mark messages as read
    $wpdb->update(
        $table,
        array('is_read' => 1),
        array(
            'conversation_id' => $conversation_id,
            'recipient_id' => $user_id,
            'is_read' => 0
        ),
        array('%d'),
        array('%d', '%d', '%d')
    );
    
    // Reset unread count
    $is_user1 = ($conversation->user1_id == $user_id);
    $wpdb->update(
        $conversations_table,
        array(($is_user1 ? 'user1_unread_count' : 'user2_unread_count') => 0),
        array('id' => $conversation_id),
        array('%d'),
        array('%d')
    );
    
    // Get messages
    $messages = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
        WHERE conversation_id = %d 
        ORDER BY created_at DESC 
        LIMIT %d OFFSET %d",
        $conversation_id, $limit, $offset
    ));
    
    return array_reverse($messages); // Reverse to show oldest first
}

// Get all conversations for a user
function get_user_conversations($user_id) {
    global $wpdb;
    
    // Verify tables exist
    verify_messaging_tables();
    
    $table = $wpdb->prefix . 'enlace_conversations';
    
    // Order by last_message_at DESC, but put NULL values (new conversations) at the end
    $conversations = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
        WHERE user1_id = %d OR user2_id = %d 
        ORDER BY 
            CASE WHEN last_message_at IS NULL THEN 1 ELSE 0 END,
            last_message_at DESC,
            created_at DESC",
        $user_id, $user_id
    ));
    
    $result = array();
    foreach ($conversations as $conv) {
        $other_user_id = ($conv->user1_id == $user_id) ? $conv->user2_id : $conv->user1_id;
        $other_user = get_userdata($other_user_id);
        
        // Skip if other user doesn't exist
        if (!$other_user) {
            continue;
        }
        
        $profile_data = get_user_profile_data($other_user_id);
        $unread_count = ($conv->user1_id == $user_id) ? $conv->user1_unread_count : $conv->user2_unread_count;
        
        // Get last message text if exists
        $last_message = '';
        if ($conv->last_message_id) {
            $last_message = get_last_message_text($conv->last_message_id);
        }
        
        $result[] = array(
            'id' => $conv->id,
            'other_user_id' => $other_user_id,
            'other_user_name' => $profile_data ? $profile_data['full_name'] : $other_user->display_name,
            'other_user_photo' => $profile_data ? $profile_data['profile_photo_url'] : '',
            'last_message_at' => $conv->last_message_at,
            'unread_count' => $unread_count,
            'last_message' => $last_message
        );
    }
    
    return $result;
}

// Get last message text
function get_last_message_text($message_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'enlace_messages';
    return $wpdb->get_var($wpdb->prepare("SELECT message FROM $table WHERE id = %d", $message_id));
}

// Get unread messages count for user
function get_unread_messages_count($user_id) {
    global $wpdb;
    $conversations_table = $wpdb->prefix . 'enlace_conversations';
    
    $total = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(CASE WHEN user1_id = %d THEN user1_unread_count ELSE user2_unread_count END) 
        FROM $conversations_table 
        WHERE user1_id = %d OR user2_id = %d",
        $user_id, $user_id, $user_id
    ));
    
    return intval($total ? $total : 0);
}

// AJAX: Send message
function ajax_send_message() {
    check_ajax_referer('enlace_messaging', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté.'));
    }
    
    $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (empty($recipient_id) || empty($message)) {
        wp_send_json_error(array('message' => 'Destinataire et message requis.'));
    }
    
    $sender_id = get_current_user_id();
    
    if ($sender_id == $recipient_id) {
        wp_send_json_error(array('message' => 'Vous ne pouvez pas vous envoyer un message.'));
    }
    
    $message_id = send_message($sender_id, $recipient_id, $message);
    
    if ($message_id) {
        wp_send_json_success(array(
            'message_id' => $message_id,
            'message' => 'Message envoyé avec succès.'
        ));
    } else {
        wp_send_json_error(array('message' => 'Erreur lors de l\'envoi du message.'));
    }
}
add_action('wp_ajax_send_message', 'ajax_send_message');

// AJAX: Get messages
function ajax_get_messages() {
    check_ajax_referer('enlace_messaging', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté.'));
    }
    
    $conversation_id = isset($_POST['conversation_id']) ? intval($_POST['conversation_id']) : 0;
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    
    if (empty($conversation_id)) {
        wp_send_json_error(array('message' => 'ID de conversation requis.'));
    }
    
    $user_id = get_current_user_id();
    $messages = get_conversation_messages($conversation_id, $user_id, 50, $offset);
    
    $formatted_messages = array();
    foreach ($messages as $msg) {
        $sender_data = get_user_profile_data($msg->sender_id);
        $formatted_messages[] = array(
            'id' => $msg->id,
            'sender_id' => $msg->sender_id,
            'sender_name' => $sender_data ? $sender_data['full_name'] : get_userdata($msg->sender_id)->display_name,
            'sender_photo' => $sender_data ? $sender_data['profile_photo_url'] : '',
            'message' => $msg->message,
            'is_read' => $msg->is_read,
            'created_at' => $msg->created_at,
            'is_own' => ($msg->sender_id == $user_id)
        );
    }
    
    wp_send_json_success(array('messages' => $formatted_messages));
}
add_action('wp_ajax_get_messages', 'ajax_get_messages');

// AJAX: Get conversations list
function ajax_get_conversations() {
    check_ajax_referer('enlace_messaging', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté.'));
    }
    
    $user_id = get_current_user_id();
    $conversations = get_user_conversations($user_id);
    
    wp_send_json_success(array('conversations' => $conversations));
}
add_action('wp_ajax_get_conversations', 'ajax_get_conversations');

// Search users for messaging
function ajax_search_users_for_messaging() {
    check_ajax_referer('enlace_messaging', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté.'));
    }
    
    $current_user_id = get_current_user_id();
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    if (strlen($query) < 2) {
        wp_send_json_success(array('users' => array()));
    }
    
    // Get all users
    $users_args = array(
        'number' => 20,
        'exclude' => array($current_user_id),
        'search' => '*' . esc_attr($query) . '*',
        'search_columns' => array('user_login', 'user_nicename', 'display_name')
    );
    
    $users = get_users($users_args);
    $results = array();
    
    foreach ($users as $user) {
        $user_id = $user->ID;
        
        // Skip users without profiles
        $service_type = get_user_meta($user_id, 'service_type', true);
        if (empty($service_type)) {
            continue;
        }
        
        $profile_data = get_user_profile_data($user_id);
        if (!$profile_data) {
            continue;
        }
        
        // Check if name matches search query
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $full_name = trim($first_name . ' ' . $last_name);
        $search_text = strtolower($full_name . ' ' . $user->user_login);
        
        if (strpos($search_text, strtolower($query)) === false) {
            continue;
        }
        
        $results[] = array(
            'id' => $user_id,
            'name' => $profile_data['full_name'] ? $profile_data['full_name'] : $user->display_name,
            'photo' => $profile_data['profile_photo_url'] ? $profile_data['profile_photo_url'] : '',
            'location' => $profile_data['ville'] ? $profile_data['ville'] : ''
        );
    }
    
    wp_send_json_success(array('users' => $results));
}
add_action('wp_ajax_search_users_for_messaging', 'ajax_search_users_for_messaging');

// ============================================
// FAVORIS SYSTEM
// ============================================

// Create favorites table on theme activation
function create_favorites_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enlace_favorites';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        item_type varchar(50) NOT NULL,
        item_id bigint(20) UNSIGNED NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_item (user_id, item_type, item_id),
        KEY user_id (user_id),
        KEY item_type (item_type),
        KEY item_id (item_id),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'create_favorites_table');

// Verify favorites table exists on init
function verify_favorites_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'enlace_favorites';
    
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) == $table;
    
    if (!$table_exists) {
        create_favorites_table();
    }
}
add_action('init', 'verify_favorites_table', 1);

// Add favorite
function add_favorite($user_id, $item_type, $item_id) {
    global $wpdb;
    
    verify_favorites_table();
    
    $table = $wpdb->prefix . 'enlace_favorites';
    
    // Validate item_type
    $allowed_types = array('user', 'annonce');
    if (!in_array($item_type, $allowed_types)) {
        return false;
    }
    
    // Check if already favorited
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE user_id = %d AND item_type = %s AND item_id = %d",
        $user_id, $item_type, $item_id
    ));
    
    if ($existing) {
        return true; // Already favorited
    }
    
    // Insert favorite
    $result = $wpdb->insert(
        $table,
        array(
            'user_id' => $user_id,
            'item_type' => $item_type,
            'item_id' => $item_id,
            'created_at' => current_time('mysql')
        ),
        array('%d', '%s', '%d', '%s')
    );
    
    return $result !== false;
}

// Remove favorite
function remove_favorite($user_id, $item_type, $item_id) {
    global $wpdb;
    
    verify_favorites_table();
    
    $table = $wpdb->prefix . 'enlace_favorites';
    
    $result = $wpdb->delete(
        $table,
        array(
            'user_id' => $user_id,
            'item_type' => $item_type,
            'item_id' => $item_id
        ),
        array('%d', '%s', '%d')
    );
    
    return $result !== false;
}

// Check if item is favorited
function is_favorited($user_id, $item_type, $item_id) {
    global $wpdb;
    
    verify_favorites_table();
    
    $table = $wpdb->prefix . 'enlace_favorites';
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE user_id = %d AND item_type = %s AND item_id = %d",
        $user_id, $item_type, $item_id
    ));
    
    return $count > 0;
}

// Get user favorites
function get_user_favorites($user_id, $item_type = null) {
    global $wpdb;
    
    verify_favorites_table();
    
    $table = $wpdb->prefix . 'enlace_favorites';
    
    if ($item_type) {
        $favorites = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND item_type = %s ORDER BY created_at DESC",
            $user_id, $item_type
        ));
    } else {
        $favorites = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }
    
    return $favorites;
}

// Get favorite count for an item
function get_favorite_count($item_type, $item_id) {
    global $wpdb;
    
    verify_favorites_table();
    
    $table = $wpdb->prefix . 'enlace_favorites';
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE item_type = %s AND item_id = %d",
        $item_type, $item_id
    ));
    
    return intval($count);
}

// AJAX: Toggle favorite
function ajax_toggle_favorite() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'enlace_favorites')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité. Veuillez rafraîchir la page.'));
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté.'));
        return;
    }
    
    $user_id = get_current_user_id();
    $item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : '';
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    
    if (empty($item_type) || empty($item_id)) {
        wp_send_json_error(array('message' => 'Paramètres invalides.'));
        return;
    }
    
    // Validate item exists
    if ($item_type === 'user') {
        $item = get_userdata($item_id);
        if (!$item) {
            wp_send_json_error(array('message' => 'Utilisateur introuvable.'));
            return;
        }
        // Don't allow favoriting yourself
        if ($item_id == $user_id) {
            wp_send_json_error(array('message' => 'Vous ne pouvez pas vous ajouter aux favoris.'));
            return;
        }
    } elseif ($item_type === 'annonce') {
        $item = get_post($item_id);
        if (!$item || $item->post_type !== 'annonce') {
            wp_send_json_error(array('message' => 'Annonce introuvable.'));
            return;
        }
    } else {
        wp_send_json_error(array('message' => 'Type invalide.'));
        return;
    }
    
    // Toggle favorite
    $is_favorited = is_favorited($user_id, $item_type, $item_id);
    
    if ($is_favorited) {
        $result = remove_favorite($user_id, $item_type, $item_id);
        $action = 'removed';
    } else {
        $result = add_favorite($user_id, $item_type, $item_id);
        $action = 'added';
    }
    
    if ($result) {
        $new_count = get_favorite_count($item_type, $item_id);
        wp_send_json_success(array(
            'action' => $action,
            'is_favorited' => !$is_favorited,
            'count' => $new_count,
            'message' => $is_favorited ? 'Retiré des favoris' : 'Ajouté aux favoris'
        ));
    } else {
        wp_send_json_error(array('message' => 'Erreur lors de l\'opération.'));
    }
}
add_action('wp_ajax_toggle_favorite', 'ajax_toggle_favorite');

// AJAX: Get favorites list
function ajax_get_favorites() {
    check_ajax_referer('enlace_favorites', 'nonce');
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté.'));
    }
    
    $user_id = get_current_user_id();
    $item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : null;
    
    $favorites = get_user_favorites($user_id, $item_type);
    
    $result = array();
    foreach ($favorites as $fav) {
        if ($fav->item_type === 'user') {
            $user = get_userdata($fav->item_id);
            if ($user) {
                $profile_data = get_user_profile_data($fav->item_id);
                $result[] = array(
                    'id' => $fav->id,
                    'type' => 'user',
                    'item_id' => $fav->item_id,
                    'name' => $profile_data ? $profile_data['full_name'] : $user->display_name,
                    'photo' => $profile_data ? $profile_data['profile_photo_url'] : '',
                    'url' => home_url('/userprofil?user_id=' . $fav->item_id),
                    'created_at' => $fav->created_at
                );
            }
        } elseif ($fav->item_type === 'annonce') {
            $post = get_post($fav->item_id);
            if ($post) {
                $image = get_the_post_thumbnail_url($fav->item_id, 'medium');
                $result[] = array(
                    'id' => $fav->id,
                    'type' => 'annonce',
                    'item_id' => $fav->item_id,
                    'title' => $post->post_title,
                    'description' => wp_trim_words($post->post_content, 20),
                    'image' => $image ? $image : '',
                    'url' => get_permalink($fav->item_id),
                    'created_at' => $fav->created_at
                );
            }
        }
    }
    
    wp_send_json_success(array('favorites' => $result));
}
add_action('wp_ajax_get_favorites', 'ajax_get_favorites');

// ============================================
// PRODUCTION COMMENTS SYSTEM
// ============================================

// Create production comments table on theme activation
function create_production_comments_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'enlace_production_comments';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        production_id varchar(255) NOT NULL,
        user_id bigint(20) UNSIGNED NOT NULL,
        production_owner_id bigint(20) UNSIGNED NOT NULL,
        comment text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY production_id (production_id),
        KEY user_id (user_id),
        KEY production_owner_id (production_owner_id),
        KEY created_at (created_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'create_production_comments_table');

// Verify production comments table exists on init
function verify_production_comments_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'enlace_production_comments';
    
    $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) == $table;
    
    if (!$table_exists) {
        create_production_comments_table();
    }
}
add_action('init', 'verify_production_comments_table', 1);

// Add comment to production
function add_production_comment($production_id, $production_owner_id, $user_id, $comment) {
    global $wpdb;
    
    verify_production_comments_table();
    
    $table = $wpdb->prefix . 'enlace_production_comments';
    
    if (empty($comment) || empty($production_id) || empty($user_id)) {
        return false;
    }
    
    $result = $wpdb->insert(
        $table,
        array(
            'production_id' => sanitize_text_field($production_id),
            'user_id' => $user_id,
            'production_owner_id' => $production_owner_id,
            'comment' => sanitize_textarea_field($comment),
            'created_at' => current_time('mysql')
        ),
        array('%s', '%d', '%d', '%s', '%s')
    );
    
    return $result !== false ? $wpdb->insert_id : false;
}

// Get comments for a production
function get_production_comments($production_id, $limit = 50, $offset = 0) {
    global $wpdb;
    
    verify_production_comments_table();
    
    $table = $wpdb->prefix . 'enlace_production_comments';
    
    $comments = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
        WHERE production_id = %s 
        ORDER BY created_at ASC 
        LIMIT %d OFFSET %d",
        $production_id, $limit, $offset
    ));
    
    $result = array();
    foreach ($comments as $comment) {
        $user = get_userdata($comment->user_id);
        $profile_data = get_user_profile_data($comment->user_id);
        
        $result[] = array(
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'user_name' => $profile_data ? $profile_data['full_name'] : ($user ? $user->display_name : 'Utilisateur'),
            'user_photo' => $profile_data ? $profile_data['profile_photo_url'] : '',
            'comment' => $comment->comment,
            'created_at' => $comment->created_at,
            'is_own' => ($comment->user_id == get_current_user_id())
        );
    }
    
    return $result;
}

// Get comment count for a production
function get_production_comment_count($production_id) {
    global $wpdb;
    
    verify_production_comments_table();
    
    $table = $wpdb->prefix . 'enlace_production_comments';
    
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE production_id = %s",
        $production_id
    ));
    
    return intval($count);
}

// Delete comment
function delete_production_comment($comment_id, $user_id) {
    global $wpdb;
    
    verify_production_comments_table();
    
    $table = $wpdb->prefix . 'enlace_production_comments';
    
    // Verify user owns the comment or is admin
    $comment = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $comment_id
    ));
    
    if (!$comment) {
        return false;
    }
    
    // Only allow deletion if user owns the comment or is the production owner
    if ($comment->user_id != $user_id && $comment->production_owner_id != $user_id) {
        return false;
    }
    
    $result = $wpdb->delete(
        $table,
        array('id' => $comment_id),
        array('%d')
    );
    
    return $result !== false;
}

// AJAX: Add comment to production
function ajax_add_production_comment() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'enlace_production_comments')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité. Veuillez rafraîchir la page.'));
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté.'));
        return;
    }
    
    $user_id = get_current_user_id();
    $production_id = isset($_POST['production_id']) ? sanitize_text_field($_POST['production_id']) : '';
    $production_owner_id = isset($_POST['production_owner_id']) ? intval($_POST['production_owner_id']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    if (empty($production_id) || empty($production_owner_id) || empty($comment)) {
        wp_send_json_error(array('message' => 'Tous les champs sont requis.'));
        return;
    }
    
    // Verify production exists
    $owner_data = get_user_profile_data($production_owner_id);
    if (!$owner_data) {
        wp_send_json_error(array('message' => 'Production introuvable.'));
        return;
    }
    
    $productions = get_user_productions($production_owner_id);
    $production_exists = false;
    foreach ($productions as $prod) {
        if ($prod['id'] === $production_id) {
            $production_exists = true;
            break;
        }
    }
    
    if (!$production_exists) {
        wp_send_json_error(array('message' => 'Production introuvable.'));
        return;
    }
    
    $comment_id = add_production_comment($production_id, $production_owner_id, $user_id, $comment);
    
    if ($comment_id) {
        $user = get_userdata($user_id);
        $profile_data = get_user_profile_data($user_id);
        
        wp_send_json_success(array(
            'comment_id' => $comment_id,
            'comment' => array(
                'id' => $comment_id,
                'user_id' => $user_id,
                'user_name' => $profile_data ? $profile_data['full_name'] : $user->display_name,
                'user_photo' => $profile_data ? $profile_data['profile_photo_url'] : '',
                'comment' => $comment,
                'created_at' => current_time('mysql'),
                'is_own' => true
            ),
            'message' => 'Commentaire ajouté avec succès.'
        ));
    } else {
        wp_send_json_error(array('message' => 'Erreur lors de l\'ajout du commentaire.'));
    }
}
add_action('wp_ajax_add_production_comment', 'ajax_add_production_comment');

// AJAX: Get comments for production
function ajax_get_production_comments() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'enlace_production_comments')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité.'));
        return;
    }
    
    $production_id = isset($_POST['production_id']) ? sanitize_text_field($_POST['production_id']) : '';
    
    if (empty($production_id)) {
        wp_send_json_error(array('message' => 'ID de production requis.'));
        return;
    }
    
    $comments = get_production_comments($production_id);
    
    wp_send_json_success(array('comments' => $comments));
}
add_action('wp_ajax_get_production_comments', 'ajax_get_production_comments');
add_action('wp_ajax_nopriv_get_production_comments', 'ajax_get_production_comments');

// AJAX: Delete comment
function ajax_delete_production_comment() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'enlace_production_comments')) {
        wp_send_json_error(array('message' => 'Erreur de sécurité.'));
        return;
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté.'));
        return;
    }
    
    $user_id = get_current_user_id();
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
    
    if (empty($comment_id)) {
        wp_send_json_error(array('message' => 'ID de commentaire requis.'));
        return;
    }
    
    $result = delete_production_comment($comment_id, $user_id);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Commentaire supprimé avec succès.'));
    } else {
        wp_send_json_error(array('message' => 'Erreur lors de la suppression ou vous n\'avez pas les permissions.'));
    }
}
add_action('wp_ajax_delete_production_comment', 'ajax_delete_production_comment');

// ============================================
// AI CHATBOT SYSTEM
// ============================================

/**
 * Enqueue AI Chatbot scripts and styles
 */
function enqueue_ai_chatbot_assets() {
    $theme_uri = get_template_directory_uri();
    
    // Enqueue CSS
    wp_enqueue_style(
        'ai-chatbot-css',
        $theme_uri . '/assets/css/ai-chatbot.css',
        array(),
        get_theme_file_version('/assets/css/ai-chatbot.css')
    );
    
    // Enqueue JS
    wp_enqueue_script(
        'ai-chatbot-js',
        $theme_uri . '/assets/js/ai-chatbot.js',
        array('jquery'),
        get_theme_file_version('/assets/js/ai-chatbot.js'),
        true
    );
    
    // Localize script with AJAX data
    $theme_uri = get_template_directory_uri();
    wp_localize_script('ai-chatbot-js', 'aiChatbot', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ai_chatbot_nonce'),
        'logoUrl' => $theme_uri . '/assets/images/Logos/logo_blanc.svg'
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_ai_chatbot_assets');

/**
 * Get AI API Key from wp-config.php or WordPress options
 * 
 * @return string|false API key or false if not set
 */
function get_ai_api_key() {
    // First, try to get from wp-config.php constant
    if (defined('AI_API_KEY') && !empty(AI_API_KEY)) {
        return AI_API_KEY;
    }
    
    // Fallback to WordPress options (can be set via admin)
    $api_key = get_option('ai_chatbot_api_key', '');
    
    if (!empty($api_key)) {
        return $api_key;
    }
    
    return false;
}

/**
 * Get AI API URL from wp-config.php or WordPress options
 * 
 * @return string|false API URL or false if not set
 */
function get_ai_api_url() {
    // First, try to get from wp-config.php constant
    if (defined('AI_API_URL') && !empty(AI_API_URL)) {
        return AI_API_URL;
    }
    
    // Fallback to WordPress options
    $api_url = get_option('ai_chatbot_api_url', '');
    
    if (!empty($api_url)) {
        return $api_url;
    }
    
    // Default to OpenAI API if no URL is set
    return 'https://api.openai.com/v1/chat/completions';
}

/**
 * Get system prompt for the AI chatbot
 * Can be customized via WordPress option 'ai_chatbot_system_prompt'
 * 
 * @param string $page_url Current page URL
 * @param string $page_title Current page title
 * @param string $site_lang Site language
 * @return string System prompt
 */
function get_ai_chatbot_system_prompt($page_url = '', $page_title = '', $site_lang = 'fr') {
    $default_prompt = "Tu es un assistant virtuel poli et concis pour un site web. 
Tu dois répondre uniquement sur les sujets liés au site et ses services. 
Si l'utilisateur pose une question hors-sujet, redirige-le poliment vers la page de contact.
Sois utile, professionnel et amical. Réponds en français sauf indication contraire.
Réponds de manière concise (maximum 3-4 phrases).";
    
    // Get custom prompt from options
    $custom_prompt = get_option('ai_chatbot_system_prompt', '');
    
    if (!empty($custom_prompt)) {
        $prompt = $custom_prompt;
    } else {
        $prompt = $default_prompt;
    }
    
    // Add context about current page
    if (!empty($page_url) || !empty($page_title)) {
        $context = "\n\nContexte de la page actuelle :";
        if (!empty($page_title)) {
            $context .= "\n- Titre : " . $page_title;
        }
        if (!empty($page_url)) {
            $context .= "\n- URL : " . $page_url;
        }
        $context .= "\n- Langue : " . $site_lang;
        $prompt .= $context;
    }
    
    return $prompt;
}

/**
 * Rate limiting: Check if IP has exceeded request limit
 * 
 * @param string $ip User IP address
 * @param int $max_requests Maximum requests allowed
 * @param int $time_window Time window in seconds
 * @return array Array with 'allowed' (bool) and 'remaining' (int)
 */
function check_rate_limit($ip, $max_requests = 20, $time_window = 600) {
    $transient_key = 'ai_chatbot_rate_' . md5($ip);
    $requests = get_transient($transient_key);
    
    if ($requests === false) {
        // First request, create new transient
        set_transient($transient_key, 1, $time_window);
        return array('allowed' => true, 'remaining' => $max_requests - 1);
    }
    
    $current_count = intval($requests);
    
    if ($current_count >= $max_requests) {
        return array('allowed' => false, 'remaining' => 0);
    }
    
    // Increment counter
    set_transient($transient_key, $current_count + 1, $time_window);
    
    return array('allowed' => true, 'remaining' => $max_requests - ($current_count + 1));
}

/**
 * Get user IP address (with proxy support)
 * 
 * @return string IP address
 */
function get_user_ip() {
    $ip_keys = array(
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_REAL_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    );
    
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Handle comma-separated IPs (from proxies)
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    // Fallback to REMOTE_ADDR
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

/**
 * Call AI API with messages
 * 
 * @param array $messages Array of message objects with 'role' and 'content'
 * @return array|WP_Error Response array with 'reply' or WP_Error on failure
 */
function call_ai_api($messages) {
    $api_key = get_ai_api_key();
    $api_url = get_ai_api_url();
    
    if (!$api_key) {
        return new WP_Error('no_api_key', 'Clé API non configurée. Veuillez contacter l\'administrateur.');
    }
    
    if (!$api_url) {
        return new WP_Error('no_api_url', 'URL de l\'API non configurée.');
    }
    
    // Prepare request body
    // NOTE: Adaptez ce payload selon l'API que vous utilisez
    // Exemple pour OpenAI GPT
    $body = array(
        'model' => defined('AI_API_MODEL') ? AI_API_MODEL : 'gpt-3.5-turbo',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 500
    );
    
    // Headers
    $headers = array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key
    );
    
    // Log request (only if WP_DEBUG is enabled, and without sensitive data)
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('AI Chatbot: API request to ' . $api_url);
    }
    
    // Make API request
    $response = wp_remote_post($api_url, array(
        'method' => 'POST',
        'timeout' => 30,
        'headers' => $headers,
        'body' => json_encode($body),
        'sslverify' => true
    ));
    
    // Handle errors
    if (is_wp_error($response)) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('AI Chatbot: API error - ' . $response->get_error_message());
        }
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    // Log response code (without body for security)
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log('AI Chatbot: API response code - ' . $response_code);
    }
    
    // Handle HTTP errors
    if ($response_code !== 200) {
        $error_message = 'Erreur API (' . $response_code . ')';
        
        // Try to parse error from response
        $error_data = json_decode($response_body, true);
        if (isset($error_data['error']['message'])) {
            $error_message = $error_data['error']['message'];
        }
        
        return new WP_Error('api_error', $error_message, array('status' => $response_code));
    }
    
    // Parse response
    $data = json_decode($response_body, true);
    
    if (!$data || !isset($data['choices'][0]['message']['content'])) {
        return new WP_Error('invalid_response', 'Réponse invalide de l\'API.');
    }
    
    $reply = trim($data['choices'][0]['message']['content']);
    
    return array('reply' => $reply);
}

/**
 * AJAX Handler: Send message to AI chatbot
 */
function ajax_ai_chatbot_send() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ai_chatbot_nonce')) {
        wp_send_json_error(array('error' => 'Erreur de sécurité. Veuillez rafraîchir la page.'));
        return;
    }
    
    // Get and sanitize input
    $message = isset($_POST['message']) ? sanitize_text_field(trim($_POST['message'])) : '';
    $page_url = isset($_POST['page_url']) ? esc_url_raw($_POST['page_url']) : '';
    $page_title = isset($_POST['page_title']) ? sanitize_text_field($_POST['page_title']) : '';
    $site_lang = isset($_POST['site_lang']) ? sanitize_text_field($_POST['site_lang']) : 'fr';
    $history_json = isset($_POST['history']) ? $_POST['history'] : '[]';
    
    // Validate message
    if (empty($message)) {
        wp_send_json_error(array('error' => 'Le message ne peut pas être vide.'));
        return;
    }
    
    // Check message length (800 characters max)
    if (strlen($message) > 800) {
        wp_send_json_error(array('error' => 'Le message est trop long (maximum 800 caractères).'));
        return;
    }
    
    // Rate limiting
    $user_ip = get_user_ip();
    $rate_limit = check_rate_limit($user_ip, 20, 600); // 20 requests per 10 minutes
    
    if (!$rate_limit['allowed']) {
        wp_send_json_error(array(
            'error' => 'Trop de requêtes. Veuillez patienter quelques instants avant de réessayer.',
            'code' => 'rate_limit_exceeded'
        ));
        return;
    }
    
    // Anti-spam: Check for suspicious patterns
    $spam_patterns = array(
        '/http[s]?:\/\/[^\s]+/i', // URLs
        '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', // Email addresses
        '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/', // Credit card patterns
    );
    
    $spam_score = 0;
    foreach ($spam_patterns as $pattern) {
        if (preg_match($pattern, $message)) {
            $spam_score++;
        }
    }
    
    // If message contains multiple spam indicators, reject
    if ($spam_score >= 2) {
        wp_send_json_error(array('error' => 'Message rejeté pour des raisons de sécurité.'));
        return;
    }
    
    // Get system prompt
    $system_prompt = get_ai_chatbot_system_prompt($page_url, $page_title, $site_lang);
    
    // Build messages array for API
    $messages = array();
    
    // Add system message
    $messages[] = array(
        'role' => 'system',
        'content' => $system_prompt
    );
    
    // Parse and add conversation history (last 10 messages)
    try {
        $history = json_decode(stripslashes($history_json), true);
        if (is_array($history)) {
            foreach ($history as $msg) {
                if (isset($msg['type']) && isset($msg['content'])) {
                    $role = ($msg['type'] === 'user') ? 'user' : 'assistant';
                    $messages[] = array(
                        'role' => $role,
                        'content' => sanitize_text_field($msg['content'])
                    );
                }
            }
        }
    } catch (Exception $e) {
        // If history parsing fails, continue without it
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('AI Chatbot: Error parsing history - ' . $e->getMessage());
        }
    }
    
    // Add current user message
    $messages[] = array(
        'role' => 'user',
        'content' => $message
    );
    
    // Call AI API
    $api_response = call_ai_api($messages);
    
    if (is_wp_error($api_response)) {
        $error_message = $api_response->get_error_message();
        
        // Don't expose internal errors to users
        if (strpos($error_message, 'Clé API') !== false || strpos($error_message, 'configurée') !== false) {
            $error_message = 'Service temporairement indisponible. Veuillez réessayer plus tard.';
        }
        
        wp_send_json_error(array('error' => $error_message));
        return;
    }
    
    // Sanitize and escape the reply
    $reply = isset($api_response['reply']) ? $api_response['reply'] : '';
    $reply = wp_kses_post($reply); // Allow safe HTML
    $reply = trim($reply);
    
    if (empty($reply)) {
        wp_send_json_error(array('error' => 'Aucune réponse reçue de l\'assistant.'));
        return;
    }
    
    // Success response
    wp_send_json_success(array('reply' => $reply));
}
add_action('wp_ajax_ai_chatbot_send', 'ajax_ai_chatbot_send');
add_action('wp_ajax_nopriv_ai_chatbot_send', 'ajax_ai_chatbot_send');

