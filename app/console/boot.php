<?php
/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Support\ClassFinder;
use Symfony\Component\Console\Application;

# include as much of the framework as possible
include_once "boot/boot.php";

# create a new console application
$app = new Application('COGS Console',"0.1.0@dev\nSource: https://github.com/OddGreg/cogs");

# install all of the commands found in the commands folder
foreach ((new ClassFinder)->findClasses('app/console/commands') as $command)
{
    $app->add(new $command);
}

$app->run();
