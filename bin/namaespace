#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

ini_set('xdebug.remote_autostart', 0);
ini_set('xdebug.remote_enable', 0);
ini_set('xdebug.profiler_enable', 0);

$app = new Symfony\Component\Console\Application();
$app->add(new NamaeSpace\Command\ReplaceCommand());
$app->add(new NamaeSpace\Command\FindCommand());
$app->run();
