#!/usr/bin/env php
<?php

require_once __DIR__ . "/App.php";



$params["user"] = isset($argv[1]) ? $argv[1] : null;
$params["password"] = isset($argv[2]) ? $argv[2] : null;
$params["owner"] = isset($argv[3]) ? $argv[3] : null;
$params["repo"] = isset($argv[4]) ? $argv[4] : null;

$configFile = "./Config/Config.yaml";

$app = new App();
$app->start();

$merge =  new Command\Merge(App::Config());
$merge->pullRequest($params);
