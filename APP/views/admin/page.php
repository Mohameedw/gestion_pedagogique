
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
document.querySelectorAll('.auto-message').forEach(message => {
    setTimeout(() => {
        message.style.opacity = '0';

        setTimeout(() => {
            message.remove();
        }, 400);
    }, 3000);
});
</script>


<?php if ($page === 'edit_student' && $editStudent): ?>

    <form method="POST" action="index.php?route=admin&page=edit_student&id=<?= (int) $_GET['id'] ?>">

        <label>CNE</label>
        <input type="text" name="CNE" value="<?= $e($editStudent['CNE']) ?>" required>

        <label>Nom</label>
        <input type="text" name="nom" value="<?= $e($editStudent['nom']) ?>" required>

        <label>Prénom</label>
        <input type="text" name="prenom" value="<?= $e($editStudent['prenom']) ?>" required>

        <label>Date de naissance</label>
        <input type="date" name="date_naissance" value="<?= $e($editStudent['date_naissance'] ?? '') ?>">

        <label>Téléphone</label>
        <input type="tel" name="telephone" value="<?= $e($editStudent['telephone'] ?? '') ?>">

        <label>Adresse</label>
        <textarea name="adresse"><?= $e($editStudent['adresse'] ?? '') ?></textarea>

        <button type="submit">Enregistrer les modifications</button>
    </form>


<?php elseif ($page === 'edit_teacher' && $editTeacher): ?>

    <form method="POST" action="index.php?route=admin&page=edit_teacher&id=<?= (int) $_GET['id'] ?>">

        <label>Matricule</label>
        <input type="text" name="matricule" value="<?= $e($editTeacher['matricule']) ?>" required>

        <label>Nom</label>
        <input type="text" name="nom" value="<?= $e($editTeacher['nom']) ?>" required>

        <label>Prénom</label>
        <input type="text" name="prenom" value="<?= $e($editTeacher['prenom']) ?>" required>

        <label>Téléphone</label>
        <input type="tel" name="telephone" value="<?= $e($editTeacher['telephone'] ?? '') ?>">

        <label>Spécialité</label>
        <input type="text" name="specialite" value="<?= $e($editTeacher['specialite'] ?? '') ?>">

        <button type="submit">Enregistrer les modifications</button>
    </form>


<?php elseif ($page === 'profile'): ?>

    <?php if ($profile): ?>

        <?php
        $adminProfileLabels = [
            'id_utilisateur' => 'ID',
            'nom_utilisateur' => 'Nom d’utilisateur',
            'email' => 'Email',
            'role' => 'Rôle'
        ];
        ?>

        <div class="profile-information">

            <?php foreach ($profile as $key => $value): ?>
                <p>
                    <strong>
                        <?= $e($adminProfileLabels[$key] ?? ucfirst(str_replace('_', ' ', $key))) ?> :
                    </strong>

                    <?= $e(($value !== null && $value !== '') ? $value : '—') ?>
                </p>
            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <p>Profil administrateur introuvable.</p>

    <?php endif; ?>


<?php elseif ($page === 'overview'): ?>

    <?php foreach ($stats as $stat): ?>
        <div>
            <h3><?= $e($stat[0]) ?></h3>
            <p><?= $e($stat[1]) ?></p>
        </div>
    <?php endforeach; ?>


<?php elseif ($page === 'students'): ?>

    <details class="teachers-add">
        <summary>+ Ajouter un étudiant</summary>

        <form action="index.php?route=admin&page=students" method="POST">
            <input type="hidden" name="create_student" value="1">

            <h3>Informations du compte</h3>

            <label for="nom_utilisateur">Nom d'utilisateur</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur" required>

            <label for="email">Adresse e-mail</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>

            <h3>Informations de l'étudiant</h3>

            <label for="CNE">CNE</label>
            <input type="text" id="CNE" name="CNE" required>

            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" required>

            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" required>

            <label for="date_naissance">Date de naissance</label>
            <input type="date" id="date_naissance" name="date_naissance">

            <label for="telephone">Téléphone</label>
            <input type="tel" id="telephone" name="telephone">

            <label for="adresse">Adresse</label>
            <textarea id="adresse" name="adresse"></textarea>

            <button type="submit">Ajouter l'étudiant</button>
        </form>
    </details>

    <?php if (empty($records)): ?>

        <p>Aucun étudiant disponible.</p>

    <?php else: ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?= $e($column) ?></th>
                    <?php endforeach; ?>

                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <td><?= $e($record[$column] ?? '') ?></td>
                        <?php endforeach; ?>

                        <td>
                            <a href="index.php?route=admin&page=edit_student&id=<?= (int) $record['id_etudiant'] ?>">
                                Modifier
                            </a>

                            <form method="POST"
                                  action="index.php?route=admin&page=delete_student"
                                  onsubmit="return confirm('Voulez-vous vraiment supprimer cet étudiant ?');">

                                <input type="hidden" name="id" value="<?= (int) $record['id_etudiant'] ?>">

                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>


<?php elseif ($page === 'teachers'): ?>

    <details class="teachers-add">
        <summary>+ Ajouter un enseignant</summary>

        <form method="POST" action="index.php?route=admin&page=teachers">
            <input type="hidden" name="create_teacher" value="1">

            <h3>Informations du compte</h3>

            <label>Nom d'utilisateur</label>
            <input type="text" name="nom_utilisateur" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Mot de passe</label>
            <input type="password" name="password" required>

            <h3>Informations de l'enseignant</h3>

            <label>Matricule</label>
            <input type="text" name="matricule" required>

            <label>Nom</label>
            <input type="text" name="nom" required>

            <label>Prénom</label>
            <input type="text" name="prenom" required>

            <label>Téléphone</label>
            <input type="tel" name="telephone">

            <label>Spécialité</label>
            <input type="text" name="specialite">

            <button type="submit">Ajouter l'enseignant</button>
        </form>
    </details>

    <?php if (empty($records)): ?>

        <p>Aucun enseignant disponible.</p>

    <?php else: ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?= $e($column) ?></th>
                    <?php endforeach; ?>

                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <td><?= $e($record[$column] ?? '') ?></td>
                        <?php endforeach; ?>

                        <td>
                            <a href="index.php?route=admin&page=edit_teacher&id=<?= (int) $record['id_enseignant'] ?>">
                                Modifier
                            </a>

                            <form method="POST"
                                  action="index.php?route=admin&page=delete_teacher"
                                  onsubmit="return confirm('Voulez-vous vraiment supprimer cet enseignant ?');">

                                <input type="hidden" name="id" value="<?= (int) $record['id_enseignant'] ?>">

                                <button type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>


<?php else: ?>

    <?php if (empty($records)): ?>

        <p>Aucune donnée disponible.</p>

    <?php else: ?>

        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?= $e($column) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <td><?= $e($record[$column] ?? '') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

<?php endif; ?>
