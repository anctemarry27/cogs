<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use App\Middleware\Middleware;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function test01()
    {
        $app = new Application(
            Forge::getInstance(),
            Config::createFromFolder(APP_CONFIG),
            new Services(Forge::getInstance()),
            new Middleware(Forge::getInstance())
        );

        $this->assertEquals($app, app('app'));
    }

}
