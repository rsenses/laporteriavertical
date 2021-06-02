<?php

$app->route('*', function () use ($app) {
    $request = $app->request();

    if ($request->url != '') {
        list($base, $query) = array_pad(explode('?', $request->url, 2), 2, null);

        if (substr($base, -1) == '/' && strlen($base) > 1) {
            $url = rtrim($base, '/');

            if ($query !== null) {
                $url .= '?' . $query;
            }

            $app->redirect($url, 301);
        }
    }

    return true;
});

// ==================================== Sections Routes ====================================
$app->route('GET /vivencias-empresariales', function () use ($app) {
    $index = new App\Controllers\CategoryController($app);
    echo $index->indexAction('vivencias-empresariales');
});

// ==================================== Content Routes ====================================
$app->route('GET /@slug:[a-z0-9-]+', function ($slug) use ($app) {
    $show = new App\Controllers\PostController($app);
    echo $show->showAction($slug);
});
$app->route('GET /alone/@slug:[a-z0-9-]+', function ($slug) use ($app) {
    $show = new App\Controllers\PostController($app);
    echo $show->aloneAction($slug);
});

// ==================================== Home Route ====================================
$app->route('GET /', function () use ($app) {
    $index = new App\Controllers\HomeController($app);
    echo $index->indexAction();
});
