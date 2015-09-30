<?php namespace Og;

/**
 * @package Og
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use App\Middleware\Middleware;
use Og\Providers\CoreServiceProvider;
use Og\Providers\SessionServiceProvider;
use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Stratigility\Http\Request;
use Zend\Stratigility\Http\Response;

final class Application
{
    const NOTIFY_MIDDLEWARE = "app.notify.middleware";

    /** @var Context */
    private $context;

    /** @var Forge */
    private $di;

    /** @var Middleware */
    private $middleware;

    /** @var Server */
    private $server;

    /** @var Services */
    private $services;

    /** @var self */
    private static $instance = NULL;

    /**
     * Application constructor.
     */
    public function __construct()
    {
        if ( ! static::$instance)
        {
            $this->di = Forge::getInstance();
            static::$instance = $this;
            $this->middleware = new Middleware($this->di);
            $this->services = $this->di->getServices();

            $this->initialize();
        }

        return static::$instance;
    }

    /**
     *  Initialize the application config and services.
     */
    private function initialize()
    {
        # register the application
        $this->di->singleton(['app', Application::class], $this);

        # register core services
        $this->services->addAndRegister(SessionServiceProvider::class);
        $this->services->addAndRegister(CoreServiceProvider::class);

        # assign the application context
        $this->context = $this->di['context'];

        # install the other providers located in config/providers.php
        $this->services->registerServiceProviders();

        # listen for the Middleware call
        $this->di->make('events')->on(static::NOTIFY_MIDDLEWARE, [$this, 'spyMiddleware']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * @return static
     */
    public function getInstance()
    {
        return static::$instance ?: new static;
    }

    /**
     * @return Middleware
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     *  Boot services and dispatch the request.
     */
    public function run()
    {
        # boot providers, etc.
        $this->boot();

        $this->middleware->add('AuthMiddleware');
        $this->middleware->add('RoutingMiddleware');
        $this->middleware->addPath('HelloWorldMiddleware', "/hello");
        $this->middleware->add('EndOfLineMiddleware');
        $this->middleware->add('ElapsedTimeMiddleware');

        $this->server->listen();

        $response = di()->has('Response') 
            ? di('Response') 
            : new Response(new \Zend\Diactoros\Response);   
        
        ddump(di('routing')->responseToString($response));
    }

    /**
     *  Boot support services etc.
     */
    private function boot()
    {
        # register server, request, response
        $this->initialize_http();

        # boot the service providers
        $this->services->bootAll();
    }

    /**
     * initialize the server/request/response and register with the service container.
     */
    private function initialize_http()
    {
        # create and register the server, request and response
        $this->server = $server = Server::createServer($this->middleware, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        # register the server
        $this->di->singleton(['server', Server::class], $server);

        # register request and response
        $this->di->add(['request', Request::class,],
            function () use ($server)
            {
                return new Request($server->{'request'});
            }
        );

        # register the response
        $this->di->add(['response', Response::class,],
            function () use ($server)
            {
                return new Response($server->{'response'});
            }
        );
    }

    /**
     * Middleware listener/
     *
     * @param          $class
     * @param Request  $request
     * @param Response $response
     *
     * @internal param Context $context
     */
    function spyMiddleware($class, $request, $response)
    {
        static $count = 0;

        $this->context->set('application.spy_middleware.fired', ++$count);
        $this->context->set('application.spy_middleware.request_query', $request->getUri());

        $et = elapsed_time();
        $class = (new \ReflectionClass($class))->getShortName();
        $response->getBody()->write("<div><b>$class</b> middleware event fired @<b>$et</b></div>" . PHP_EOL);
    }

}
