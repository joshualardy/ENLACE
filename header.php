<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-textured-light'); ?>>
    <?php wp_body_open(); ?>

    <header class="main-header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo get_template_directory_uri() . '/assets/images/Logos/logo+ENLACE.svg'; ?>" alt="ENLACE Logo" class="nav-logo">
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
                        <?php if (is_user_logged_in()) : 
                            $unread_count = get_unread_messages_count(get_current_user_id());
                        ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo esc_url(home_url('/messagerie')); ?>">
                                    Messages
                                    <?php if ($unread_count > 0) : ?>
                                        <span class="nav-unread-badge"><?php echo $unread_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item dropdown dropdown-hover">
                                <a class="nav-link dropdown-toggle" href="#" id="userMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <?php echo esc_html(wp_get_current_user()->display_name); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                                    <li>
                                        <a class="dropdown-item" href="<?php echo esc_url(home_url('/userprofil')); ?>">
                                            <span class="dropdown-item-icon"><?php the_icon('UserIcon', array('width' => '16', 'height' => '16')); ?></span>
                                            Mon compte
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo esc_url(wp_logout_url(home_url())); ?>">
                                            <span class="dropdown-item-icon"><?php the_icon('ArrowRightOnRectangleIcon', array('width' => '16', 'height' => '16')); ?></span>
                                            Se déconnecter
                                        </a>
                                    </li>
                                </ul>
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