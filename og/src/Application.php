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

    /** @var Context */
    private $context;

    /** @var Events */
    private $events;

    /** @var Forge */
    private $forge;

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
     * @param Forge      $forge
     * @param Config     $config
     * @param Services   $services
     * @param Middleware $middleware
     */
    public function __construct(Forge $forge, Config $config, Services $services, Middleware $middleware)
    {
        $this->forge = $forge;
        $this->services = $services;
        $this->middleware = $middleware;
        $this->config = $config;

        $this->initialize();

        static::$instance = $this;
    }

    /**
     *  Initialize the application config and services.
     */
    private function initialize()
    {
        $this->register_core_services();

        # global events object
        $this->events = $this->forge['events'];

        # assign the application context
        $this->context = $this->forge['context'];

        # listen for the Middleware call

        /** @var Events $events */
        $events = $this->forge->make('events');
        $events->on(static::NOTIFY_MIDDLEWARE, [$this, 'middlewareSnooper']);

    }

    private function register_core_services()
    {
        # register the application
        $this->forge->singleton(['app', Application::class], $this);

        # Core Configuration
        $this->forge->singleton(['config', Config::class], $this->config);

        # load the provider list
        $this->services->configure((array) $this->config['core.providers']);
        
        # install the other providers located in config/core.php
        $this->services->registerServiceProviders();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Events
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return static
     */
    public function getInstance()
    {
        $forge = new Forge();

        return static::$instance ?: new static($forge, new Config, new Services($forge), new Middleware($forge));
    }

    /**
     * @return Middleware
     */
    public function getMiddleware()
    {
        return $this->middleware;
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
    function middlewareSnooper($class, $request, $response)
    {
        static $count = 0;

        $this->context->set('application.spy_middleware.fired', ++$count);
        $this->context->set('application.spy_middleware.request_query', $request->getUri());

        $et = elapsed_time();
        $class = (new \ReflectionClass($class))->getShortName();
        $response->getBody()->write("<div><b>$class</b> middleware event fired @<b>$et</b></div>" . PHP_EOL);
    }

    /**
     *  Boot services and dispatch the request.
     */
    public function run()
    {
        # boot providers, etc.
        $this->boot();

        # queue-up the Middleware
        $this->middleware->loadPipeline(config('app.middleware'));

        # enter the Middleware loop
        $this->runMiddleware();

        # notify that application shutdown is imminent
        $this->events->fire(OG_APPLICATION_SHUTDOWN, [$this]);
    }

    /**
     *  Boot support services etc.
     */
    private function boot()
    {
        # register server, request, response
        $this->initialize_server();

        $this->events->fire(OG_APPLICATION_STARTUP, [$this]);

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
        $this->forge->singleton(['server', Server::class], $server);

        # register request
        $this->forge->add(['request', ServerRequestInterface::class,],
            function () use ($server)
            {
                # return the active request
                return new Request($server->{'request'});
            }
        );

        # register the response
        $this->forge->add(['response', ResponseInterface::class,],
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
    private function runMiddleware()
    {
        $this->server->listen();
    }

}
