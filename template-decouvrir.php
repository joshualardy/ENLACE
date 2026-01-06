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

// Helper function to get user activity score for sorting
function get_user_activity_score($user_id) {
    $score = 0;
    
    // Check if profile is new (registered in last 14 days)
    $user = get_userdata($user_id);
    if ($user) {
        $registered = strtotime($user->user_registered);
        $fourteen_days_ago = strtotime('-14 days');
        if ($registered >= $fourteen_days_ago) {
            $score += 10; // New profiles get priority
        }
    }
    
    // Check last active
    $last_active = get_user_meta($user_id, 'last_active', true);
    if ($last_active) {
        $last_active_time = strtotime($last_active);
        $thirty_days_ago = strtotime('-30 days');
        $seven_days_ago = strtotime('-7 days');
        
        if ($last_active_time >= $seven_days_ago) {
            $score += 5; // Very active
        } elseif ($last_active_time >= $thirty_days_ago) {
            $score += 2; // Active
        }
    }
    
    // Check profile completeness
    $profile_photo = get_user_meta($user_id, 'profile_photo_url', true);
    $biographie = get_user_meta($user_id, 'biographie', true);
    if ($profile_photo && !empty($biographie)) {
        $score += 3; // Complete profile
    }
    
    return $score;
}

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

// Sort filtered users by activity score (intelligent curation)
usort($filtered_users, function($a, $b) {
    $score_a = get_user_activity_score($a->ID);
    $score_b = get_user_activity_score($b->ID);
    if ($score_a === $score_b) {
        // If same score, sort by registration date (newest first)
        return strtotime($b->user_registered) - strtotime($a->user_registered);
    }
    return $score_b - $score_a;
});

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

// Map for service and genre labels (for contextual subtitle)
$service_labels_map = array(
    'beatmaker' => 'Beatmakers et producteurs',
    'chanteur' => 'Chanteurs et chanteuses',
    'organisateur' => 'Organisateurs d\'événements',
    'dj' => 'DJ',
    'ingenieur' => 'Ingénieurs son',
    'compositeur' => 'Compositeurs',
    'musicien' => 'Musiciens'
);

// Build contextual subtitle
$contextual_subtitle = '';
if (!empty($search_query)) {
    $contextual_subtitle = 'Résultats pour "' . esc_html($search_query) . '"';
} elseif (!empty($ville_filter) && !empty($filter_value)) {
    $talent_label = '';
    if (isset($service_labels_map[$filter_value])) {
        $talent_label = $service_labels_map[$filter_value];
    } else {
        $talent_label = $filter_value; // Genre or other
    }
    $contextual_subtitle = $talent_label . ' à ' . esc_html($ville_filter);
} elseif (!empty($ville_filter)) {
    $contextual_subtitle = 'Profils à ' . esc_html($ville_filter);
} elseif (!empty($filter_value)) {
    if (isset($service_labels_map[$filter_value])) {
        $contextual_subtitle = $service_labels_map[$filter_value] . ' disponibles';
    } else {
        $contextual_subtitle = 'Profils ' . esc_html($filter_value);
    }
} else {
    $contextual_subtitle = 'Profils actifs sur ENLACE';
}
?>

