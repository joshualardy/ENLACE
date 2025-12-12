<?php

/**
 * Template Name: Découvrir Template
 */

get_header();

// Get filter parameters from URL
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$ville_filter = isset($_GET['ville']) ? sanitize_text_field($_GET['ville']) : '';
$talent_filter = isset($_GET['talent']) ? sanitize_text_field($_GET['talent']) : '';

// Parse talent filter (format: "service:beatmaker" or "genre:Pop")
$filter_value = '';
if (!empty($talent_filter)) {
    $parts = explode(':', $talent_filter, 2);
    if (count($parts) === 2) {
        $filter_value = $parts[1];
    }
} else {
    // Fallback for old URL format
    $filter_value = isset($_GET['filter_value']) ? sanitize_text_field($_GET['filter_value']) : '';
}

// Get all users with profiles
$users_args = array(
    'number' => -1,
    'orderby' => 'registered',
    'order' => 'DESC'
);

$all_users = get_users($users_args);
$filtered_users = array();

// Filter users based on criteria
foreach ($all_users as $user) {
    $user_id = $user->ID;
    
    // Skip if user doesn't have a profile (no service_type)
    $service_type = get_user_meta($user_id, 'service_type', true);
    if (empty($service_type)) {
        continue;
    }
    
    // Filter by search query (name, username, biographie)
    if (!empty($search_query)) {
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $full_name = trim($first_name . ' ' . $last_name);
        $biographie = get_user_meta($user_id, 'biographie', true);
        $username = $user->user_login;
        $display_name = $user->display_name;
        
        $search_in = strtolower($full_name . ' ' . $username . ' ' . $display_name . ' ' . $biographie);
        if (strpos($search_in, strtolower($search_query)) === false) {
            continue;
        }
    }
    
    // Filter by ville
    if (!empty($ville_filter)) {
        $ville = get_user_meta($user_id, 'ville', true);
        if (empty($ville) || strpos(strtolower($ville), strtolower($ville_filter)) === false) {
            continue;
        }
    }
    
    // Filter by talent (service or genre) - no service_type distinction needed
    if (!empty($filter_value)) {
        // Check if it's a service filter
        $filters = get_user_meta($user_id, 'filters', true);
        $has_service = is_array($filters) && in_array($filter_value, $filters);
        
        // Check if it's a genre filter
        $music_genres = get_user_meta($user_id, 'music_genres', true);
        $has_genre = is_array($music_genres) && in_array($filter_value, $music_genres);
        
        // User must have either the service or the genre
        if (!$has_service && !$has_genre) {
            continue;
        }
    }
    
    $filtered_users[] = $user;
}

// Get unique villes for filter dropdown
$all_villes = array();
foreach ($all_users as $user) {
    $ville = get_user_meta($user->ID, 'ville', true);
    if (!empty($ville)) {
        $all_villes[] = $ville;
    }
}
$unique_villes = array_unique($all_villes);
sort($unique_villes);
?>

