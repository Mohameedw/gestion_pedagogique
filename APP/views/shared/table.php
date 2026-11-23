
<?php
$e = static fn($value) => htmlspecialchars(
    (string) ($value ?? ''),
    ENT_QUOTES,
    'UTF-8'
);
?>

<?php if ($records): ?>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th><?= $e(ucfirst(str_replace('_', ' ', $column))) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <td>
                            <?php if (in_array($column, ['statut_validation', 'statut', 'actif'], true)): ?>
                                <span class="status-pill">
                                    <?= $e($record[$column] ?: '—') ?>
                                </span>
                            <?php else: ?>
                                <?= $e($record[$column] ?? '—') ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php else: ?>

<div class="empty-inline">
    Aucune donnée à afficher pour le moment.
</div>

<?php endif; ?>
