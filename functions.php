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
    wp_enqueue_style('bootstrap-css', get_template_directory_uri() . '/assets/css/bootstrap.min.css', array(), '5.3.8');
    wp_enqueue_style('theme-style', get_template_directory_uri() . '/assets/css/main.css', array('bootstrap-css'), '1.0.0');
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    wp_enqueue_script('theme-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true);
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
