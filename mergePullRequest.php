#!/usr/bin/env php
<?php

require_once __DIR__ . "/App.php";



$user = isset($argv[1]) ? $argv[1] : null;
$password = isset($argv[2]) ? $argv[2] : null;
$owner = isset($argv[3]) ? $argv[3] : null;
$repo = isset($argv[4]) ? $argv[4] : null;

$configFile = "./Config/Config.yaml";

$app = new App();
$app->start();

$merge =  new Command\Merge(App::Config());
$merge->pullRequest($user, $password, $owner, $repo);
