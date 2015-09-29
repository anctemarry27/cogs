<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\routeCollector as Collector;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Riimu\Kit\UrlParser\UriParser;

//use Zend\Diactoros\Response;

class Routing
{
    /** @var \FastRoute\Dispatcher */
    private $dispatcher = NULL;

    /** @var RequestInterface */
    private $request;

    /** @var Response */
    private $response;

    /** @var null|string */
    private $route_filename;

    /** @var UriParser */
    private $url_parser;

    /**
     * Routing constructor.
     *
     * @param RequestInterface $request
     * @param Response         $response
     */
    public function __construct(RequestInterface $request, Response $response)
    {
        # construct the Uri Parser
        $this->url_parser = new UriParser();

        # assign request and response
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param $http_method
     * @param $uri
     *
     * @return array
     */
    public function dispatch($http_method = NULL, $uri = NULL)
    {
        // Fetch method and URI from somewhere
        $http_method = $http_method ?: $this->request->getMethod();
        $uri = $uri ?: $this->request->getUri()->getPath();

        $routeInfo = $this->dispatcher->dispatch($http_method, $uri);

        return $routeInfo;
    }

    /**
     * @param null $route_filename
     */
    public function makeRoutes($route_filename = NULL)
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
    }

}
