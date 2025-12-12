<?php

/**
 * Theme Functions
 */

// Theme setup
function theme_setup()
{
    add_theme_support('post-thumbnails');
    register_nav_menus(array(
        'primary' => 'Menu Principal',
    ));
}
add_action('after_setup_theme', 'theme_setup');

// Enqueue styles and scripts
function theme_scripts()
{
    // Bootstrap CSS (always loaded)
    wp_enqueue_style('bootstrap-css', get_template_directory_uri() . '/assets/css/bootstrap.min.css', array(), '5.3.8');
    
    // Base CSS files (always loaded)
    wp_enqueue_style('theme-variables', get_template_directory_uri() . '/assets/css/variables.css', array(), '1.0.0');
    wp_enqueue_style('theme-base', get_template_directory_uri() . '/assets/css/base.css', array('theme-variables'), '1.0.0');
    wp_enqueue_style('theme-header', get_template_directory_uri() . '/assets/css/header.css', array('theme-base'), '1.0.0');
    wp_enqueue_style('theme-footer', get_template_directory_uri() . '/assets/css/footer.css', array('theme-base'), '1.0.0');
    wp_enqueue_style('theme-common', get_template_directory_uri() . '/assets/css/common.css', array('theme-base'), '1.0.0');
    
    // Page-specific CSS
    if (is_front_page() || is_home()) {
        wp_enqueue_style('theme-front-page', get_template_directory_uri() . '/assets/css/front-page.css', array('theme-common'), '1.0.0');
    }
    
    // Check if we're on a template page
    $template = get_page_template_slug();
    if ($template === 'template-login.php') {
        wp_enqueue_style('theme-login', get_template_directory_uri() . '/assets/css/login.css', array('theme-common'), '1.0.0');
    } elseif ($template === 'template-register.php') {
        wp_enqueue_style('theme-register', get_template_directory_uri() . '/assets/css/register.css', array('theme-common'), '1.0.0');
    } elseif ($template === 'template-offering-service.php' || $template === 'template-seeking-service.php') {
        wp_enqueue_style('theme-service-forms', get_template_directory_uri() . '/assets/css/service-forms.css', array('theme-common'), '1.0.0');
    } elseif ($template === 'template-userprofil.php') {
        wp_enqueue_style('theme-userprofil', get_template_directory_uri() . '/assets/css/userprofil.css', array('theme-common'), '1.0.0');
    } elseif ($template === 'template-annonces.php') {
        wp_enqueue_style('theme-annonces', get_template_directory_uri() . '/assets/css/annonces.css', array('theme-common'), '1.0.0');
    } elseif ($template === 'template-decouvrir.php') {
        wp_enqueue_style('theme-decouvrir', get_template_directory_uri() . '/assets/css/decouvrir.css', array('theme-common'), '1.0.0');
    } elseif ($template === 'template-messagerie.php') {
        wp_enqueue_style('theme-messagerie', get_template_directory_uri() . '/assets/css/messagerie.css', array('theme-common'), '1.0.0');
    }
    
    // Scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    wp_enqueue_script('theme-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('theme-script', 'enlaceAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('enlace_messaging')
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
        'photo_invalid_type' => 'Format de photo non autorisé. Formats acceptés : JPEG, PNG, GIF, WebP.'
    );
}

// Helper: Display registration error messages
function display_registration_error_message() {
    if (!isset($_GET['registration']) || $_GET['registration'] !== 'error') {
        return;
    }
    
    $error_messages = get_registration_error_messages();
    $message = isset($_GET['message']) && isset($error_messages[$_GET['message']]) 
        ? $error_messages[$_GET['message']] 
        : 'L\'inscription a échoué. Veuillez vérifier tous les champs requis.';
    
    $error_fields = isset($_GET['fields']) ? explode(',', $_GET['fields']) : array();
    echo '<div class="error-message fixed">' . esc_html($message) . '</div>';
    
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
    
    $uploadedfile = $_FILES['profile_photo'];
    
    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($uploadedfile['size'] > $max_size) {
        return 'size_error';
    }
    
    // Validate file type
    $upload_overrides = array('test_form' => false);
    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
    $file_type = wp_check_filetype($uploadedfile['name']);
    $mime_type = $uploadedfile['type'];
    
    if (!in_array($mime_type, $allowed_types) && !in_array($file_type['type'], $allowed_types)) {
        return 'type_error';
    }
    
    // Handle upload
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
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
function handle_user_registration()
{
    if (!isset($_POST['register_submit']) || !isset($_POST['register_nonce']) || !wp_verify_nonce($_POST['register_nonce'], 'register_action')) {
        return;
    }

    $username = sanitize_user($_POST['user_login']);
    $email = sanitize_email($_POST['user_email']);
    $password = $_POST['user_pass'];
    $password_confirm = $_POST['user_pass_confirm'];

    if ($password !== $password_confirm || username_exists($username) || email_exists($email)) {
            wp_redirect(home_url('/signup?registration=error'));
            exit;
        }

    $registration_data = array(
        'user_login' => $username,
        'user_email' => $email,
        'user_pass' => $password,
        'first_name' => isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '',
        'last_name' => isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '',
        'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
        'ville' => isset($_POST['ville']) ? sanitize_text_field($_POST['ville']) : '',
        'service_type' => $_POST['register_submit'] === 'offer' ? 'offer' : 'seek'
    );

    $_SESSION['registration_data'] = $registration_data;
    
    if ($_POST['register_submit'] === 'offer') {
        wp_redirect(home_url('/offering-service'));
    } else {
        wp_redirect(home_url('/seeking-service'));
    }
    exit;
}
add_action('template_redirect', 'handle_user_registration');

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

    // Redirect with success/error message
    $redirect_url = home_url('/userprofil');
    if (empty($errors)) {
        $redirect_url = add_query_arg('profile_updated', 'success', $redirect_url);
    } else {
        $redirect_url = add_query_arg('profile_updated', 'error', $redirect_url);
    }
    
    wp_redirect($redirect_url);
    exit;
}
add_action('template_redirect', 'handle_profile_update');

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

// Add production to user
function add_user_production($user_id, $production_data) {
    $productions = get_user_productions($user_id);
    
    $new_production = array(
        'id' => uniqid('prod_'),
        'title' => sanitize_text_field($production_data['title']),
        'genre' => sanitize_text_field($production_data['genre']),
        'description' => sanitize_textarea_field($production_data['description']),
        'rating' => isset($production_data['rating']) ? intval($production_data['rating']) : 5,
        'created_at' => current_time('mysql')
    );
    
    $productions[] = $new_production;
    
    update_user_meta($user_id, 'productions', $productions);
    
    return $new_production['id'];
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
    
    $action = $_POST['production_action'];
    
    if ($action === 'add') {
        if (isset($_POST['production_title']) && isset($_POST['production_genre']) && isset($_POST['production_description'])) {
            add_user_production($user_id, array(
                'title' => $_POST['production_title'],
                'genre' => $_POST['production_genre'],
                'description' => $_POST['production_description'],
                'rating' => isset($_POST['production_rating']) ? $_POST['production_rating'] : 5
            ));
            
            wp_redirect(home_url('/userprofil?production_added=success'));
        } else {
            wp_redirect(home_url('/userprofil?production_added=error'));
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
    
    // Check if tables exist
    $messages_exists = $wpdb->get_var("SHOW TABLES LIKE '$messages_table'") == $messages_table;
    $conversations_exists = $wpdb->get_var("SHOW TABLES LIKE '$conversations_table'") == $conversations_table;
    
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

