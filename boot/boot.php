<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use App\Middleware\Middleware;
use Dotenv\Dotenv;
use Tracy\Debugger;

include 'paths.php';
include 'conveniences.php';
include 'messages.php';

include VENDOR . 'autoload.php';

# include illuminate support helpers
# - mainly for BladeViews and other compatibilities.
# - note that this MUST follow conveniences.php - not before.
//include SUPPORT . "illuminate/support/helpers.php";

# load environment
if (file_exists(ROOT . '.env'))
{
    $dotenv = new Dotenv(ROOT);
    $dotenv->load();
}

# install Tracy if in DEBUG mode
if (getenv('DEBUG') !== 'false')
{
    Debugger::$maxDepth = 6;
    Debugger::enable(Debugger::DEVELOPMENT);
    Debugger::$showLocation = TRUE;
    # core debug utilities
    # note that debug requires that the environment has been loaded
    include 'debug.php';
}

$forge = new Forge;
$config = Config::createFromFolder(APP_CONFIG);
$services = new Services($forge);
$middleware = new Middleware($forge);

$app = new Application($forge, $config, $services, $middleware);

//$app->getEvents()->on(OG_AFTER_ROUTE_DISPATCH,
//    function ($request, $response) use ($app, $forge)
//    {
//        /** @var Routing $routing */
//        $routing = $forge['routing'];
//        $result = $routing->bodyToString($response);
//
//        echo($result);
//    }
//);
