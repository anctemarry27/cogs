<?php

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use App\Middleware\Middleware;
use Og\Application;
use Og\Config;
use Og\Forge;

include "../boot/boot.php";

$app = new Application(
    Forge::getInstance(),
    new Middleware(Forge::getInstance()),
    Config::createFromFolder(APP_CONFIG)
);

$app->run();
