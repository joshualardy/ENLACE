<?php

/**
 * Template Name: Register Template
 */
get_header();
?>

<div class="register-container">
    <div class="register-form-wrapper">
        <h1>Sign Up</h1>

        <?php
        if (isset($_GET['registration']) && $_GET['registration'] == 'success') {
            echo '<div class="success-message">Registration successful! You can now login.</div>';
        }
        if (isset($_GET['registration']) && $_GET['registration'] == 'error') {
            echo '<div class="error-message">Registration failed. Please try again.</div>';
        }
        if (is_user_logged_in()) {
            echo '<div class="success-message">You are already logged in. <a href="' . wp_logout_url(home_url()) . '">Logout</a></div>';
        } else {
        ?>

            <form method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" class="register-form">
                <?php wp_nonce_field('register_action', 'register_nonce'); ?>

                <div class="form-group">
                    <label for="user_login">Username</label>
                    <input type="text" name="user_login" id="user_login" required>
                </div>

                <div class="form-group">
                    <label for="user_email">Email</label>
                    <input type="email" name="user_email" id="user_email" required>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone">
                </div>

                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" name="student_id" id="student_id">
                </div>

                <div class="form-group">
                    <label for="user_pass">Password</label>
                    <input type="password" name="user_pass" id="user_pass" required>
                </div>

                <div class="form-group">
                    <label for="user_pass_confirm">Confirm Password</label>
                    <input type="password" name="user_pass_confirm" id="user_pass_confirm" required>
                </div>

                <button type="submit" name="register_submit" class="submit-btn">Sign Up</button>
            </form>

            <p class="login-link">Already have an account? <a href="<?php echo esc_url(home_url('/login')); ?>">Login</a></p>

        <?php } ?>
    </div>
</div>

<?php
get_footer();
?>