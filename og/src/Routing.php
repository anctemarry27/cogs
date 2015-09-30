<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\routeCollector as Collector;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Zend\Diactoros\Response as ClientResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream as Body;
use Zend\Stratigility\Http\Response as HttpResponse;

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
     * @param Body|Response $stream
     *
     * @return string
     */
    public function bodyToString($stream)
    {
        # accept either a Body or a Response for stream.
        $stream = $stream instanceof Response ? $stream->getBody() : $stream; 

        # use the current stream cursor as a length or remainder.
        $stream_len = $stream->tell();

        # if the stream is empty then return an empty string.
        if ($stream_len < 1)
            return '';

        $stream->rewind();

        # return the string contents of the stream.
        return $stream->getContents();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
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
    public function makeDispatcher($route_filename = NULL)
    {
        # the file that defines routes
        $this->route_filename = $route_filename ? $route_filename : HTTP . "routes.php";

        # generate the route dispatcher and load routes
        $this->dispatcher = \FastRoute\simpleDispatcher(
            function (Collector $routes) { include $this->route_filename; },
            [
                'routeParser'    => 'FastRoute\\RouteParser\\Std',
                'dataGenerator'  => 'FastRoute\\DataGenerator\\GroupCountBased',
                'dispatcher'     => 'FastRoute\\Dispatcher\\GroupCountBased',
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

    /**
     * @param string $body
     * @param int    $status
     * @param array  $headers
     *
     * @return HttpResponse
     */
    public static function makeHttpResponse($body = '', $status = 200, $headers = [])
    {
        $response = new HttpResponse(new ClientResponse());
        $response->withStatus($status)->getBody()->write($body);

        return $response;
    }

    /**
     * @return \Zend\Stratigility\Http\Request
     */
    public static function makeRequest()
    {
        return new \Zend\Stratigility\Http\Request(ServerRequestFactory::fromGlobals());
    }

    /**
     * @param string $body
     *
     * @return ClientResponse
     */
    public static function makeResponse($body = '')
    {
        $response = new ClientResponse;
        $response->getBody()->write($body);

        return $response;
    }
}
