#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

ini_set('xdebug.remote_autostart', 0);
ini_set('xdebug.remote_enable', 0);
ini_set('xdebug.profiler_enable', 0);

use Symfony\Component\Console\Application;
use NamaeSpace\Command\ReplaceCommand;

$app = new Application();
$app->add(new ReplaceCommand());
//$app->add(new FindCommand($parser, $traverser));
$app->run();
