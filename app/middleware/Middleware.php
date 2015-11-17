<?php namespace App\Middleware;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\Application;
use Og\Events;
use Og\Forge;
use Og\Interfaces\ContainerInterface;
use Og\Interfaces\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Zend\Stratigility\Http\Request;
use Zend\Stratigility\Http\Response;
use Zend\Stratigility\MiddlewarePipe;

class Middleware extends MiddlewarePipe implements MiddlewareInterface
{
    /** @var Events */
    protected $events;

    /** @var ContainerInterface|Forge */
    protected $forge;

    /**
     * Middleware constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->forge  = Forge::getInstance();
        $this->events = $this->forge->make(Events::class);
    }

    /**
     * @param RequestInterface|Request   $request
     * @param ResponseInterface|Response $response
     * @param callable|NULL              $next
     *
     * @return Response
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next = NULL)
    {
        /** @var Events $events - cache for event object */
        //static $events;
        //$events = $events ?: $this->forge->make('events');

        # TODO: Middleware Pre-Hook?

        # call the app middleware before event

        $this->events->fire(Application::NOTIFY_MIDDLEWARE, [$this, $request, $response]);

        # call parent middleware
        return parent::__invoke($this->decorateRequest($request), $this->decorateResponse($response), $next);

        # TODO: Middleware Post-Hook?
    }

    /**
     * Decorate the Request instance
     *
     * @param RequestInterface|Request $request
     *
     * @return Request
     */
    public function decorateRequest(RequestInterface $request)
    {
        if ($request instanceof Request)
        {
            return $request;
        }

        return new Request($request);
    }

    /**
     * Decorate the Response instance
     *
     * @param ResponseInterface|Response $response
     *
     * @return Response
     */
    public function decorateResponse(ResponseInterface $response)
    {
        if ($response instanceof Response)
        {
            return $response;
        }

        return new Response($response);
    }

    /**
     * @param array $middlewares - an array of middlewares
     */
    public function loadPipeline(array $middlewares)
    {
        foreach ($middlewares as $middleware)
        {
            is_array($middleware)
                # array as ['<middleware>','<path>']
                ? $this->addPath($middleware[0], $middleware[1])
                # '<middleware>'
                : $this->add($middleware);
        }

    }

    /**
     * @param $abstract
     */
    public function add($abstract)
    {
        # if no namespace is evident then inject the Middleware namespace.

        /** @var static $abstract */
        $abstract = $this->filter_namespace($abstract);

        $this->pipe($abstract::create());
    }

    /**
     * @param $abstract
     * @param $path
     */
    public function addPath($abstract, $path)
    {
        # if no namespace is evident then inject the Middleware namespace.

        /** @var static $abstract */
        $abstract = $this->filter_namespace($abstract);

        $this->pipe($path, $abstract::create());
    }

    /**
     * @param $abstract
     *
     * @return string
     */
    private function filter_namespace($abstract)
    {
        # get the segments so we can check for a namespace
        $segments = explode('\\', $abstract);

        # if no namespace is evident then inject the Middleware namespace.
        return count($segments) == 1 ? __NAMESPACE__ . "\\$abstract" : $abstract;
    }

    /**
     * Middleware 'Factory'
     *
     * @return static
     */
    static public function create()
    {
        # cache a forge reference
        static $forge = NULL;
        $forge = $forge ?: Forge::getInstance();

        return new static($forge);
    }

}
