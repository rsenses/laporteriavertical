<?php

$templateNameParser = new Symfony\Component\Templating\TemplateNameParser();
$filesystemLoader = new Symfony\Component\Templating\Loader\FilesystemLoader(__DIR__ . '/../src/views/%name%');

$app->register('view', 'Symfony\Component\Templating\PhpEngine', [$templateNameParser, $filesystemLoader]);

$app->register('db', 'App\Services\MyPDO', [
    "{$GLOBALS['env']['db']['dbdriver']}:host={$GLOBALS['env']['db']['dbhost']};port={$GLOBALS['env']['db']['dbport']};dbname={$GLOBALS['env']['db']['dbname']};charset={$GLOBALS['env']['db']['dbcharset']}",
    $GLOBALS['env']['db']['dbuser'],
    $GLOBALS['env']['db']['dbpass'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
]);

// Get domain name
$host = str_replace('.localhost', '', $_SERVER['SERVER_NAME']);
$hostNames = explode('.', $host);
$domain = $hostNames[count($hostNames) - 2] . '.' . $hostNames[count($hostNames) - 1];

$app->view()->addGlobal('domain', $domain);

if ($domain === 'expansion.com') {
    $logo = 'https://e00-expansion.uecdn.es/assets/desktop/master/img/logos/logo_expansion_noticia.png';
    $width = 287;
    $height = 60;
} elseif ($domain === 'marca.com') {
    $logo = 'https://e00-marca.uecdn.es/assets/v1/img/logo-marca.svg';
    $width = 287;
    $height = 81;
} else {
    $logo = 'https://e00-elmundo.uecdn.es/promociones/native/fixed-theme/img/elmundo.svg';
    $width = 287;
    $height = 38;
}

$app->view()->addGlobal('logo', $logo);
$app->view()->addGlobal('logo_width', $width);
$app->view()->addGlobal('logo_height', $height);
