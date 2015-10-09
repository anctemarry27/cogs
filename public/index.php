<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

include "../boot/boot.php";

/* ***************************************************************\
 * At this point, the Application has wired-up service providers  *
 * and queued Middleware. Here you can interject any additional   *
 * settings and conditions before running the application.        *
 *                                                                *
 * Variables made available by boot.php:                          *
 *                                                                *
 *      $app         => Application                               *
 *      $config      => Config                                    *
 *      $forge       => Forge                                     *
 *      $middleware  => Middleware;                               *
 *      $services    => Services                                  *
 *                                                                *
 * ***************************************************************/

/** @var Application $app
 *
 * Normally we just run the application.
 */
$app = new Application(new Kernel(Forge::getInstance()));
$app->run();
