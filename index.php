<?php

session_start();

spl_autoload_register(function ($class) {
    $prefix = 'App\\';

    if (str_starts_with($class, $prefix)) {
        $class = substr($class, strlen($prefix));
        $file = __DIR__ . '/APP/' . str_replace('\\', '/', $class) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
});

require_once __DIR__ . '/APP/routes/web.php';
