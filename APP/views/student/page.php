
<?php
$e = static fn($value) => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
);
?>

<?php if (!empty($message)): ?>
    <div class="alert-success auto-message">
        <?= $e($message) ?>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert-error auto-message">
        <?= $e($error) ?>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.auto-message').forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s ease';
            message.style.opacity = '0';

            setTimeout(() => {
                message.remove();
            }, 500);

        }, 3000);
    });
});
</script>


<?php if (!$profile): ?>

    <?php
    $emptyTitle = 'Profil académique introuvable';
    $emptyMessage = 'Aucun profil étudiant associé à ce compte.';
    require dirname(__DIR__) . '/shared/empty-state.php';
    ?>


<?php elseif ($page === 'overview'): ?>

    <div class="welcome">
        <div>
            <span class="eyebrow">VOTRE ESPACE</span>
            <h2>Bonjour, <?= $e($profile['prenom']) ?></h2>
            <p>Consultez votre parcours, vos évaluations et vos résultats.</p>
        </div>

        <div class="welcome-icon">✦</div>
    </div>

    <div class="stats-grid">

        <article class="stat-card">
            <span>Mes modules</span>
            <strong><?= (int) $stats[0] ?></strong>
            <small>Inscriptions validées</small>
        </article>

        <article class="stat-card">
            <span>Mes notes</span>
            <strong><?= (int) $stats[1] ?></strong>
            <small>Enregistrées dans le système</small>
        </article>

        <article class="stat-card">
            <span>Identifiant</span>
            <strong><?= $e($profile['CNE']) ?></strong>
            <small>CNE</small>
        </article>

    </div>

    <h2>Mon parcours académique</h2>

    <?php
    $records = $overviewModules;
    $columns = $overviewColumns;
    require dirname(__DIR__) . '/shared/table.php';
    ?>


<?php elseif ($page === 'profile'): ?>

    <div class="profile-card">

        <!-- Informations personnelles : lecture seule -->
        <section class="profile-section">

            <h3>Informations personnelles</h3>

            <div class="profile-field">
                <label>CNE</label>
                <p><?= $e($profile['CNE']) ?></p>
            </div>

            <div class="profile-field">
                <label>Nom</label>
                <p><?= $e($profile['nom']) ?></p>
            </div>

            <div class="profile-field">
                <label>Prénom</label>
                <p><?= $e($profile['prenom']) ?></p>
            </div>

            <div class="profile-field">
                <label>Date de naissance</label>
                <p><?= $e($profile['date_naissance']) ?></p>
            </div>

        </section>


        <!-- Coordonnées : lecture seule -->
        <section class="profile-section">

            <h3>Mes coordonnées</h3>

            <div class="profile-field">
                <label>Téléphone</label>
                <p><?= $e($profile['telephone'] ?: 'Non renseigné') ?></p>
            </div>

            <div class="profile-field">
                <label>Adresse</label>
                <p><?= $e($profile['adresse'] ?: 'Non renseignée') ?></p>
            </div>

        </section>


        <!-- Changement de mot de passe uniquement -->
        <section class="profile-section">

            <h3>Sécurité</h3>

            <form
                method="POST"
                action="index.php?route=student&page=profile"
                id="password-form"
            >

                <input type="hidden" name="change_password" value="1">

                <div class="profile-field">
                    <label for="current_password">
                        Mot de passe actuel
                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="profile-field">
                    <label for="new_password">
                        Nouveau mot de passe
                    </label>

                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>

                <div class="profile-field">
                    <label for="confirm_password">
                        Confirmer le nouveau mot de passe
                    </label>

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>

                <button type="submit">
                    Changer le mot de passe
                </button>

            </form>

        </section>

    </div>


<?php elseif ($page === 'notifications'): ?>

    <?php
    $emptyTitle = 'Vous êtes à jour';
    $emptyMessage = 'Aucune notification disponible pour le moment.';
    require dirname(__DIR__) . '/shared/empty-state.php';
    ?>


<?php else: ?>

    <?php require dirname(__DIR__) . '/shared/table.php'; ?>

<?php endif; ?>
