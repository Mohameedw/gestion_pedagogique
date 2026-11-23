<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Student;
use App\Models\User;

final class StudentController extends Controller
{
    private const NAV = [
        'overview' => 'Tableau de bord',
        'profile' => 'Mon profil',
        'modules' => 'Mes modules',
        'evaluations' => 'Mes évaluations',
        'grades' => 'Mes notes',
        'notifications' => 'Notifications'
    ];

    public function index(): void
    {
        $this->requireRole('ETUDIANT');

        $model = new Student();
        $userModel = new User();

        $userId = (int) $_SESSION['user_id'];

        $profile = $model->profile($userId);

        $page = $_GET['page'] ?? 'overview';

        if (!isset(self::NAV[$page])) {
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

        $records = [];
        $columns = [];

        if (
            $profile
            && in_array($page, ['modules', 'evaluations', 'grades'], true)
        ) {
            $records = $model->academicRows(
                (int) $profile['id_etudiant'],
                $page
            );

            $columns = array_keys($records[0] ?? []);
        }

        $overviewModules = $profile
            ? $model->academicRows(
                (int) $profile['id_etudiant'],
                'modules'
            )
            : [];

        $overviewColumns = array_keys($overviewModules[0] ?? []);

        $this->render('student/page', [
            'page' => $page,
            'title' => self::NAV[$page],
            'nav' => self::NAV,
            'roleLabel' => 'Étudiant',
            'roleTitle' => 'Espace étudiant',
            'userName' => $_SESSION['user_name'] ?? '',
            'profile' => $profile,
            'stats' => $profile
                ? $model->stats((int) $profile['id_etudiant'])
                : [],
            'records' => $records,
            'columns' => $columns,
            'overviewModules' => $overviewModules,
            'overviewColumns' => $overviewColumns,
            'message' => $message,
            'error' => $error
        ], ['role' => 'ETUDIANT']);
    }
}
