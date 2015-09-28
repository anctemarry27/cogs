<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\routeCollector as Collector;
use Og\Interfaces\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Riimu\Kit\UrlParser\UriParser;
use function FastRoute\simpleDispatcher as Dispatcher;
use Zend\Diactoros\Response;

class Routing
{
    /** @var ContainerInterface|Forge */
    private $di;

    /** @var \FastRoute\Dispatcher */
    private $dispatcher = NULL;

    /** @var RequestInterface */
    private $request;

    /** @var Response */
    private $response;

    /** @var null|string */
    private $route_filename = HTTP . "routes.php";

    /** @var UriParser */
    private $url_parser;

    /**
     * Routing constructor.
     *
     * @param ContainerInterface $di
     * @param RequestInterface   $request
     * @param Response           $response
     */
    public function __construct(ContainerInterface $di, RequestInterface $request, Response $response)
    {
        # assign the dependency/service container
        $this->di = $di;

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
        ///** @var Request $request */
        //$request = $this->di['request'];

        // Fetch method and URI from somewhere
        $http_method = $http_method ?: $this->request->getMethod();
        $uri = $uri ?: $this->request->getUri()->getPath();

        $routeInfo = $this->dispatcher->dispatch($http_method, $uri);
        //switch ($routeInfo[0])
        //{
        //    case \FastRoute\Dispatcher::NOT_FOUND:
        //        # ... 404 Not Found
        //        break;
        //
        //    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        //        # $allowedMethods = $routeInfo[1];
        //        #  ... 405 Method Not Allowed
        //        break;
        //
        //    case \FastRoute\Dispatcher::FOUND:
        //
        //        return $routeInfo;
        //        # $handler = $routeInfo[1];
        //        # $vars = $routeInfo[2];
        //        #  ... call $handler with $vars
        //        break;
        //}

        return $routeInfo;
    }

    /**
     * @param null $route_filename
     */
    public function makeRoutes($route_filename = NULL)
    {
        # the file that defines routes
        $this->route_filename = $route_filename ?: $this->route_filename;

        # generate the route dispatcher and load routes
        $this->dispatcher = Dispatcher(
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
