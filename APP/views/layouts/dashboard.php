
<?php
$currentPage = $page ?? 'overview';

$route = strtolower(
    $roleLabel === 'Administrateur'
        ? 'admin'
        : ($roleLabel === 'Professeur' ? 'professor' : 'student')
);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'CampusFlow') ?></title>

    <link rel="stylesheet" href="public/css/dashboard.css">
</head>
<body>

<div class="dashboard">

    <aside class="sidebar">
        <h2 class="logo">CampusFlow</h2>

        <p class="role"><?= htmlspecialchars($roleLabel ?? '') ?></p>

        <nav>
    <?php foreach ($nav as $key => $label): ?>

        <?php if ($key === 'profile') continue; ?>

        <a
            href="index.php?route=<?= $route ?>&page=<?= urlencode($key) ?>"
            class="<?= $currentPage === $key ? 'active' : '' ?>"
        >
            <?= htmlspecialchars($label) ?>
        </a>

    <?php endforeach; ?>
</nav>
    </aside>

    <div class="main-area">

        <header class="topbar">
            <h3><?= htmlspecialchars($title ?? '') ?></h3>

            <details class="profile-menu">
               <summary>
    <span class="profile-name">
        <?= htmlspecialchars($roleLabel ?? '') ?>
    </span>
</summary>

                <div class="profile-dropdown">
                    <a href="index.php?route=<?= $route ?>&page=profile">
                        Mon profil
                    </a>

                    <a href="index.php?route=logout" class="logout-link">
                        Déconnexion
                    </a>
                </div>
            </details>
        </header>

        <main class="content">
            <?= $pageContent ?>
        </main>

    </div>

</div>

</body>
</html>
