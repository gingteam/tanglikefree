#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use Gingdev\Tools\MainCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new MainCommand());

$application->run();