<div class="decouvrir-page">
    <div class="container">
        <!-- Page Title -->
        <h1 class="decouvrir-page-title">Découvrir</h1>
        <?php if (!empty($contextual_subtitle)) : ?>
            <p class="decouvrir-page-subtitle"><?php echo esc_html($contextual_subtitle); ?></p>
        <?php endif; ?>

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
                        <label for="decouvrir_search" class="sr-only">Rechercher un nom, un talent</label>
                        <svg class="decouvrir-search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <input type="search" name="search" id="decouvrir_search" class="decouvrir-search-input" autocomplete="off" placeholder="Rechercher un nom, un talent..." value="<?php echo esc_attr($search_query); ?>">
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
                    <label for="decouvrir_ville_filter" class="decouvrir-filter-label">Ville</label>
                    <select name="ville" id="decouvrir_ville_filter" class="decouvrir-filter-select" autocomplete="off" onchange="updateFilter('ville', this.value)">
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
                    <label for="decouvrir_talent_filter" class="decouvrir-filter-label">Talent</label>
                        <select name="talent" id="decouvrir_talent_filter" class="decouvrir-filter-select" autocomplete="off" onchange="updateTalentFilter(this.value)">
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
                <?php 
                $results_count = count($filtered_users);
                if ($results_count === 0) {
                    $count_text = 'Aucun profil';
                } elseif ($results_count === 1) {
                    $count_text = '1 profil';
                } else {
                    $count_text = $results_count . ' profils';
                }
                
                // Add context based on filters
                if (!empty($search_query) || !empty($ville_filter) || !empty($filter_value)) {
                    $count_text .= ' correspondant à tes critères';
                } else {
                    $count_text .= ' à découvrir';
                }
                ?>
                <span><?php echo esc_html($count_text); ?></span>
                <?php if ($results_count > 0 && empty($search_query) && empty($ville_filter) && empty($filter_value)) : ?>
                    <span class="decouvrir-sort-indicator"> — Triés par pertinence</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Editorial Context Block -->
        <?php if (!empty($filtered_users)) : ?>
            <div class="decouvrir-editorial-context">
                <?php
                $context_text = '';
                if (!empty($search_query)) {
                    $context_text = 'Résultats pour "' . esc_html($search_query) . '". Affine ta recherche si besoin.';
                } elseif (!empty($ville_filter) && !empty($filter_value)) {
                    $talent_label = isset($service_labels_map[$filter_value]) ? $service_labels_map[$filter_value] : $filter_value;
                    $context_text = $talent_label . ' à ' . esc_html($ville_filter) . '. Profils correspondant à tes critères.';
                } elseif (!empty($ville_filter)) {
                    $context_text = 'Profils à ' . esc_html($ville_filter) . '. Explore les talents locaux disponibles.';
                } elseif (!empty($filter_value)) {
                    if (isset($service_labels_map[$filter_value])) {
                        $context_text = $service_labels_map[$filter_value] . ' disponibles sur la plateforme. Contacte-les pour discuter de ton projet.';
                    } else {
                        $context_text = 'Profils ' . esc_html($filter_value) . '. Explore les talents disponibles.';
                    }
                } else {
                    $context_text = 'Profils actifs sur ENLACE. Contacte directement les professionnels qui correspondent à tes besoins.';
                }
                ?>
                <p><?php echo esc_html($context_text); ?></p>
            </div>
        <?php endif; ?>

        <!-- Editorial breathing space (if many results) -->
        <?php if (count($filtered_users) > 10) : ?>
            <div class="decouvrir-editorial-break"></div>
        <?php endif; ?>

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
                    $filters_raw = $profile_data['filters']; // Raw filter values for clickable tags
                    $music_genres = $profile_data['music_genres'];
                    
                    // Check if profile is new (registered in last 7 days)
                    $user_registered = strtotime($user->user_registered);
                    $seven_days_ago = strtotime('-7 days');
                    $is_new_profile = $user_registered >= $seven_days_ago;
                    
                    // Check if profile is active (last active in last 30 days)
                    $last_active = get_user_meta($user_id, 'last_active', true);
                    $is_active_profile = false;
                    $last_active_relative = '';
                    if ($last_active) {
                        $last_active_time = strtotime($last_active);
                        $thirty_days_ago = strtotime('-30 days');
                        if ($last_active_time >= $thirty_days_ago) {
                            $is_active_profile = true;
                            $days_ago = floor((time() - $last_active_time) / (60 * 60 * 24));
                            if ($days_ago === 0) {
                                $last_active_relative = "Aujourd'hui";
                            } elseif ($days_ago === 1) {
                                $last_active_relative = "Hier";
                            } elseif ($days_ago < 7) {
                                $last_active_relative = "Il y a " . $days_ago . " jours";
                            } elseif ($days_ago < 30) {
                                $weeks_ago = floor($days_ago / 7);
                                $last_active_relative = "Il y a " . $weeks_ago . " semaine" . ($weeks_ago > 1 ? 's' : '');
                            }
                        }
                    }
                    
                    // Check if profile is curated (high activity score)
                    $activity_score = get_user_activity_score($user_id);
                    $is_curated = $activity_score >= 8; // High activity or new + complete
                    
                    // Check if bio is truncated
                    $bio_word_count = str_word_count($biographie);
                    $bio_is_truncated = $bio_word_count > 20;
                ?>
                    <div class="decouvrir-user-card decouvrir-user-card-<?php echo esc_attr($service_type); ?>" onclick="window.location.href='<?php echo esc_url($profile_url); ?>'" style="cursor: pointer;">
                        <div class="decouvrir-user-card-image">
                            <?php if ($is_new_profile) : ?>
                                <span class="decouvrir-profile-badge decouvrir-profile-badge-new">Nouveau</span>
                            <?php elseif ($is_active_profile && !$is_new_profile) : ?>
                                <span class="decouvrir-profile-badge decouvrir-profile-badge-active">Actif</span>
                            <?php endif; ?>
                            <?php if ($is_curated && !$is_new_profile && !$is_active_profile) : ?>
                                <span class="decouvrir-profile-badge decouvrir-profile-badge-curated">Sélection</span>
                            <?php endif; ?>
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
                            <div class="decouvrir-user-card-header">
                                <div class="decouvrir-user-card-name-wrapper">
                                    <h3 class="decouvrir-user-card-name"><?php echo esc_html($full_name); ?></h3>
                                    <?php if (!empty($last_active_relative) && $is_active_profile) : ?>
                                        <span class="decouvrir-user-last-active" title="Dernière activité"><?php echo esc_html($last_active_relative); ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="decouvrir-user-intention">
                                    <?php if ($service_type === 'offer') : ?>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Propose
                                    <?php else : ?>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Recherche
                                    <?php endif; ?>
                                </span>
                            </div>
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
                                <p class="decouvrir-user-card-bio <?php echo $bio_is_truncated ? 'decouvrir-bio-truncated' : ''; ?>">
                                    <?php echo esc_html(wp_trim_words($biographie, 20)); ?>
                                    <?php if ($bio_is_truncated) : ?>
                                        <span class="decouvrir-bio-indicator" title="Lire la suite sur le profil">...</span>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>
                            <div class="decouvrir-user-card-tags">
                                <?php if ($service_type === 'offer' && !empty($filters_labels)) : ?>
                                    <?php 
                                    // Build mapping of labels to raw filter values
                                    $filter_label_to_value = array();
                                    if (is_array($filters_raw) && !empty($filters_raw)) {
                                        foreach ($filters_raw as $raw_value) {
                                            if (isset($service_labels_map[$raw_value])) {
                                                $filter_label_to_value[$service_labels_map[$raw_value]] = $raw_value;
                                            }
                                        }
                                    }
                                    
                                    // Show first 3 filters
                                    $filters_to_show = array_slice($filters_labels, 0, 3);
                                    foreach ($filters_to_show as $label) : 
                                        $filter_key = isset($filter_label_to_value[$label]) ? $filter_label_to_value[$label] : '';
                                        $is_active_filter = !empty($filter_value) && $filter_key === $filter_value;
                                        $tag_class = $is_active_filter ? 'decouvrir-tag-active' : (!empty($filter_key) ? 'decouvrir-tag-clickable' : '');
                                    ?>
                                        <span class="decouvrir-user-tag <?php echo esc_attr($tag_class); ?>" 
                                              <?php if (!empty($filter_key)) : ?>
                                              data-filter-type="service" 
                                              data-filter-value="<?php echo esc_attr($filter_key); ?>"
                                              onclick="event.stopPropagation(); handleTagClick('service', '<?php echo esc_js($filter_key); ?>');"
                                              <?php endif; ?>>
                                            <?php echo esc_html($label); ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (count($filters_labels) > 3) : ?>
                                        <span class="decouvrir-user-tag decouvrir-tag-more">+<?php echo count($filters_labels) - 3; ?></span>
                                    <?php endif; ?>
                                <?php elseif ($service_type === 'seek' && !empty($music_genres)) : ?>
                                    <?php 
                                    $genres_to_show = array_slice($music_genres, 0, 3);
                                    foreach ($genres_to_show as $genre) : 
                                        $is_active_filter = !empty($filter_value) && $genre === $filter_value;
                                    ?>
                                        <span class="decouvrir-user-tag <?php echo $is_active_filter ? 'decouvrir-tag-active' : 'decouvrir-tag-clickable'; ?>" 
                                              data-filter-type="genre" 
                                              data-filter-value="<?php echo esc_attr($genre); ?>"
                                              onclick="event.stopPropagation(); handleTagClick('genre', '<?php echo esc_js($genre); ?>');">
                                            <?php echo esc_html($genre); ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if (count($music_genres) > 3) : ?>
                                        <span class="decouvrir-user-tag decouvrir-tag-more">+<?php echo count($music_genres) - 3; ?></span>
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
                    <?php if (!empty($search_query)) : ?>
                        <p>Aucun résultat pour "<?php echo esc_html($search_query); ?>".</p>
                        <p class="decouvrir-no-results-suggestion">Essayez un autre terme ou explorez par <a href="<?php echo esc_url(home_url('/decouvrir')); ?>">ville ou talent</a>.</p>
                    <?php elseif (!empty($ville_filter) || !empty($filter_value)) : ?>
                        <p>Aucun profil ne correspond à ces critères.</p>
                        <p class="decouvrir-no-results-suggestion"><a href="<?php echo esc_url(home_url('/decouvrir')); ?>">Voir tous les profils disponibles</a></p>
                    <?php else : ?>
                        <p>Aucun profil disponible pour le moment.</p>
                    <?php endif; ?>
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

// Handle tag clicks for filtering
function handleTagClick(filterType, filterValue) {
    if (!filterValue) return;
    
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    
    // Set the talent filter based on type
    if (filterType === 'service') {
        params.set('talent', 'service:' + filterValue);
    } else if (filterType === 'genre') {
        params.set('talent', 'genre:' + filterValue);
    }
    
    // Remove old filter params
    params.delete('service_type');
    params.delete('filter_type');
    params.delete('filter_value');
    
    // Scroll to top and reload
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
