<?php

/**
 * Template Name: Login Template
 */
get_header();
?>

<div class="login-container">
    <div class="login-form-wrapper">
        <h1>Login</h1>

        <?php
        if (isset($_GET['login']) && $_GET['login'] == 'failed') {
            echo '<div class="error-message">Invalid username or password.</div>';
        }
        if (isset($_GET['login']) && $_GET['login'] == 'empty') {
            echo '<div class="error-message">Please fill in all fields.</div>';
        }
        if (is_user_logged_in()) {
            echo '<div class="success-message">You are already logged in. <a href="' . wp_logout_url(home_url()) . '">Logout</a></div>';
        } else {
        ?>

            <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="login-form">
                <?php wp_nonce_field('login_action', 'login_nonce'); ?>

                <div class="form-group">
                    <label for="user_login">Username or Email</label>
                    <input type="text" name="log" id="user_login" required>
                </div>

                <div class="form-group">
                    <label for="user_pass">Password</label>
                    <input type="password" name="pwd" id="user_pass" required>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="rememberme" value="forever"> Remember me
                    </label>
                </div>

                <button type="submit" name="login_submit" class="submit-btn">Login</button>
            </form>

            <p class="register-link">Don't have an account? <a href="<?php echo esc_url(home_url('/signup')); ?>">Sign up</a></p>

        <?php } ?>
    </div>
</div>

<?php
get_footer();
?>