<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Grade;
use App\Models\Professor;
use App\Models\User;
use RuntimeException;
use Throwable;

final class ProfessorController extends Controller
{
    private const NAV = [
        'overview' => 'Tableau de bord',
        'modules' => 'Mes modules',
        'students' => 'Mes étudiants',
        'evaluations' => 'Évaluations',
        'grades' => 'Notes',
        'notifications' => 'Notifications'
    ];

    public function index(): void
    {
        $this->requireRole('ENSEIGNANT');

        $model = new Professor();
        $gradeModel = new Grade();
        $userModel = new User();

        $userId = (int) $_SESSION['user_id'];

        $profile = $model->profile($userId);

        // Supprimer les clés numériques PDO du profil
        if ($profile) {
            $profile = array_filter(
                $profile,
                fn($key) => !is_numeric($key),
                ARRAY_FILTER_USE_KEY
            );
        }

        $page = $_GET['page'] ?? 'overview';

        $allowedPages = array_merge(
            array_keys(self::NAV),
            ['profile']
        );

        if (!in_array($page, $allowedPages, true)) {
            $page = 'overview';
        }

        $message = '';
        $error = '';

        // Changement du mot de passe
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && $page === 'profile'
            && isset($_POST['change_password'])
        ) {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (
                $currentPassword === ''
                || $newPassword === ''
                || $confirmPassword === ''
            ) {
                $error = 'Veuillez remplir tous les champs.';
            } elseif (strlen($newPassword) < 8) {
                $error = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Les nouveaux mots de passe ne correspondent pas.';
            } elseif ($currentPassword === $newPassword) {
                $error = 'Le nouveau mot de passe doit être différent de l’ancien.';
            } else {
                $result = $userModel->changePassword(
                    $userId,
                    $currentPassword,
                    $newPassword
                );

                if ($result === 'success') {
                    $message = 'Votre mot de passe a été modifié avec succès.';
                } elseif ($result === 'invalid_current') {
                    $error = 'Votre mot de passe actuel est incorrect.';
                } elseif ($result === 'not_found') {
                    $error = 'Utilisateur introuvable.';
                } else {
                    $error = 'Erreur lors du changement de mot de passe.';
                }
            }
        }

        // Enregistrement d'une note
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['save_grade'])
        ) {
            try {
                if (!$profile) {
                    throw new RuntimeException(
                        'Aucun profil enseignant n’est rattaché à ce compte.'
                    );
                }

                $evaluationId = filter_input(
                    INPUT_POST,
                    'id_evaluation',
                    FILTER_VALIDATE_INT
                );

                $registrationId = filter_input(
                    INPUT_POST,
                    'id_inscription',
                    FILTER_VALIDATE_INT
                );

                $value = filter_input(
                    INPUT_POST,
                    'valeur',
                    FILTER_VALIDATE_FLOAT
                );

                if (
                    !$evaluationId
                    || !$registrationId
                    || $value === false
                    || $value === null
                ) {
                    throw new RuntimeException(
                        'Vérifiez les champs de la note.'
                    );
                }

                $gradeModel->saveForProfessor(
                    (int) $profile['id_enseignant'],
                    $evaluationId,
                    $registrationId,
                    $value,
                    trim($_POST['observation'] ?? '')
                );

                $message = 'Note enregistrée.';
                $page = 'grades';

            } catch (Throwable $exception) {
                $error = $exception->getMessage();
                $page = 'grades';
            }
        }

        $records = [];
        $columns = [];

        $overviewModules = $profile
            ? $model->modules((int) $profile['id_enseignant'])
            : [];

        $overviewColumns = [
            'code_module',
            'nom_module',
            'nom_filiere',
            'annee',
            'semestre',
            'statut'
        ];

        if ($profile) {
            if ($page === 'modules') {
                $records = $model->modules(
                    (int) $profile['id_enseignant']
                );
                $columns = $overviewColumns;
            }

            if ($page === 'students') {
                $records = $model->students(
                    (int) $profile['id_enseignant']
                );
                $columns = array_keys($records[0] ?? []);
            }

            if ($page === 'evaluations') {
                $records = $model->evaluations(
                    (int) $profile['id_enseignant']
                );
                $columns = array_keys($records[0] ?? []);
            }

            if ($page === 'grades') {
                $records = $model->gradeRows(
                    (int) $profile['id_enseignant']
                );
                $columns = array_keys($records[0] ?? []);
            }
        }

        $titles = self::NAV;
        $titles['profile'] = 'Mon profil';

        $this->render('professor/page', [
            'page' => $page,
            'title' => $titles[$page],
            'nav' => self::NAV,
            'roleLabel' => 'Professeur',
            'roleTitle' => 'Espace enseignant',
            'userName' => $_SESSION['user_name'] ?? '',
            'profile' => $profile,
            'stats' => $profile
                ? $model->stats((int) $profile['id_enseignant'])
                : [],
            'records' => $records,
            'columns' => $columns,
            'overviewModules' => $overviewModules,
            'overviewColumns' => $overviewColumns,
            'message' => $message,
            'error' => $error,
            'evaluationOptions' => $profile
                ? $gradeModel->evaluationOptions(
                    (int) $profile['id_enseignant']
                )
                : [],
            'registrationOptions' => $profile
                ? $gradeModel->registrationOptions(
                    (int) $profile['id_enseignant']
                )
                : []
        ], ['role' => 'ENSEIGNANT']);
    }
}
