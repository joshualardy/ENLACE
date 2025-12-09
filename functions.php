<?php

/**
 * Theme Functions
 */

// Theme setup
function theme_setup()
{
    add_theme_support('post-thumbnails');
    
    // Register navigation menu
    register_nav_menus(array(
        'primary' => 'Menu Principal',
    ));
}
add_action('after_setup_theme', 'theme_setup');

// Enqueue styles and scripts
function theme_scripts()
{
    // Bootstrap CSS (local file)
    wp_enqueue_style('bootstrap-css', get_template_directory_uri() . '/assets/css/bootstrap.min.css', array(), '5.3.8');
    
    // Theme CSS
    wp_enqueue_style('theme-style', get_template_directory_uri() . '/assets/css/main.css', array('bootstrap-css'), '1.0.0');
    
    // jQuery (required for Bootstrap)
    wp_enqueue_script('jquery');
    
    // Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.0', true);
    
    // Theme JS
    wp_enqueue_script('theme-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'theme_scripts');

// Handle user registration
function handle_user_registration()
{
    if (isset($_POST['register_submit']) && isset($_POST['register_nonce']) && wp_verify_nonce($_POST['register_nonce'], 'register_action')) {
        $username = sanitize_user($_POST['user_login']);
        $email = sanitize_email($_POST['user_email']);
        $password = $_POST['user_pass'];
        $password_confirm = $_POST['user_pass_confirm'];

        if ($password !== $password_confirm) {
            wp_redirect(home_url('/signup?registration=error'));
            exit;
        }

        $user_id = wp_create_user($username, $password, $email);

        if (!is_wp_error($user_id)) {
            // Save custom fields as user meta
            if (isset($_POST['first_name'])) {
                update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
            }
            if (isset($_POST['last_name'])) {
                update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
            }
            if (isset($_POST['genre'])) {
                update_user_meta($user_id, 'genre', sanitize_text_field($_POST['genre']));
            }
            if (isset($_POST['register_submit'])) {
                $service_type = $_POST['register_submit'] === 'offer' ? 'offer' : 'seek';
                update_user_meta($user_id, 'service_type', $service_type);
            }
            if (isset($_POST['phone'])) {
                update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
            }
            if (isset($_POST['ville'])) {
                update_user_meta($user_id, 'ville', sanitize_text_field($_POST['ville']));
            }

            // Update display name
            $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
            $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
            if ($first_name || $last_name) {
                wp_update_user(array(
                    'ID' => $user_id,
                    'display_name' => trim($first_name . ' ' . $last_name),
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ));
            }

            wp_redirect(home_url('/signup?registration=success'));
            exit;
        } else {
            wp_redirect(home_url('/signup?registration=error'));
            exit;
        }
    }
}
add_action('template_redirect', 'handle_user_registration');

// Handle user login
function handle_user_login()
{
    if (isset($_POST['login_submit']) && isset($_POST['login_nonce']) && wp_verify_nonce($_POST['login_nonce'], 'login_action')) {
        $username = sanitize_user($_POST['log']);
        $password = $_POST['pwd'];
        $remember = isset($_POST['rememberme']) ? true : false;

        if (empty($username) || empty($password)) {
            wp_redirect(home_url('/login?login=empty'));
            exit;
        }

        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );

        $user = wp_signon($creds, false);

        if (!is_wp_error($user)) {
            wp_redirect(home_url());
            exit;
        } else {
            wp_redirect(home_url('/login?login=failed'));
            exit;
        }
    }
}
add_action('template_redirect', 'handle_user_login');

// Redirect after login
function redirect_after_login($redirect_to, $request, $user)
{
    if (!is_wp_error($user)) {
        return home_url();
    }
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_after_login', 10, 3);

// Helper function to get user custom field
function get_user_custom_field($user_id, $field_name)
{
    return get_user_meta($user_id, $field_name, true);
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

    if (isset($_POST['genre'])) {
        update_user_meta($user_id, 'genre', sanitize_text_field($_POST['genre']));
    }
    if (isset($_POST['phone'])) {
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
    }
    if (isset($_POST['ville'])) {
        update_user_meta($user_id, 'ville', sanitize_text_field($_POST['ville']));
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
    if ($column_name == 'genre') {
        $genre = get_user_meta($user_id, 'genre', true);
        $genres = array(
            'homme' => 'Homme',
            'femme' => 'Femme',
            'autre' => 'Autre'
        );
        return isset($genres[$genre]) ? $genres[$genre] : ($genre ?: '—');
    }
    if ($column_name == 'phone') {
        return get_user_meta($user_id, 'phone', true) ?: '—';
    }
    if ($column_name == 'ville') {
        return get_user_meta($user_id, 'ville', true) ?: '—';
    }
    return $value;
}
add_filter('manage_users_custom_column', 'show_custom_user_column_data', 10, 3);

// Create default pages on theme activation
function create_default_pages() {
    // Check if pages already exist
    $decouvrir_page = get_page_by_path('decouvrir');
    $annonces_page = get_page_by_path('annonces');
    
    // Create Découvrir page
    if (!$decouvrir_page) {
        $decouvrir = array(
            'post_title'    => 'Découvrir',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'decouvrir'
        );
        $decouvrir_id = wp_insert_post($decouvrir);
        if ($decouvrir_id) {
            update_post_meta($decouvrir_id, '_wp_page_template', 'template-decouvrir.php');
        }
    }
    
    // Create Annonces page
    if (!$annonces_page) {
        $annonces = array(
            'post_title'    => 'Annonces',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'annonces'
        );
        $annonces_id = wp_insert_post($annonces);
        if ($annonces_id) {
            update_post_meta($annonces_id, '_wp_page_template', 'template-annonces.php');
        }
    }
}
add_action('after_switch_theme', 'create_default_pages');
