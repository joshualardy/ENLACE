<?php

/**
 * Template Name: Annonces Template
 */

get_header();

// Get all announcements
$args = array(
    'post_type' => 'annonce',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
);
$annonces_query = new WP_Query($args);
?>

<div class="annonces-page">
    <div class="container">
        <!-- Page Title -->
        <h1 class="annonces-page-title">Événements</h1>

        <!-- Add Announcement Button (only for logged in users) -->
        <?php if (is_user_logged_in()) : ?>
            <div class="annonces-actions">
                <button class="btn btn-add-annonce" id="add-annonce-btn">+ Ajouter une annonce</button>
            </div>
        <?php endif; ?>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] === 'annonce_created') : ?>
            <div class="alert alert-success">Votre annonce a été créée avec succès !</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])) : ?>
            <div class="alert alert-danger">
                <?php
                switch ($_GET['error']) {
                    case 'not_logged_in':
                        echo 'Vous devez être connecté pour créer une annonce.';
                        break;
                    case 'missing_fields':
                        echo 'Veuillez remplir tous les champs requis.';
                        break;
                    case 'creation_failed':
                        echo 'Erreur lors de la création de l\'annonce. Veuillez réessayer.';
                        break;
                    default:
                        echo 'Une erreur est survenue.';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Add Announcement Form (Hidden by default) -->
        <?php if (is_user_logged_in()) : ?>
            <div class="add-annonce-form-wrapper" id="add-annonce-form-wrapper" style="display: none;">
                <div class="add-annonce-form-container">
                    <div class="add-annonce-form-header">
                        <h2>Ajouter une annonce</h2>
                        <button class="btn-close-form" id="close-annonce-form">&times;</button>
                    </div>
                    <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="add-annonce-form" enctype="multipart/form-data">
                        <?php wp_nonce_field('create_annonce_action', 'create_annonce_nonce'); ?>
                        
                        <div class="mb-3">
                            <label for="annonce_title" class="form-label">Titre <span class="required">*</span></label>
                            <input type="text" class="form-control" name="annonce_title" id="annonce_title" required>
                        </div>

                        <div class="mb-3">
                            <label for="annonce_content" class="form-label">Description <span class="required">*</span></label>
                            <textarea class="form-control" name="annonce_content" id="annonce_content" rows="5" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="annonce_localisation" class="form-label">Localisation</label>
                            <input type="text" class="form-control" name="annonce_localisation" id="annonce_localisation" placeholder="localisation">
                        </div>

                        <div class="mb-3">
                            <label for="annonce_service_type" class="form-label">Type de service</label>
                            <select class="form-control" name="annonce_service_type" id="annonce_service_type">
                                <option value="offer">J'offre un service</option>
                                <option value="seek">Je cherche un service</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="annonce_image" class="form-label">Image (optionnel)</label>
                            <input type="file" class="form-control" name="annonce_image" id="annonce_image" accept="image/*">
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="create_annonce_submit" class="btn btn-submit-annonce">Publier l'annonce</button>
                            <button type="button" class="btn btn-cancel-annonce" id="cancel-annonce-form">Annuler</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Announcements Grid -->
        <div class="annonces-grid" id="annonces-grid">
            <?php if ($annonces_query->have_posts()) : ?>
                <?php while ($annonces_query->have_posts()) : $annonces_query->the_post(); 
                    $localisation = get_post_meta(get_the_ID(), '_annonce_localisation', true);
                    $service_type = get_post_meta(get_the_ID(), '_annonce_service_type', true);
                    $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                    $author = get_userdata(get_the_author_meta('ID'));
                    $author_name = $author ? $author->display_name : 'Utilisateur';
                ?>
                    <div class="annonce-card" data-annonce-id="<?php echo get_the_ID(); ?>">
                        <div class="annonce-card-image">
                            <?php if ($thumbnail_url) : ?>
                                <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                            <?php else : ?>
                                <div class="annonce-card-placeholder">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4 16L8.586 11.414C9.367 10.633 10.633 10.633 11.414 11.414L16 16M14 14L15.586 12.414C16.367 11.633 17.633 11.633 18.414 12.414L22 16M18 8V6C18 4.895 17.105 4 16 4H8C6.895 4 6 4.895 6 6V18C6 19.105 6.895 20 8 20H16C17.105 20 18 19.105 18 18V16M18 8H20C21.105 8 22 8.895 22 10V18C22 19.105 21.105 20 20 20H12C10.895 20 10 19.105 10 18V10C10 8.895 10.895 8 12 8H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="annonce-card-content">
                            <div class="annonce-card-header">
                                <h3 class="annonce-card-title"><?php echo esc_html(get_the_title()); ?></h3>
                                <button class="annonce-bookmark-btn" data-annonce-id="<?php echo get_the_ID(); ?>" aria-label="Ajouter aux favoris">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 21L12 16L5 21V5C5 4.46957 5.21071 3.96086 5.58579 3.58579C5.96086 3.21071 6.46957 3 7 3H17C17.5304 3 18.0391 3.21071 18.4142 3.58579C18.7893 3.96086 19 4.46957 19 5V21Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="annonce-card-description"><?php echo esc_html(wp_trim_words(get_the_content(), 20)); ?></p>
                            <?php if ($localisation) : ?>
                                <div class="annonce-card-location">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="1.5"/>
                                        <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    <span><?php echo esc_html($localisation); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; 
                wp_reset_postdata();
                ?>
            <?php else : ?>
                <div class="no-annonces">
                    <p>Aucune annonce pour le moment.</p>
                    <?php if (is_user_logged_in()) : ?>
                        <p>Soyez le premier à publier une annonce !</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Announcement Detail Modal -->
<div class="annonce-modal-overlay" id="annonce-modal-overlay" style="display: none;">
    <div class="annonce-modal-content">
        <button class="annonce-modal-close" id="close-annonce-modal">&times;</button>
        <div class="annonce-modal-body" id="annonce-modal-body">
            <!-- Content will be loaded via JavaScript -->
        </div>
    </div>
</div>

<?php get_footer(); ?>
