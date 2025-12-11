# 🔍 Audit Technique - Code Review functions.php

## 📊 Vue d'ensemble
- **Fichier analysé** : `functions.php` (820 lignes)
- **Nombre de fonctions** : 25+
- **Hooks WordPress** : 15+
- **Problèmes critiques identifiés** : 12
- **Optimisations recommandées** : 20+

---

## 🚨 PROBLÈMES CRITIQUES

### 1. **REDONDANCE : Gestion des erreurs incohérente**

**Problème** : Retours d'erreur inconsistants entre fonctions
```php
// Ligne 106-140 : handle_profile_photo_upload()
return false;        // Pas de fichier
return 'size_error'; // Erreur taille
return 'type_error'; // Erreur type
return true;         // Succès

// Ligne 143-166 : create_user_with_meta()
return false; // Erreur (mais laquelle ?)
return $user_id; // Succès
```

**Pourquoi c'est un problème** :
- Impossible de distinguer les types d'erreurs
- Code appelant doit deviner le type de retour
- Pas de logging des erreurs
- Debugging difficile

**Solution optimisée** :
```php
/**
 * Handle profile photo upload with server-side validation
 * @param int $user_id User ID
 * @return array|false Returns array with 'success' => bool, 'error' => string|false
 */
function handle_profile_photo_upload($user_id) {
    if (empty($_FILES['profile_photo']['name'])) {
        return false; // No file (optional field)
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    
    $uploadedfile = $_FILES['profile_photo'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate file size
    if ($uploadedfile['size'] > $max_size) {
        return array(
            'success' => false,
            'error' => 'size_error',
            'message' => 'La photo est trop grande. Taille maximum : 5MB.'
        );
    }
    
    // Validate file type
    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp');
    $file_type = wp_check_filetype($uploadedfile['name']);
    $mime_type = $uploadedfile['type'];
    
    if (!in_array($mime_type, $allowed_types) && !in_array($file_type['type'], $allowed_types)) {
        return array(
            'success' => false,
            'error' => 'type_error',
            'message' => 'Format de photo non autorisé.'
        );
    }
    
    // Handle upload
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        update_user_meta($user_id, 'profile_photo_url', esc_url_raw($movefile['url']));
        return array('success' => true, 'error' => false);
    }
    
    return array(
        'success' => false,
        'error' => 'upload_error',
        'message' => 'Erreur lors de l\'upload.'
    );
}
```

---

### 2. **SÉCURITÉ : Validation insuffisante des données**

**Problème** : Validation inconsistante et manquante
```php
// Ligne 181-184 : Pas de validation de longueur
$username = sanitize_user($_POST['user_login']);
$email = sanitize_email($_POST['user_email']);
$password = $_POST['user_pass']; // Pas de sanitize (normal) mais pas de validation
$password_confirm = $_POST['user_pass_confirm'];

// Ligne 186 : Validation trop tardive
if ($password !== $password_confirm || username_exists($username) || email_exists($email)) {
    wp_redirect(home_url('/signup?registration=error'));
    exit;
}
```

**Pourquoi c'est un problème** :
- Pas de validation de longueur minimale du mot de passe
- Pas de validation de complexité
- Messages d'erreur génériques
- Pas de validation côté serveur avant traitement

**Solution optimisée** :
```php
/**
 * Validate registration data
 * @param array $data Registration data
 * @return array ['valid' => bool, 'errors' => array]
 */
function validate_registration_data($data) {
    $errors = array();
    
    // Username validation
    if (empty($data['user_login'])) {
        $errors['user_login'] = 'Le nom d\'utilisateur est requis.';
    } elseif (strlen($data['user_login']) < 3) {
        $errors['user_login'] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères.';
    } elseif (username_exists($data['user_login'])) {
        $errors['user_login'] = 'Ce nom d\'utilisateur existe déjà.';
    }
    
    // Email validation
    if (empty($data['user_email'])) {
        $errors['user_email'] = 'L\'email est requis.';
    } elseif (!is_email($data['user_email'])) {
        $errors['user_email'] = 'Format d\'email invalide.';
    } elseif (email_exists($data['user_email'])) {
        $errors['user_email'] = 'Cet email est déjà utilisé.';
    }
    
    // Password validation
    if (empty($data['user_pass'])) {
        $errors['user_pass'] = 'Le mot de passe est requis.';
    } elseif (strlen($data['user_pass']) < 8) {
        $errors['user_pass'] = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($data['user_pass'] !== $data['user_pass_confirm']) {
        $errors['user_pass_confirm'] = 'Les mots de passe ne correspondent pas.';
    }
    
    return array(
        'valid' => empty($errors),
        'errors' => $errors
    );
}
```

