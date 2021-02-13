#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Gingdev\Tools\MainCommand;
use Gingdev\Facebook\Commands\LoginCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new LoginCommand());
$application->add(new MainCommand());

$application->run();
