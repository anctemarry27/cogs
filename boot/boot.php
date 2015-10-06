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
include 'helpers.php';
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
    $dotenv->overload();
}

# install Tracy if in DEBUG mode
if (getenv('DEBUG') === 'true')
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
