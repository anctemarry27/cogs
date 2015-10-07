<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

# reset timezone to UTC before configuration. 
use App\Middleware\Middleware;
use Dotenv\Dotenv;

include 'paths.php';
include 'helpers.php';
include 'messages.php';
include VENDOR . 'autoload.php';

$forge = new Forge;
$config = Config::createFromFolder(APP_CONFIG);
date_default_timezone_set($config['app.timezone']);

# load environment
if (file_exists(ROOT . '.env'))
{
    $dotenv = new Dotenv(ROOT);
    $dotenv->overload();
}

# install Tracy if in DEBUG mode
if (getenv('DEBUG') === 'true')
{
    # core debug utilities
    # note that debug requires that the environment has been loaded
    include 'debug.php';
}

$services = new Services($forge);
$middleware = new Middleware($forge);
$app = new Application($forge, $config, $services, $middleware);
