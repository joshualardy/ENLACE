    </main>

    <footer>
        <div class='logo-footer'>
            <img src='<?php echo esc_url(get_template_directory_uri() . '/assets/images/Logos/logo_blanc.svg'); ?>' alt='ENLACE Logo'>
        </div>
        <div class='footer-content'>
            <nav class='footer-nav'>
                <a href='<?php echo esc_url(home_url('/decouvrir')); ?>'>Découvrir</a>
                <a href='<?php echo esc_url(home_url('/annonces')); ?>'>Annonces</a>
                <?php if (is_user_logged_in()) : ?>
                    <a href='<?php echo esc_url(home_url('/profil')); ?>'><?php echo esc_html(wp_get_current_user()->display_name); ?></a>
                    <a href='<?php echo esc_url(wp_logout_url(home_url())); ?>'>Déconnexion</a>
                <?php else : ?>
                    <a href='<?php echo esc_url(home_url('/login')); ?>'>Connexion</a>
                    <a href='<?php echo esc_url(home_url('/signup')); ?>'>Inscription</a>
                <?php endif; ?>
            </nav>
            <div class='footer-legal'>
                <div class='footer-legal-links'>
                    <?php
                    // Get pages by slug
                    $politique_page = get_page_by_path('politique');
                    $mentions_page = get_page_by_path('mentions-legales');
                    $cgu_page = get_page_by_path('cgu');
                    
                    // Politique link
                    if ($politique_page) {
                        echo '<a href="' . esc_url(get_permalink($politique_page->ID)) . '">Politique</a>';
                    } else {
                        echo '<a href="' . esc_url(home_url('/politique')) . '">Politique</a>';
                    }
                    ?>
                    <span class='footer-legal-separator'>|</span>
                    <?php
                    // Mentions légales link
                    if ($mentions_page) {
                        echo '<a href="' . esc_url(get_permalink($mentions_page->ID)) . '">Mentions légales</a>';
                    } else {
                        echo '<a href="' . esc_url(home_url('/mentions-legales')) . '">Mentions légales</a>';
                    }
                    ?>
                    <span class='footer-legal-separator'>|</span>
                    <?php
                    // CGU link
                    if ($cgu_page) {
                        echo '<a href="' . esc_url(get_permalink($cgu_page->ID)) . '">CGU</a>';
                    } else {
                        echo '<a href="' . esc_url(home_url('/cgu')) . '">CGU</a>';
                    }
                    ?>
                </div>
                <div class='footer-copyright'>
                    © 2025, ENLACE
                </div>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
    </body>
    </html>