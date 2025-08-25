<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'todo_app');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_pass');
define('DB_CHARSET', 'utf8mb4');

// Configuration générale
define('APP_NAME', 'Gestionnaire de Tâches');
define('APP_VERSION', '1.0.0');


// Autoload des classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
?>