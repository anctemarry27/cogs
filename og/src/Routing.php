<?php namespace Og;

/**
 * Routing
 *
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\RouteCollector as Collector;
use Og\Support\Collections\Input;
use Og\Support\Util;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ReflectionClass;
use Zend\Diactoros\Response as ClientResponse;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream as Body;

/**
 * The Routing class provides framework access to the current Request/Response
 * objects and the FastRoute dispatcher.
 */
class Routing
{
    const APP_NAMESPACE = "App\\Http\\Controllers\\";

    // FastRoute route match status
    const NOT_FOUND          = 0;
    const FOUND              = 1;
    const METHOD_NOT_ALLOWED = 2;

    // FastRoute controller::method separator is '@'
    const ROUTE_METHOD_SEPARATOR = '@';

    // FastRoute returns 3 elements on a successful route match.
    const SUCCESS_ELEMENT_COUNT = 3;

    // FastRoute result index for status is 0
    const ROUTE_STATUS_INDEX = 0;

    /**
     * @var Context
     */
    private $context;

    /** @var \FastRoute\Dispatcher */
    private $dispatcher;

    /** @var Forge */
    private $forge;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var null|string */
    private $route_filename;

    /**
     * Routing constructor.
     *
     * @param Forge   $forge
     * @param Events  $events
     * @param Context $context
     */
    public function __construct(Forge $forge, Events $events, Context $context)
    {
        $this->forge = $forge;
        $this->events = $events;
        $this->context = $context;

        // set current server request and response
        // which are dynamically retrieved from the Server object. 
        $this->request = $forge['request'];
        $this->response = $forge['response'];
    }

    /**
     * @param Body|Response $stream
     *
     * @return string
     */
    public function bodyToString($stream)
    {
        // accept either a Body or a Response for stream.
        $stream = $stream instanceof Response ? $stream->getBody() : $stream;

        // use the current stream cursor as a length or remainder.
        $stream_len = $stream->tell();

        // if the stream is empty then return an empty string.
        if ($stream_len < 1)
            return '';

        $stream->rewind();

        // return the string contents of the stream.
        return $stream->getContents();
    }

    /**
     * Evaluate the route match result and return a standardized
     * result structure.
     *
     * @param $route_match - result of FastRoute route match check.
     *
     * @return array - [<status>, <action>, <input>]
     */
    public function evaluate_route(array $route_match)
    {
        // if count($result) == 3 then collect all three route values
        // else get the status and null the rest.
        if (count($route_match) == static::SUCCESS_ELEMENT_COUNT)
        {
            list($status, $action, $input) = $route_match;

            return [$status, $action, (array) $input];
        }
        else
        {
            // an error has occurred - most likely static::NOT_FOUND or static::METHOD_NOT_ALLOWED 
            $status = $route_match[static::ROUTE_STATUS_INDEX];
            $action = $input = NULL;

            return [$status, $action, (array) $input];
        }
    }

    /**
     * Execute an HTTP route.
     *
     * @param          $input  - HTTP input fields
     * @param          $action - Route action
     *
     * @return mixed|Response
     */
    public function executeRouteAction($input, $action)
    {
        // register the current Input object 
        $this->forge->add(['input', Input::class], new Input($input));

        $this->before_route_dispatch();

        // If the route is a controller::method then first determine if the controller
        // implements __invoke(). We'll assume the signature is correct for Middleware.   
        if (is_string($action) and Util::string_has(static::ROUTE_METHOD_SEPARATOR, $action))
        {
            // extract controller and method
            list($controller, $method) = explode(static::ROUTE_METHOD_SEPARATOR, $action);

            // use the static::APP_NAMESPACE to normalize constructor names without namespace. 
            $controller = $this->normalize_namespace($controller);

            // depending on the controller constructor signature, 
            // find and instantiate the constructor
            $processor = $this->newConstructorWithInjection($controller);

            // transfer control over to the controller
            $result = $this->invoke_controller($processor, $method);

        }
        else
        {
            // Otherwise, transfer control to the controller::method directly.
            // Note that call() handles dependency injection.
            $result = $this->forge->callWithDependencyInjection($action);
        }

        $this->after_route_dispatch();

        return $result;
    }

