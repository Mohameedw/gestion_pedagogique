<?php
session_start();

require_once __DIR__ . '/connect_db.php';

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '') {
        $errors[] = 'Please enter your institutional email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        // TODO: vérification MySQL, session, redirection selon le rôle
        $errors[] = 'Authentication is not available yet.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CampusFlow</title>
    <link rel="stylesheet" href="../CSS/login.css">
</head>

<body class="login-page">

    <header class="site-header">
        <div class="logo-placeholder">
            <img src="../images/logo.png" alt="CampusFlow Logo">
        </div>
        <span class="app-name">CampusFlow</span>
    </header>

    <main class="login-container">

        <section class="login-card">

            <header class="login-header">
                <div class="card-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12h11"/>
                        <path d="M10 7l5 5-5 5"/>
                        <path d="M17 4h1.5A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5H17"/>
                    </svg>
                </div>
                <h1 class="welcome-message">login with email</h1>
                <p class="welcome-subtitle">
                    Manage your courses, modules and grades in one place.
                </p>
            </header>

            <?php if (!empty($errors)): ?>
                <div class="form-errors" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="login-form" action="login.php" method="post" novalidate>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Email"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Password"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            type="button"
                            id="toggle-password"
                            class="toggle-password"
                            aria-controls="password"
                            aria-pressed="false"
                        >Show</button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember_me" name="remember_me" value="1">
                        <label for="remember_me">Remember Me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-button">Login</button>

            </form>

        </section>

    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleButton  = document.getElementById('toggle-password');

        toggleButton.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            toggleButton.textContent = isHidden ? 'Hide' : 'Show';
            toggleButton.setAttribute('aria-pressed', String(isHidden));
        });
    </script>

</body>
</html>