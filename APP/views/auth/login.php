
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CampusFlow</title>
    <link rel="stylesheet" href="CSS/login.css">
</head>

<body class="login-page">

    <header class="site-header">
        <div class="logo-placeholder">
            <img src="images/logo.png" alt="Logo CampusFlow">
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

                <h1 class="welcome-message">Connexion</h1>

                <p class="welcome-subtitle">
                    Gérez vos cours, vos modules et vos notes au même endroit.
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

            <form class="login-form" action="index.php?route=login" method="post" novalidate>

                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Entrez votre adresse e-mail"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-field">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Entrez votre mot de passe"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            type="button"
                            id="toggle-password"
                            class="toggle-password"
                            aria-controls="password"
                            aria-pressed="false"
                        >Afficher</button>
                    </div>
                </div>

                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember_me" name="remember_me" value="1">
                        <label for="remember_me">Se souvenir de moi</label>
                    </div>
                    <a href="#" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="login-button">Se connecter</button>

            </form>

        </section>

    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('toggle-password');

        toggleButton.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            toggleButton.textContent = isHidden ? 'Masquer' : 'Afficher';
            toggleButton.setAttribute('aria-pressed', String(isHidden));
        });
    </script>

</body>
</html>
