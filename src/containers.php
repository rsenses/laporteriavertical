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
