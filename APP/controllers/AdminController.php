<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AdminDashboard;
use App\Models\AdminAccount;
use PDOException;

final class AdminController extends Controller
{
    private const NAV = [
        'overview'       => 'Vue d’ensemble',
        'teachers'       => 'Enseignants',
        'students'       => 'Étudiants',
        'departments'    => 'Départements',
        'formations'     => 'Formations',
        'filieres'       => 'Filières',
        'modules'        => 'Modules',
        'registrations'  => 'Inscriptions',
        'evaluations'    => 'Évaluations',
        'grades'         => 'Notes',
        'announcements'  => 'Annonces',
        'notifications'  => 'Notifications'
    ];

    public function index(): void
    {
        $this->requireRole('ADMIN');

        $page = $_GET['page'] ?? 'overview';

        $allowedPages = [
            'edit_student',
            'delete_student',
            'edit_teacher',
            'delete_teacher',
            'profile'
    ];

        if (
            !isset(self::NAV[$page])
            && !in_array($page, $allowedPages, true)
        ) {
            $page = 'overview';
        }

        $message = '';
        $error = '';

        $editStudent = null;
        $editTeacher = null;

        $account = new AdminAccount();

        // Ajouter un étudiant
        if (
    ($page === 'add_student' && $_SERVER['REQUEST_METHOD'] === 'POST') ||
    ($page === 'students' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_student']))
) {
            try {
                $account->createStudent($_POST);
                $message = 'Étudiant ajouté avec succès.';

            } catch (PDOException $e) {
                $error = $e->getCode() === '23000'
                    ? 'Cet email, nom d’utilisateur ou CNE existe déjà.'
                    : 'Erreur lors de l’ajout de l’étudiant.';

            } catch (\Throwable $e) {
                $error = 'Erreur lors de l’ajout de l’étudiant.';
            }
        }

        // Ajouter un enseignant depuis la page Enseignants
if (
    $page === 'teachers' &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['create_teacher'])
) {
    try {
        $account->createTeacher($_POST);
        $message = 'Enseignant ajouté avec succès.';

    } catch (PDOException $e) {
        $error = $e->getCode() === '23000'
            ? 'Cet email, nom d’utilisateur ou matricule existe déjà.'
            : 'Erreur lors de l’ajout de l’enseignant.';

    } catch (\Throwable $e) {
        $error = 'Erreur lors de l’ajout de l’enseignant.';
    }
}

        // Modifier un étudiant
        if ($page === 'edit_student') {
            $studentId = (int) ($_GET['id'] ?? 0);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $account->updateStudent($studentId, $_POST);
                    $message = 'Étudiant modifié avec succès.';

                } catch (PDOException $e) {
                    $error = $e->getCode() === '23000'
                        ? 'Ce CNE existe déjà.'
                        : 'Erreur lors de la modification.';

                } catch (\Throwable $e) {
                    $error = 'Erreur lors de la modification.';
                }
            }

            $editStudent = $account->findStudent($studentId);

            if (!$editStudent) {
                $error = 'Étudiant introuvable.';
                $page = 'students';
            }
        }

        // Supprimer un étudiant
        if ($page === 'delete_student') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $account->deleteStudent((int) ($_POST['id'] ?? 0));
                    $message = 'Étudiant supprimé avec succès.';

                } catch (\RuntimeException $e) {
                    $error = $e->getMessage();

                } catch (\Throwable $e) {
                    $error = 'Erreur lors de la suppression de l’étudiant.';
                }
            }

            $page = 'students';
        }

        // Modifier un enseignant
        if ($page === 'edit_teacher') {
            $teacherId = (int) ($_GET['id'] ?? 0);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $account->updateTeacher($teacherId, $_POST);
                    $message = 'Enseignant modifié avec succès.';

                } catch (PDOException $e) {
                    $error = $e->getCode() === '23000'
                        ? 'Ce matricule existe déjà.'
                        : 'Erreur lors de la modification.';

                } catch (\Throwable $e) {
                    $error = 'Erreur lors de la modification.';
                }
            }

            $editTeacher = $account->findTeacher($teacherId);

            if (!$editTeacher) {
                $error = 'Enseignant introuvable.';
                $page = 'teachers';
            }
        }

        // Supprimer un enseignant
        if ($page === 'delete_teacher') {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $account->deleteTeacher((int) ($_POST['id'] ?? 0));
                    $message = 'Enseignant supprimé avec succès.';

                } catch (\RuntimeException $e) {
                    $error = $e->getMessage();

                } catch (\Throwable $e) {
                    $error = 'Erreur lors de la suppression de l’enseignant.';
                }
            }

            $page = 'teachers';
        }

        $model = new AdminDashboard();
        $adminProfile = null;

if ($page === 'profile') {
    $adminProfile = $account->findAdminProfile(
        (int) $_SESSION['user_id']
    );
}
        $title = match ($page) {
            'edit_student' => 'Modifier un étudiant',
            'edit_teacher' => 'Modifier un enseignant',
            default => self::NAV[$page] ?? 'Administration'
        };

        $data = [
            'page'        => $page,
            'title'       => $title,
            'nav'         => self::NAV,
            'roleLabel'   => 'Administrateur',
            'roleTitle'   => 'Espace administration',
            'userName'    => $_SESSION['user_name'] ?? '',
            'stats'       => $page === 'overview' ? $model->overview() : [],
            'records'     => [],
            'columns'     => [],
            'message'     => $message,
            'error'       => $error,
            'profile'     => $adminProfile,
            'editStudent' => $editStudent,
            'editTeacher' => $editTeacher
        ];

        if (
            $page !== 'overview'
            && !in_array($page, [
                'announcements',
                'notifications',
                'edit_student',
                'edit_teacher'
            ], true)
        ) {
            $table = $model->page($page);

            if ($table) {
                $data = array_merge($data, $table);
            }
        }

        $this->render('admin/page', $data, ['role' => 'ADMIN']);
    }
}
