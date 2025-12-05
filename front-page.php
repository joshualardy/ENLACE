<?php
get_header();
?>

<div class="front-page-content">
    <h1>Homepage</h1>

    <?php if (is_user_logged_in()) : ?>
        <div class="user-status logged-in">
            <p>Welcome, <strong><?= esc_html(wp_get_current_user()->display_name); ?></strong>!</p>
            <p>You are logged in.</p>
            <a href="<?= esc_url(wp_logout_url(home_url())); ?>" class="logout-btn">Logout</a>
        </div>
    <?php else : ?>
        <div class="user-status logged-out">
            <p>You are not logged in.</p>
            <div class="auth-links">
                <a href="/login" class="login-btn">Login</a>
                <a href="/signup" class="register-btn">Sign Up</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
?>