---

### 3. **RÉPÉTITION : Code dupliqué pour les redirections**

**Problème** : Même pattern de redirection répété partout
```php
// Répété dans handle_user_registration, handle_profile_update, 
// handle_user_login, handle_production_action, etc.
wp_redirect(home_url('/signup?registration=error'));
exit;
```

**Solution optimisée** :
```php
/**
 * Helper: Safe redirect with query args
 * @param string $path Path relative to home_url
 * @param array $query_args Query arguments
 */
function safe_redirect($path, $query_args = array()) {
    $url = home_url($path);
    if (!empty($query_args)) {
        $url = add_query_arg($query_args, $url);
    }
    wp_safe_redirect($url);
    exit;
}

// Usage
safe_redirect('/signup', array('registration' => 'error', 'message' => 'user_exists'));
```

---

### 4. **COMPLEXITÉ : Fonction get_user_profile_data() trop longue**

**Problème** : Fonction de 75 lignes qui fait trop de choses
```php
// Ligne 470-545 : 75 lignes, mélange récupération et transformation
function get_user_profile_data($user_id = null) {
    // Récupération user
    // Récupération meta
    // Transformation filters
    // Construction array
    // ...
}
```

**Pourquoi c'est un problème** :
- Violation du principe Single Responsibility
- Difficile à tester
- Difficile à maintenir
- Réutilisabilité faible

**Solution optimisée** :
```php
/**
 * Get user basic data
 */
function get_user_basic_data($user_id) {
    $user = get_userdata($user_id);
    if (!$user) {
        return false;
    }
    
    return array(
        'id' => $user_id,
        'username' => $user->user_login,
        'email' => $user->user_email,
        'display_name' => $user->display_name,
        'registered_date' => date_i18n('d/m/Y', strtotime($user->user_registered))
    );
}

/**
 * Get user meta data
 */
function get_user_meta_data($user_id) {
    return array(
        'first_name' => get_user_meta($user_id, 'first_name', true),
        'last_name' => get_user_meta($user_id, 'last_name', true),
        'phone' => get_user_meta($user_id, 'phone', true),
        'ville' => get_user_meta($user_id, 'ville', true),
        'service_type' => get_user_meta($user_id, 'service_type', true) ?: 'offer',
        'profile_photo_url' => get_user_meta($user_id, 'profile_photo_url', true),
        'biographie' => get_user_meta($user_id, 'biographie', true),
        'genre' => get_user_meta($user_id, 'genre', true),
        'filters' => get_user_meta($user_id, 'filters', true),
        'music_genres' => get_user_meta($user_id, 'music_genres', true) ?: array()
    );
}

/**
 * Map filter values to labels
 */
function map_filter_labels($filters) {
    $filter_labels_map = array(
        'beatmaker' => 'Beatmaker / Producteur',
        'chanteur' => 'Chanteur / Chanteuse',
        'organisateur' => 'Organisateur d\'événements',
        'dj' => 'DJ',
        'ingenieur' => 'Ingénieur son',
        'compositeur' => 'Compositeur',
        'musicien' => 'Musicien'
    );
    
    if (!is_array($filters) || empty($filters)) {
        return array();
    }
    
    return array_filter(array_map(function($filter) use ($filter_labels_map) {
        return isset($filter_labels_map[$filter]) ? $filter_labels_map[$filter] : null;
    }, $filters));
}

/**
 * Get complete user profile data
 */
function get_user_profile_data($user_id = null) {
    if (!$user_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        $user_id = get_current_user_id();
    }
    
    $basic_data = get_user_basic_data($user_id);
    if (!$basic_data) {
        return false;
    }
    
    $meta_data = get_user_meta_data($user_id);
    
    // Build full name
    $full_name = trim($meta_data['first_name'] . ' ' . $meta_data['last_name']);
    if (empty($full_name)) {
        $full_name = $basic_data['display_name'];
    }
    
    // Map filters
    $filters_labels = map_filter_labels($meta_data['filters']);
    
    // Get productions
    $productions = get_user_productions($user_id);
    
    return array_merge($basic_data, $meta_data, array(
        'full_name' => $full_name,
        'filters_labels' => $filters_labels,
        'productions' => $productions
    ));
}
```

