<?php

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite. "php composer.phar install --dev"');
}

require_once $file;
require_once __DIR__ . "/../App.php";
use Symfony\Component\ClassLoader\UniversalClassLoader;


$classLoader = new UniversalClassLoader();
$classLoader->useIncludePath(true);
$classLoader->registerNamespaces(
    array(
        'Symfony' => __DIR__ . '/vendor/symfony/symfony/src',
        'Library' => __DIR__ . '/Library',
        'Command' => __DIR__ . '/Command',
        'Listener' => __DIR__ . '/Listener',
        'System' => __DIR__ . '/Library/System'
    )
);
$classLoader->register();