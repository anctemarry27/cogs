<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use FastRoute\Dispatcher;
use Og\Support\Cogs\Collections\Input;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

/**
 * Test the framework core classes
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class RoutingTest extends \PHPUnit_Framework_TestCase
{
    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var Routing */
    private $routing;

    public function setUp()
    {
        $this->request = (new ServerRequest)->withMethod('GET')->withUri(new Uri('/test/greg'));
        $this->response = new Response();
        $this->routing = new Routing($this->request, $this->response);
    }

    public function test_Routing()
    {
        //$this->routing->makeRoutes();
        $route = $this->routing
            ->makeRoutes(TEST_PATH . '/tests/core/app/routes.php')
            ->dispatch();

        # status will be in the following:
        #   NOT_FOUND = 0;
        #   FOUND = 1;
        #   METHOD_NOT_ALLOWED = 2;
        $status = $route[0];

        # get the target callable
        $target = $route[1];

        # get the request parameters
        $params = $route[2];

        # should be found with correct parameters
        $this->assertTrue($status === Dispatcher::FOUND);
        $this->assertTrue($params === ['name' => 'greg']);

        # simulate routing middleware for this test case
        $result = call_user_func_array($target, [new Input($route[2]), new Response()]);

        $this->assertEquals("Test Route [greg]", $result,
            'Test route should return `Test Route [greg]`.');
    }

}
