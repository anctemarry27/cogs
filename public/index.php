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
use Og\Services;

include "../boot/boot.php";

$forge = Forge::getInstance();
$app = new Application(
    $forge,
    new Services($forge),
    new Middleware($forge),
    Config::createFromFolder(APP_CONFIG)
);

$app->run();
