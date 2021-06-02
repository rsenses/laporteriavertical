<?php

// Errores en archivo log o en pantalla si estamos en desarrollo
error_reporting(E_ALL);
// Errores en archivo log o en pantalla si estamos en desarrollo
ini_set('ignore_repeated_source', 0);
ini_set('ignore_repeated_errors', 1); // do not log repeating errors
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

require __DIR__ . '/../vendor/autoload.php';

$app = new flight\Engine();
$app->set('flight.log_errors', false);

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../storage/logs/' . date('Y-m-d') . '_error.log');

// source of error plays role in determining if errors are different
if ($GLOBALS['env']['debug']) {
    $app->set('flight.handle_errors', false);
    ini_set('display_errors', 1); // Mostramos los errores en pantalla
    ini_set('display_startup_errors', 1);
} else {
    $app->map('notFound', function () use ($app) {
        $error = new App\Controllers\NotFoundController($app);

        $app->response()
            ->clear()
            ->status(404)
            ->write($error->indexAction())
            ->send();
    });

    $app->map('error', function ($e) use ($app) {
        error_log($e);

        $error = new App\Controllers\NotFoundController($app);

        $app->response()
            ->clear()
            ->status(500)
            ->write($error->indexAction())
            ->send();
    });
}

// Language Initial Settings
$locale = array_keys($GLOBALS['config']['locales'])[0];

date_default_timezone_set('Europe/Madrid');
setlocale(LC_TIME, $GLOBALS['config']['locales'][$locale]);
setlocale(LC_ALL, $GLOBALS['config']['locales'][$locale]);

$app->set('locale', $locale);

// Dependency Injection
require __DIR__ . '/../src/containers.php';

// Routes
require __DIR__ . '/../src/routes.php';

// App Start
$app->start();
