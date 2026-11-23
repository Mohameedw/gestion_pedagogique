<?php

$route = $_GET['route'] ?? 'login';

switch ($route) {
    case 'admin':
        (new App\Controllers\AdminController())->index();
        break;

    case 'login':
        (new App\Controllers\AuthController())->index();
        break;

    case 'logout':
        (new App\Controllers\AuthController())->logout();
        break;

    case 'professor':
        (new App\Controllers\ProfessorController())->index();
        break;

    case 'student':
        (new App\Controllers\StudentController())->index();
        break;

    default:
        http_response_code(404);
        echo 'Page introuvable.';
        break;
}