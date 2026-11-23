
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
    $emptyMessage = 'Le compte connecté n’a pas encore de profil associé dans la table Enseignant.';
    require dirname(__DIR__) . '/shared/empty-state.php';
    ?>


<?php elseif ($page === 'overview'): ?>

    <div class="welcome">
        <div>
            <span class="eyebrow">VOTRE ESPACE</span>
            <h2>Bonjour, <?= $e($profile['prenom']) ?></h2>
            <p>Consultez vos modules, suivez vos étudiants et gérez leurs notes.</p>
        </div>

        <div class="welcome-icon">✦</div>
    </div>

    <div class="stats-grid">

        <article class="stat-card">
            <span>Mes modules</span>
            <strong><?= (int) $stats[0] ?></strong>
            <small>Enseignements attribués</small>
        </article>

        <article class="stat-card">
            <span>Évaluations</span>
            <strong><?= (int) $stats[1] ?></strong>
            <small>Dans mes enseignements</small>
        </article>

        <article class="stat-card">
            <span>Notes saisies</span>
            <strong><?= (int) $stats[2] ?></strong>
            <small>Notes enregistrées</small>
        </article>

    </div>

    <h2>Mes enseignements</h2>

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
                <label>Nom</label>
                <p><?= $e($profile['nom'] ?? '') ?></p>
            </div>

            <div class="profile-field">
                <label>Prénom</label>
                <p><?= $e($profile['prenom'] ?? '') ?></p>
            </div>

            <div class="profile-field">
                <label>Matricule</label>
                <p><?= $e($profile['matricule'] ?? '') ?></p>
            </div>

            <div class="profile-field">
                <label>Téléphone</label>
                <p><?= $e($profile['telephone'] ?: 'Non renseigné') ?></p>
            </div>

            <div class="profile-field">
                <label>Spécialité</label>
                <p><?= $e($profile['specialite'] ?: 'Non renseignée') ?></p>
            </div>

        </section>


        <!-- Changement de mot de passe -->
        <section class="profile-section">

            <h3>Sécurité</h3>

            <form
                method="POST"
                action="index.php?route=professor&page=profile"
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

    <?php if ($page === 'grades'): ?>

        <h2>Enregistrer une note</h2>

        <form
            class="grade-form"
            method="post"
            action="?route=professor&amp;page=grades"
        >

            <label>Évaluation</label>

            <select name="id_evaluation" required>
                <option value="">Choisir une évaluation</option>

                <?php foreach ($evaluationOptions as $evaluation): ?>
                    <option value="<?= (int) $evaluation['id_evaluation'] ?>">
                        <?= $e($evaluation['code_module'] . ' · ' . $evaluation['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Étudiant</label>

            <select name="id_inscription" required>
                <option value="">Choisir un étudiant</option>

                <?php foreach ($registrationOptions as $student): ?>
                    <option value="<?= (int) $student['id_inscription'] ?>">
                        <?= $e($student['CNE'] . ' · ' . $student['prenom'] . ' ' . $student['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Note</label>
            <input type="number" name="valeur" min="0" step="0.01" required>

            <label>Observation (facultative)</label>
            <input type="text" name="observation" maxlength="1000">

            <button type="submit" name="save_grade" value="1">
                Enregistrer la note
            </button>

        </form>

    <?php endif; ?>

    <h2><?= $e($title) ?></h2>

    <?php require dirname(__DIR__) . '/shared/table.php'; ?>

<?php endif; ?>
