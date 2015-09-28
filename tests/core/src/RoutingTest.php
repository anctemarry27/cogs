<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use FastRoute\Dispatcher;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;
use Zend\Stratigility\Http\Response as HttpResponse;

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

    /** @var HttpResponse */
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
        $this->routing->makeRoutes();
        $route = $this->routing->dispatch();

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

        # append the request to the parameters
        $params += [$this->request];

        $result = call_user_func_array($target, $params);
        $this->assertEquals("Test Route [greg]", $result);
    }

}