    /**
     * Handle any events etc before the route is dispatched
     */
    private function before_route_dispatch()
    {
        // before dispatch event
        $this->events->fire(OG_BEFORE_ROUTE_DISPATCH, [$this->request, $this->response]);
    }

    /**
     * @param $class_name - name of the class to construct.
     *
     * @return array
     */
    private function newConstructorWithInjection($class_name)
    {
        // find the constructor class
        $class = new ReflectionClass($class_name);

        // extract the call signature
        $c_params = $class->getConstructor()->getParameters();

        // collect the object from the forge.
        // note that ONLY DI objects are allowed in constructors
        $args = [];

        foreach ($c_params as $c_param)
            $args[] = $this->forge[$c_param->getClass()->getName()];

        //$class->newInstanceArgs($args);

        return $class->newInstanceArgs($args);
    }

    /**
     * @param $processor
     * @param $method
     *
     * @return mixed
     */
    private function invoke_controller($processor, $method)
    {
        // determine if the controller implements a router
        if (method_exists($processor, 'routeTo'))
            // routing controller - send the $method to the controller router
            $result = $this->forge->callWithDependencyInjection([$processor, 'routeTo'], [$method]);
        else
            // standard controller
            $result = $this->forge->callWithDependencyInjection([$processor, $method]);

        return $result;
    }

    /**
     * Handle ant events etc after the route has been dispatched
     */
    private function after_route_dispatch()
    {
        // after dispatch event
        $this->events->fire(OG_AFTER_ROUTE_DISPATCH, [$this->request, $this->response]);
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
     * Verify that the route is valid.
     *
     * @param Response $response - the passed Middleware request object.
     * @param          $status   - as returned by FastRoute.
     *
     * @return null|Response
     */
    public function getRouteError(Response $response, $status)
    {
        switch ($status)
        {
            case static::NOT_FOUND:
            {
                //
                // @TODO ... 404 Not Found
                //
                $error = $this->set_simple_error($response, 404);
                break;
            }
            case static::METHOD_NOT_ALLOWED:
            {
                //
                // @TODO ... 405 Method Not Allowed
                //
                $error = $this->set_simple_error($response, 405);
                break;
            }
            default:
                $error = NULL;
                break;
        }

        return $error;
    }

    /**
     * @param Response $response
     * @param          $code
     *
     * @return Response
     */
    private function set_simple_error(Response $response, $code)
    {
        $error_message = Util::http_code($code);

        $response->getBody()
                 ->write("<span style='color:maroon'><b>$code Error</b></span> - <i>$error_message.</i>");

        return $response->withStatus($code);
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
        // the file that defines routes
        $this->route_filename = $route_filename ?: HTTP . "routes.php";

        // generate the route dispatcher and load routes
        $this->dispatcher = \FastRoute\simpleDispatcher(
            function (Collector $routes) { include $this->route_filename; },
            [
                'routeParser'    => \FastRoute\RouteParser\Std::class,
                'dataGenerator'  => \FastRoute\DataGenerator\GroupCountBased::class,
                'dispatcher'     => \FastRoute\Dispatcher\GroupCountBased::class,
                'routeCollector' => \FastRoute\RouteCollector::class,
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
        // Fetch method and uri from injected request object
        $http_method = $http_method ?: $this->request->getMethod();
        $uri = $uri ?: $this->request->getUri()->getPath();

        // parse the request and return a status array
        $routeInfo = $this->dispatcher->dispatch($http_method, $uri);

        return $routeInfo;
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

    /**
     * @param $controller
     *
     * @return string
     */
    private function normalize_namespace($controller)
    {
        $namespace = Util::parse_class_name($controller);

        if (empty($namespace['namespace_path']))
            return static::APP_NAMESPACE . $controller;

        return $controller;
    }
}
