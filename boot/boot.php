<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Dotenv\Dotenv;
use Og\Kernel\Kernel;

include 'paths.php';
include SUPPORT . 'helpers.php';
include SUPPORT . 'messages.php';
include VENDOR . 'autoload.php';

/** @noinspection PhpUnusedLocalVariableInspection */
$forge  = new Forge(new \Illuminate\Container\Container);

// register the Config
$forge->add(['config', Config::class], Config::createFromFolder(CONFIG));

// register the Kernel
$forge->singleton(['kernel', Kernel::class], new Kernel($forge));

// set the timezone (as required by earlier versions of PHP before 7.0.0
date_default_timezone_set($forge['config']['app.timezone']);

# load environment as a requirement
if (file_exists(ROOT . '.env'))
{
    $dotenv = new Dotenv(ROOT);
    $dotenv->overload();
}
else
    throw new \LogicException('Unable to find root environment file. Did you remember to rename `.env-example?');

# install Tracy if in DEBUG mode
if (strtolower(getenv('DEBUG')) === 'true')
{
    # core debug utilities
    # note that debug requires that the environment has been loaded
    include 'debug.php';
}

// register the application instance
$forge['app'] = function () use ($forge) { return new Application($forge->make('kernel')); };
