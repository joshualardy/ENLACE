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
        <nav class="main-nav">
            <ul class="nav-menu">
                <li class="nav-logo-item">
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/Logos/logo+ENLACE.svg'); ?>" alt="ENLACE Logo" class="nav-logo">
                    </a>
                </li>
                <li><a href="<?php echo esc_url(home_url('/')); ?>">HOME</a></li>
                <li><a href="<?php echo esc_url(home_url('/decouvrir')); ?>">DÉCOUVRIR</a></li>
                <li><a href="<?php echo esc_url(home_url('/annonces')); ?>">ANNONCES</a></li>
                <?php if (is_user_logged_in()) : ?>
                    <li><a href="<?php echo esc_url(home_url('/userprofil')); ?>"><?php echo esc_html(wp_get_current_user()->display_name); ?></a></li>
                    <li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>">DÉCONNEXION</a></li>
                <?php else : ?>
                    <li><a href="<?php echo esc_url(home_url('/login')); ?>">CONNEXION</a></li>
                    <li><a href="<?php echo esc_url(home_url('/signup')); ?>">INSCRIPTION</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main id="main-content">