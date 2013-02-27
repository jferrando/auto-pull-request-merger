#!/usr/bin/env php
<?php

require_once './vendor/autoload.php';


//require_once './vendorsrc/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

$loader->useIncludePath(true);

$loader->registerNamespaces(
    array(
        'Symfony' => __DIR__ . './vendor/symfony/symfony/src',
        'Library' => __DIR__ . './Library',
        'Command' => __DIR__ . './Command'
    )
);

$loader->register();

/*
require_once './Commands/Merge.php';
require_once './Library/Git/Git.php';
require_once './Library/hipchat-php/src/HipChat/HipChat.php';
require_once './Library/GitHub-API-PHP/lib/autoloader.class.php';
*/


$configFile = "./Config/Config.yaml";
$user = isset($argv[1]) ? $argv[1] : null;
$password = isset($argv[2]) ? $argv[2] : null;
$owner = isset($argv[3]) ? $argv[3] : null;
$repo = isset($argv[4]) ? $argv[4] : null;
$merge = new Command\Merge($configFile);
$merge->pullRequest($user, $password, $owner, $repo);
