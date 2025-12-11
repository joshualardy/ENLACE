<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="main-header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Logos/logo+ENLACE.svg'); ?>" alt="ENLACE Logo" class="nav-logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo esc_url(home_url('/')); ?>">HOME</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo esc_url(home_url('/decouvrir')); ?>">DÉCOUVRIR</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo esc_url(home_url('/annonces')); ?>">ANNONCES</a>
                        </li>
                        <?php if (is_user_logged_in()) : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo esc_url(home_url('/userprofil')); ?>"><?php echo esc_html(wp_get_current_user()->display_name); ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">DÉCONNEXION</a>
                            </li>
                        <?php else : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo esc_url(home_url('/login')); ?>">CONNEXION</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo esc_url(home_url('/signup')); ?>">INSCRIPTION</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main id="main-content">