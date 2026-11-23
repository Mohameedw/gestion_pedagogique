<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

final class AuthController extends Controller
{
    public function index(): void
    {
        $errors = [];
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
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
                $user = (new User())->findByEmail($email);

                if (
                    $user
                    && (int) $user['actif'] === 1
                    && password_verify($password, $user['mot_de_passe_hash'])
                ) {
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $user['id_utilisateur'];
                    $_SESSION['user_name'] = $user['nom_utilisateur'];
                    $_SESSION['role'] = $user['role'];

                    $route = [
                    'ADMIN' => 'admin',
                    'ENSEIGNANT' => 'professor',
                    'ETUDIANT' => 'student'
                ];

                $this->redirect($route[$user['role']] ?? 'login');
                } else {
                    $errors[] = 'Invalid email or password.';
                }
            }
        }

        $this->render('auth/login', [
            'errors' => $errors,
            'email' => $email
        ]);
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();

        $this->redirect('login');
    }

}