<div class="decouvrir-page">
    <div class="container">
        <!-- Page Title -->
        <h1 class="decouvrir-page-title">Découvrir</h1>
        <p class="decouvrir-page-subtitle">Trouve les talents qui correspondent à tes projets</p>

        <!-- Search and Filters Section -->
        <div class="decouvrir-filters-section">
            <!-- Search Bar -->
            <div class="decouvrir-search-wrapper">
                <form method="get" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="decouvrir-search-form">
                    <?php if (!empty($ville_filter)) : ?>
                        <input type="hidden" name="ville" value="<?php echo esc_attr($ville_filter); ?>">
                    <?php endif; ?>
                    <?php if (!empty($filter_value)) : ?>
                        <input type="hidden" name="filter_value" value="<?php echo esc_attr($filter_value); ?>">
                    <?php endif; ?>
                    <div class="decouvrir-search-input-wrapper">
                        <svg class="decouvrir-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input type="text" name="search" class="decouvrir-search-input" placeholder="Rechercher un nom, un talent..." value="<?php echo esc_attr($search_query); ?>">
                        <?php if (!empty($search_query)) : ?>
                            <button type="button" class="decouvrir-search-clear" onclick="this.form.search.value=''; this.form.submit();">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Filters Row -->
            <div class="decouvrir-filters-row">
                <!-- Ville Filter -->
                <div class="decouvrir-filter-group">
                    <label class="decouvrir-filter-label">Ville</label>
                    <select name="ville" class="decouvrir-filter-select" onchange="updateFilter('ville', this.value)">
                        <option value="">Toutes les villes</option>
                        <?php foreach ($unique_villes as $ville) : ?>
                            <option value="<?php echo esc_attr($ville); ?>" <?php selected($ville_filter, $ville); ?>>
                                <?php echo esc_html($ville); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Talent/Specialty Filter (combines services and genres) -->
                <div class="decouvrir-filter-group">
                    <label class="decouvrir-filter-label">Talent</label>
                        <select name="talent" class="decouvrir-filter-select" onchange="updateTalentFilter(this.value)">
                        <option value="">Tous les talents</option>
                        <optgroup label="Services">
                            <option value="service:beatmaker" <?php echo (!empty($talent_filter) && $talent_filter === 'service:beatmaker') || (!empty($filter_value) && $filter_value === 'beatmaker') ? 'selected' : ''; ?>>Beatmaker / Producteur</option>
                            <option value="service:chanteur" <?php echo (!empty($talent_filter) && $talent_filter === 'service:chanteur') || (!empty($filter_value) && $filter_value === 'chanteur') ? 'selected' : ''; ?>>Chanteur / Chanteuse</option>
                            <option value="service:organisateur" <?php echo (!empty($talent_filter) && $talent_filter === 'service:organisateur') || (!empty($filter_value) && $filter_value === 'organisateur') ? 'selected' : ''; ?>>Organisateur d'événements</option>
                            <option value="service:dj" <?php echo (!empty($talent_filter) && $talent_filter === 'service:dj') || (!empty($filter_value) && $filter_value === 'dj') ? 'selected' : ''; ?>>DJ</option>
                            <option value="service:ingenieur" <?php echo (!empty($talent_filter) && $talent_filter === 'service:ingenieur') || (!empty($filter_value) && $filter_value === 'ingenieur') ? 'selected' : ''; ?>>Ingénieur son</option>
                            <option value="service:compositeur" <?php echo (!empty($talent_filter) && $talent_filter === 'service:compositeur') || (!empty($filter_value) && $filter_value === 'compositeur') ? 'selected' : ''; ?>>Compositeur</option>
                            <option value="service:musicien" <?php echo (!empty($talent_filter) && $talent_filter === 'service:musicien') || (!empty($filter_value) && $filter_value === 'musicien') ? 'selected' : ''; ?>>Musicien</option>
                        </optgroup>
                        <optgroup label="Genres musicaux">
                            <option value="genre:Pop" <?php echo (!empty($talent_filter) && $talent_filter === 'genre:Pop') || (!empty($filter_value) && $filter_value === 'Pop') ? 'selected' : ''; ?>>Pop</option>
                            <option value="genre:Rock" <?php echo (!empty($talent_filter) && $talent_filter === 'genre:Rock') || (!empty($filter_value) && $filter_value === 'Rock') ? 'selected' : ''; ?>>Rock</option>
                            <option value="genre:Electro / House / Techno" <?php echo (!empty($talent_filter) && $talent_filter === 'genre:Electro / House / Techno') || (!empty($filter_value) && $filter_value === 'Electro / House / Techno') ? 'selected' : ''; ?>>Electro / House / Techno</option>
                            <option value="genre:Classique" <?php echo (!empty($talent_filter) && $talent_filter === 'genre:Classique') || (!empty($filter_value) && $filter_value === 'Classique') ? 'selected' : ''; ?>>Classique</option>
                            <option value="genre:Jazz" <?php echo (!empty($talent_filter) && $talent_filter === 'genre:Jazz') || (!empty($filter_value) && $filter_value === 'Jazz') ? 'selected' : ''; ?>>Jazz</option>
                            <option value="genre:Metal" <?php echo (!empty($talent_filter) && $talent_filter === 'genre:Metal') || (!empty($filter_value) && $filter_value === 'Metal') ? 'selected' : ''; ?>>Metal</option>
                            <option value="genre:Reggaeton / Afro" <?php echo (!empty($talent_filter) && $talent_filter === 'genre:Reggaeton / Afro') || (!empty($filter_value) && $filter_value === 'Reggaeton / Afro') ? 'selected' : ''; ?>>Reggaeton / Afro</option>
                            <option value="genre:Autre" <?php echo (!empty($talent_filter) && $talent_filter === 'genre:Autre') || (!empty($filter_value) && $filter_value === 'Autre') ? 'selected' : ''; ?>>Autre</option>
                        </optgroup>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <?php if (!empty($search_query) || !empty($ville_filter) || !empty($filter_value)) : ?>
                    <div class="decouvrir-filter-actions">
                        <a href="<?php echo esc_url(home_url('/decouvrir')); ?>" class="decouvrir-clear-filters">
                            Réinitialiser les filtres
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Results Count -->
            <div class="decouvrir-results-count">
                <span><?php echo count($filtered_users); ?> profil<?php echo count($filtered_users) > 1 ? 's' : ''; ?> trouvé<?php echo count($filtered_users) > 1 ? 's' : ''; ?></span>
            </div>
        </div>

        <!-- Users Grid -->
        <div class="decouvrir-users-grid">
            <?php if (!empty($filtered_users)) : ?>
                <?php foreach ($filtered_users as $user) : 
                    $user_id = $user->ID;
                    $profile_data = get_user_profile_data($user_id);
                    if (!$profile_data) continue;
                    
                    $profile_url = home_url('/userprofil?user_id=' . $user_id);
                    $profile_photo = $profile_data['profile_photo_url'];
                    $full_name = $profile_data['full_name'];
                    $biographie = $profile_data['biographie'];
                    $ville = $profile_data['ville'];
                    $service_type = $profile_data['service_type'];
                    $filters_labels = $profile_data['filters_labels'];
                    $music_genres = $profile_data['music_genres'];
                ?>
                    <div class="decouvrir-user-card decouvrir-user-card-<?php echo esc_attr($service_type); ?>" onclick="window.location.href='<?php echo esc_url($profile_url); ?>'" style="cursor: pointer;">
                        <div class="decouvrir-user-card-image">
                            <?php if ($profile_photo) : ?>
                                <img src="<?php echo esc_url($profile_photo); ?>" alt="<?php echo esc_attr($full_name); ?>">
                            <?php else : ?>
                                <div class="decouvrir-user-card-placeholder">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="decouvrir-user-card-content">
                            <h3 class="decouvrir-user-card-name"><?php echo esc_html($full_name); ?></h3>
                            <?php if (!empty($ville)) : ?>
                                <div class="decouvrir-user-card-location">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    <span><?php echo esc_html($ville); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($biographie)) : ?>
                                <p class="decouvrir-user-card-bio"><?php echo esc_html(wp_trim_words($biographie, 20)); ?></p>
                            <?php endif; ?>
                            <div class="decouvrir-user-card-tags">
                                <?php if ($service_type === 'offer' && !empty($filters_labels)) : ?>
                                    <?php foreach (array_slice($filters_labels, 0, 3) as $label) : ?>
                                        <span class="decouvrir-user-tag"><?php echo esc_html($label); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($filters_labels) > 3) : ?>
                                        <span class="decouvrir-user-tag">+<?php echo count($filters_labels) - 3; ?></span>
                                    <?php endif; ?>
                                <?php elseif ($service_type === 'seek' && !empty($music_genres)) : ?>
                                    <?php foreach (array_slice($music_genres, 0, 3) as $genre) : ?>
                                        <span class="decouvrir-user-tag"><?php echo esc_html($genre); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($music_genres) > 3) : ?>
                                        <span class="decouvrir-user-tag">+<?php echo count($music_genres) - 3; ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php if (is_user_logged_in() && get_current_user_id() != $user_id) : ?>
                                <div class="decouvrir-user-card-actions">
                                    <a href="<?php echo esc_url(home_url('/messagerie?user_id=' . $user_id)); ?>" class="decouvrir-contact-btn" onclick="event.stopPropagation();">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Contacter
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="decouvrir-no-results">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h3>Aucun profil trouvé</h3>
                    <p>Essayez de modifier vos critères de recherche</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateFilter(filterName, filterValue) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    if (filterName === 'ville') {
        if (filterValue) {
            params.set('ville', filterValue);
        } else {
            params.delete('ville');
        }
    }
    
    // Remove old service_type and filter_type params
    params.delete('service_type');
    params.delete('filter_type');
    params.delete('filter_value');
    
    window.location.href = url.pathname + '?' + params.toString();
}

function updateTalentFilter(talentValue) {
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    if (talentValue) {
        params.set('talent', talentValue);
    } else {
        params.delete('talent');
    }
    
    // Remove old filter params
    params.delete('service_type');
    params.delete('filter_type');
    params.delete('filter_value');
    
    window.location.href = url.pathname + '?' + params.toString();
}

// Auto-submit search form on Enter
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('.decouvrir-search-input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }
});
</script>

<?php get_footer(); ?>
