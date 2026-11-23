<?php

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = [], ?array $layout = null): void
    {
        extract($data, EXTR_SKIP);

        // Chemin vers APP/views/
        $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new \RuntimeException(
                'View not found: ' . $view . ' | Path: ' . $viewFile
            );
        }

        // Afficher uniquement la vue si aucun layout n'est demandé
        if ($layout === null) {
            require $viewFile;
            return;
        }

        // Récupérer le contenu de la vue
        ob_start();
        require $viewFile;
        $pageContent = ob_get_clean();

        // Charger le layout commun
        require dirname(__DIR__) . '/views/layouts/dashboard.php';
    }

    protected function requireRole(string $role): void
    {
        if (
            empty($_SESSION['user_id'])
            || ($_SESSION['role'] ?? null) !== $role
        ) {
            http_response_code(403);
            $this->redirect('login');
        }
    }

    protected function redirect(string $route): never
    {
        header('Location: index.php?route=' . rawurlencode($route));
        exit;
    }
}