---

### 5. **MAUVAISE PRATIQUE : Utilisation de $_SESSION sans vérification**

**Problème** : Session utilisée sans vérifier si elle est démarrée
```php
// Ligne 70, 76, 202 : Utilisation directe de $_SESSION
if (!isset($_SESSION['registration_data'])) {
    // ...
}
$_SESSION['registration_data'] = $registration_data;
```

**Pourquoi c'est un problème** :
- Risque d'erreur si session non démarrée
- Pas de gestion d'erreur
- Code fragile

**Solution optimisée** :
```php
/**
 * Get session data safely
 */
function get_session_data($key, $default = null) {
    if (!session_id()) {
        session_start();
    }
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

/**
 * Set session data safely
 */
function set_session_data($key, $value) {
    if (!session_id()) {
        session_start();
    }
    $_SESSION[$key] = $value;
}

/**
 * Clear session data
 */
function clear_session_data($key) {
    if (session_id() && isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}
```

---

### 6. **RÉPÉTITION : Mapping des genres dupliqué**

**Problème** : Mapping des genres répété dans plusieurs fonctions
```php
// Ligne 503-511 : Dans get_user_profile_data()
$filter_labels_map = array(...);

// Ligne 612-616 : Dans show_custom_user_column_data()
$genres = array(
    'homme' => 'Homme',
    'femme' => 'Femme',
    'autre' => 'Autre'
);
```

**Solution optimisée** :
```php
/**
 * Get filter labels mapping
 * @return array
 */
function get_filter_labels_map() {
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
 * @return array
 */
function get_genre_labels_map() {
    return array(
        'homme' => 'Homme',
        'femme' => 'Femme',
        'autre' => 'Autre'
    );
}
```

---

### 7. **BUG POTENTIEL : Variables non initialisées**

**Problème** : Variables utilisées sans vérification
```php
// Ligne 246-248 : $first_name et $last_name peuvent être undefined
if (isset($first_name) && isset($last_name)) {
    $update_data['display_name'] = trim($first_name . ' ' . $last_name);
}
```

**Solution optimisée** :
```php
// Initialiser avant utilisation
$first_name = '';
$last_name = '';

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
if (!empty($first_name) || !empty($last_name)) {
    $update_data['display_name'] = trim($first_name . ' ' . $last_name);
}
```

---

### 8. **SÉCURITÉ : Upload d'image non sécurisé**

**Problème** : Upload sans validation complète
```php
// Ligne 757-779 : handle_annonce_creation()
// Pas de vérification de taille
// Pas de vérification de type MIME réel
// Pas de vérification de dimensions
```

**Solution optimisée** :
```php
/**
 * Validate and upload image
 * @param array $file $_FILES array
 * @param int $max_size Maximum size in bytes
 * @return array|WP_Error
 */
function validate_and_upload_image($file, $max_size = 5242880) {
    // Check if file was uploaded
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return new WP_Error('no_file', 'Aucun fichier uploadé.');
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return new WP_Error('file_too_large', 'Le fichier est trop volumineux.');
    }
    
    // Validate MIME type
    $allowed_mimes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
    $file_type = wp_check_filetype($file['name']);
    $mime_type = $file['type'];
    
    if (!in_array($mime_type, $allowed_mimes) && !in_array($file_type['type'], $allowed_mimes)) {
        return new WP_Error('invalid_type', 'Type de fichier non autorisé.');
    }
    
    // Validate actual file content (prevent fake extensions)
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return new WP_Error('invalid_image', 'Le fichier n\'est pas une image valide.');
    }
    
    // Validate dimensions (optional)
    $max_width = 4000;
    $max_height = 4000;
    if ($image_info[0] > $max_width || $image_info[1] > $max_height) {
        return new WP_Error('image_too_large', 'Les dimensions de l\'image sont trop grandes.');
    }
    
    // Proceed with upload
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($file, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        return $movefile;
    }
    
    return new WP_Error('upload_failed', $movefile['error'] ?? 'Erreur lors de l\'upload.');
}
```

