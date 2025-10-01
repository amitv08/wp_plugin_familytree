<?php
if (is_user_logged_in()) {
    wp_redirect('/family-dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .login-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .login-form input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .login-form input[type="submit"]:hover {
            background: #005a87;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body>

<div class="login-container">
    <h2>Family Tree Login</h2>
    <?php
    wp_login_form(array(
        'redirect' => '/family-dashboard',
        'label_username' => 'Username or Email',
        'label_remember' => 'Remember Me'
    ));
    ?>
    
    <?php if (get_option('users_can_register')): ?>
        <div class="register-link">
            <p>Don't have an account? <a href="<?php echo wp_registration_url(); ?>">Register here</a></p>
        </div>
    <?php endif; ?>
</div>

<?php wp_footer(); ?>
</body>
</html>