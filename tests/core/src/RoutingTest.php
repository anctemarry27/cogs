<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use FastRoute\Dispatcher;
use Og\Kernel\Kernel;
use Og\Support\Collections\Input;
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

    /** @var HttpServer */
    private $server;

    public function setUp()
    {
        $this->request  = (new ServerRequest)->withMethod('GET')->withUri(new Uri('/test/greg'));
        $this->response = new Response;

        $forge         = new Forge();
        $this->server  = new HttpServer(new Kernel($forge));
        $this->routing = new Routing($forge, new Events, new Context);
    }

    public function test_Routing()
    {
        
        //$request = new Request('/controller/amiguchi','GET');
        //die_dump($request);
        
        //$this->routing->makeRoutes();
        $route = $this->routing
            ->makeDispatcher(TEST_PATH . '/tests/core/app/routes.php')
            ->match();

        # status will be in the following:
        #   NOT_FOUND = 0;
        #   FOUND = 1;
        #   METHOD_NOT_ALLOWED = 2;
        $status = array_key_exists(0, $route) ? $route[0] : NULL;

        # get the target callable
        $target = array_key_exists(1, $route) ? $route[1] : NULL;

        # get the request parameters
        $params = array_key_exists(2, $route) ? $route[2] : NULL;
        

        # should be found with correct parameters
        //$this->assertTrue($status === Dispatcher::FOUND);
        //$this->assertTrue($params === ['name' => 'greg']);

        # simulate routing middleware for this test case
        //$result = call_user_func_array($target, [new Input($route[2]), new Response()]);

        //$this->assertEquals("Test Route [greg]", $result,
        //    'Test route should return `Test Route [greg]`.');
    }

}
