<?php namespace Og;

/**
 * @package Og
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use App\Middleware\Middleware;
use Og\Providers\CoreServiceProvider;
use Og\Providers\SessionServiceProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Server;
use Zend\Stratigility\Http\Request;
use Zend\Stratigility\Http\Response;

final class Application
{
    const NOTIFY_MIDDLEWARE = "app.notify.middleware";

    /** @var Config */
    private $config;

    /** @var Forge */
    private $container;

    /** @var Context */
    private $context;

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
     *
     * @param Forge      $container
     * @param Config     $config
     * @param Services   $services
     * @param Middleware $middleware
     */
    public function __construct(Forge $container, Config $config, Services $services, Middleware $middleware)
    {
        if ( ! static::$instance)
        {
            static::$instance = $this;

            $this->container = $container;
            $this->services = $services;
            $this->middleware = $middleware;
            $this->config = $config;

            //$this->services = new Services($container); # $container->getServices();

            $this->initialize();
        }

        return static::$instance;
    }

    /**
     *  Initialize the application config and services.
     */
    private function initialize()
    {
        # Core Configuration
        $this->container->singleton(['config', Config::class], $this->config);

        # register first-order services
        $this->services->addAndRegister(SessionServiceProvider::class);
        $this->services->addAndRegister(CoreServiceProvider::class);

        # assign the application context
        $this->context = $this->container['context'];

        # install the other providers located in config/providers.php
        $this->services->registerServiceProviders();

        # listen for the Middleware call
        /** @var Events $events */
        $events = $this->container->make('events');
        $events->on(static::NOTIFY_MIDDLEWARE, [$this, 'spyMiddleware']);

        # register the application
        $this->container->singleton(['app', Application::class], $this);
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
        $forge = new Forge();

        return static::$instance ?: new static($forge, new Services($forge), new Middleware($forge), new Config);
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

        $this->middleware->installMiddlewares(config('core.middleware'));
        $this->serve();

        //$response = forge()->has('Response')
        //    ? forge('Response')
        //    : Routing::makeHttpResponse('Not Found', 200);

        //expose(forge('routing')->bodyToString($response));
    }

    /**
     *  Boot support services etc.
     */
    private function boot()
    {
        # register server, request, response
        $this->initialize_server();

        # boot the service providers
        $this->services->bootAll();
    }

    /**
     * initialize the server/request/response and register with the service container.
     */
    private function initialize_server()
    {
        # create and register the server, request and response
        $this->server = $server = Server::createServer($this->middleware, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        # register the server
        $this->container->singleton(['server', Server::class], $server);

        # register request and response
        $this->container->add(['request', ServerRequestInterface::class,],
            function () use ($server)
            {
                # return the active request
                return new Request($server->{'request'});
            }
        );

        # register the response
        $this->container->add(['response', ResponseInterface::class,],
            function () use ($server)
            {
                # return the active response
                return new Response($server->{'response'});
            }
        );
    }

    /**
     * @return void
     */
    private function serve()
    {
        $this->server->listen();
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
