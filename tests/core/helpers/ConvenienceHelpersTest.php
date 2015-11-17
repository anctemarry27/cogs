<?php
namespace Og;

use Illuminate\Contracts\Container\Container as IlluminateContainer;

/**
 * Test the framework support functions
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ConvenienceHelpersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The only tests done here are for existence and validation.
     */
    public function test01_Conveniences()
    {
        /*
         * Test each function twice - first for value then after caching 
         */
        $this->assertTrue(forge() instanceof Forge);
        $this->assertTrue(forge('ioc') instanceof IlluminateContainer);

        $this->assertTrue(config() instanceof Config);
        $this->assertTrue(config() instanceof Config);

        $this->assertTrue(path() instanceof Paths);
        $this->assertTrue(path() instanceof Paths);

        $this->assertTrue(events() instanceof Events);
        $this->assertTrue(events() instanceof Events);

        //$this->assertTrue(router() instanceof Router);
        //$this->assertTrue(router() instanceof Router);

        //$this->assertTrue(routing() instanceof Routing);
        //$this->assertTrue(routing() instanceof Routing);

        $test = function () { return "test"; };
        $this->assertEquals("test", value($test));

    }

    public function test_request_input_response()
    {
        //$this->assertTrue(request() instanceof Request);
        //$this->assertTrue(response() instanceof Response);
        //
        //$this->assertTrue(input() instanceof ParameterBag);
        //$this->assertNull(input('password'));
        //
        ////$router = new Router(new RouteCollection, new RestfulStrategy);
        ////$router->get('router.test.router', '/router.test/{id}', function (Request $request, $id) { die_dump($request->attributes); return [$id]; });
        ////$result = $router->dispatch('router.test.router', '/router.test/6')->getContent();
        //
        //$this->assertTrue(response('Page not found', 404) instanceof Response);
        //$this->assertEquals('Page not found', response('Page not found', 404)->getContent());
        //$this->assertEquals(404, response('Page not found', 404)->getStatusCode());
        //$this->assertEquals(200, response('Oh happy day.')->getStatusCode());
    }

    public function test_route()
    {
        //forge('routing')->getRouter()->namedPost('test.route.name1', '/route-test1',
        //    function (Request $request, Response $response)
        //    {
        //        $response->setContent('I made it.');
        //
        //        return $response;
        //    }
        //);
        //
        //forge('router')->namedRouteDispatch('test.route.name1', '/route-test1');
        //die_dump(forge('routing')->dispatch('GET','/route-test1'));
    }

}
