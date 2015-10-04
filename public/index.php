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

$forge = new Forge;
$config = Config::createFromFolder(APP_CONFIG);
$services = new Services($forge);
$middleware = new Middleware($forge);

$app = new Application($forge, $config, $services, $middleware);

$app->run();