---

### 9. **ORGANISATION : Pas de séparation des responsabilités**

**Problème** : Tout dans un seul fichier de 820 lignes

**Solution optimisée** : Structure modulaire
```
includes/
  ├── class-user-handler.php      # Gestion utilisateurs
  ├── class-session-handler.php    # Gestion sessions
  ├── class-file-upload-handler.php # Gestion uploads
  ├── class-annonce-handler.php    # Gestion annonces
  ├── helpers/
  │   ├── validation-helpers.php
  │   ├── redirect-helpers.php
  │   └── sanitization-helpers.php
  └── constants.php                 # Constantes partagées
```

---

### 10. **PERFORMANCE : Requêtes répétées**

**Problème** : Appels répétés à get_user_meta()
```php
// Ligne 485-494 : 10 appels get_user_meta() dans une boucle
$first_name = get_user_meta($user_id, 'first_name', true);
$last_name = get_user_meta($user_id, 'last_name', true);
// ... 8 autres appels
```

**Solution optimisée** :
```php
/**
 * Get all user meta in one query
 */
function get_user_all_meta($user_id) {
    global $wpdb;
    $meta = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d",
        $user_id
    ), OBJECT_K);
    
    $result = array();
    foreach ($meta as $key => $value) {
        $result[$key] = maybe_unserialize($value->meta_value);
    }
    
    return $result;
}
```

---

### 11. **CODE SMELL : Magic strings partout**

**Problème** : Strings magiques répétées
```php
'service_type' // Répété 20+ fois
'offer' / 'seek' // Répété partout
'registration_data' // Clé de session
```

**Solution optimisée** :
```php
// constants.php
class ENLACE_Constants {
    const SERVICE_TYPE_OFFER = 'offer';
    const SERVICE_TYPE_SEEK = 'seek';
    
    const SESSION_REGISTRATION_DATA = 'registration_data';
    
    const USER_META_SERVICE_TYPE = 'service_type';
    const USER_META_FILTERS = 'filters';
    const USER_META_MUSIC_GENRES = 'music_genres';
    
    const MAX_FILE_SIZE = 5242880; // 5MB
    const ALLOWED_IMAGE_TYPES = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
}
```

---

### 12. **MAUVAISE PRATIQUE : Pas de logging d'erreurs**

**Problème** : Aucun logging pour debug

**Solution optimisée** :
```php
/**
 * Log error with context
 */
function enlace_log_error($message, $context = array()) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log(sprintf(
            '[ENLACE] %s | Context: %s',
            $message,
            json_encode($context)
        ));
    }
}

// Usage
if (is_wp_error($user_id)) {
    enlace_log_error('User creation failed', array(
        'username' => $username,
        'email' => $email,
        'error' => $user_id->get_error_message()
    ));
}
```

---

## 📋 RÉSUMÉ DES OPTIMISATIONS PRIORITAIRES

### 🔴 Critique (À faire immédiatement)
1. ✅ Standardiser les retours d'erreur
2. ✅ Ajouter validation complète des données
3. ✅ Sécuriser les uploads d'images
4. ✅ Corriger les variables non initialisées

### 🟡 Important (À planifier)
5. ✅ Refactoriser get_user_profile_data()
6. ✅ Créer helpers pour redirections
7. ✅ Centraliser les mappings (genres, filters)
8. ✅ Optimiser les requêtes get_user_meta()

### 🟢 Amélioration (Nice to have)
9. ✅ Séparer en modules
10. ✅ Ajouter logging
11. ✅ Utiliser des constantes
12. ✅ Améliorer la documentation

---

## 🎯 Plan d'Action Recommandé

### Phase 1 : Sécurité (1-2 jours)
- Validation complète des données
- Sécurisation des uploads
- Correction des bugs critiques

### Phase 2 : Refactoring (3-5 jours)
- Séparation en modules
- Création des helpers
- Standardisation des retours

### Phase 3 : Optimisation (2-3 jours)
- Optimisation des requêtes
- Ajout du logging
- Documentation

---

*Audit réalisé le : [Date]*
*Tech Lead : [Nom]*

