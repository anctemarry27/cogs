<?php namespace Og;

/**
 * @package Og
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Og\App\Middleware\HelloWorldMiddleware;
use Og\App\Middleware\Middleware;
use Og\Interfaces\ContainerInterface;
use Og\Interfaces\MiddlewareInterface;
use Og\Providers\CoreServiceProvider;
use Zend\Diactoros\Server;
use Zend\Stratigility\Http\Request;
use Zend\Stratigility\Http\Response;

final class Application
{
    const NOTIFY_MIDDLEWARE = "app.notify.middleware";

    /** @var Context */
    private static $context;

    /** @var Forge */
    private static $di;

    /** @var self */
    private static $instance = NULL;

    /** @var Middleware */
    private static $middleware;

    /** @var Server */
    private static $server;

    /** @var Services */
    private static $services;

    /**
     * Application constructor.
     *
     * @param Forge|ContainerInterface $forge
     * @param MiddlewareInterface      $middleware
     */
    public function __construct(ContainerInterface $forge = NULL, MiddlewareInterface $middleware = NULL)
    {
        if ( ! static::$instance)
        {
            static::$di = $forge ? $forge : Forge::getInstance();
            static::$instance = $this;
            static::$middleware = $middleware ? $middleware : new Middleware;
            static::$services = static::$di->getServices();

            $this->initialize();
        }

        return static::$instance;
    }

    /**
     * @return Application
     */
    public function getInstance()
    {
        return static::$instance ?: new static(static::$di);
    }

    /**
     * @return Middleware
     */
    public function getMiddleware()
    {
        return self::$middleware;
    }

    /**
     *  Boot services and dispatch the request.
     */
    public function run()
    {
        # boot providers, etc.
        $this->boot();

        static::$middleware->add('AuthMiddleware');
        static::$middleware->add('RoutingMiddleware');
        static::$middleware->addPath("/hello", new HelloWorldMiddleware);
        static::$middleware->add('EndOfLineMiddleware');
        static::$middleware->add('ElapsedTimeMiddleware');

        static::$server->listen();
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

        static::$context->set('application.spy_middleware.fired', ++$count);
        static::$context->set('application.spy_middleware.request_query', $request->getUri());

        $et = elapsed_time();
        $class = (new \ReflectionClass($class))->getShortName();
        $response->getBody()->write("<div><b>$class</b> middleware event fired @<b>$et</b></div>" . PHP_EOL);
    }

    /**
     *  Boot support services etc.
     */
    private function boot()
    {
        # register server, request, response
        $this->initialize_http();

        # boot the service providers
        static::$services->bootAll();
    }

    /**
     *  Initialize the application config and services.
     */
    private function initialize()
    {
        # register the application
        static::$di->singleton(['app', Application::class], $this);

        # register core services
        static::$services->addAndRegister(CoreServiceProvider::class);

        # assign the application context
        static::$context = static::$di['context'];

        # install the other providers located in config/providers.php
        static::$services->registerServiceProviders();

        # listen for the Middleware call
        di('events')->on(static::NOTIFY_MIDDLEWARE, [$this, 'spyMiddleware']);
    }

    /**
     * initialize the server/request/response and register with the service container.
     */
    private function initialize_http()
    {
        # load application routes
        //include APP . "http/routes.php";

        # crate and register the server, request and response
        static::$server = $server = Server::createServer(static::$middleware, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        # register the server
        static::$di->singleton(['server', Server::class], $server);

        # register request and response
        static::$di->add(['request', Request::class,], function () use ($server)
        {
            return new Request($server->{'request'});
        });

        # register the response
        static::$di->add(['response', Response::class,], function () use ($server)
        {
            return new Response($server->{'response'});
        });
    }

}
