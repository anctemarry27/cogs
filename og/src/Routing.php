<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\routeCollector as Collector;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Routing
{
    /** @var \FastRoute\Dispatcher */
    private $dispatcher;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var null|string */
    private $route_filename;

    /**
     * Routing constructor.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * makeRoutes creates the dispatcher and parses the supplied route file
     * (a PHP executable) based on the standard settings.
     *
     * Encapsulates FastRoute.
     *
     * @param null $route_filename
     *
     * @return $this
     */
    public function make($route_filename = NULL)
    {
        # the file that defines routes
        $this->route_filename = $route_filename ? $route_filename : HTTP . "routes.php";

        # generate the route dispatcher and load routes
        $this->dispatcher = \FastRoute\simpleDispatcher(
            function (Collector $routes) { include $this->route_filename; },
            [
                'routeParser' => 'FastRoute\\RouteParser\\Std',
                'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
                'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
                'routeCollector' => 'FastRoute\\RouteCollector',
            ]
        );

        return $this;
    }

    /**
     * Look for a route that matches the request.
     *
     * @param $http_method
     * @param $uri
     *
     * @return array
     */
    public function match($http_method = NULL, $uri = NULL)
    {
        # Fetch method and uri from injected request object
        $http_method = $http_method ?: $this->request->getMethod();
        $uri = $uri ?: $this->request->getUri()->getPath();

        # parse the request and return a status array
        $routeInfo = $this->dispatcher->dispatch($http_method, $uri);

        return $routeInfo;
    }

}
