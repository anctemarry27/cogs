<?php namespace Og;

/**
 * @package Og
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\Server;
use Zend\Stratigility\Http\Request;
use Zend\Stratigility\Http\Response;

/**
 * Application Director
 *
 * This class wires components and configurations together prior to
 * executing the server (kernel) listener.
 *
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
final class Application
{
    const NOTIFY_MIDDLEWARE = "app.notify.middleware";

    /** @var Kernel */
    private $kernel;

    /** @var Server */
    private $server;

    /** @var self */
    private static $instance = NULL;

    /**
     * Application constructor.
     *
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->initialize();

        static::$instance = $this;
    }

    /** @return string */
    public function __toString() { return get_class($this); }

    /** @return static */
    public function getInstance() { return static::$instance ?: new static(new Kernel(Forge::getInstance())); }

    /** @return Kernel */
    public function kernel() { return $this->kernel; }

    public function last_ditch()
    {
        $this->server->{'response'}->getBody()->write('Yo!');
        echo forge('routing')->bodyToString($this->server->{'response'});

        return FALSE;
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

        $this->kernel->context()->set('application.spy_middleware.fired', ++$count);
        $this->kernel->context()->set('application.spy_middleware.request_query', $request->getUri());

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
        $this->kernel->middleware()->loadPipeline(config('app.middleware'));

        # enter the Middleware loop
        $this->traverse_middleware_pipeline();

        # notify that application shutdown is imminent
        $this->kernel->events()->notify(OG_APPLICATION_SHUTDOWN, [$this]);
    }

    /**
     *  Boot support services etc.
     */
    private function boot()
    {
        # register server, request, response
        $this->initialize_server();

        $this->kernel->events()->notify(OG_APPLICATION_STARTUP, [$this]);

        # boot the service providers
        $this->kernel->services()->bootAll();
    }

    /**
     *  Initialize the application config and services.
     */
    private function initialize()
    {
        # register the application
        $this->kernel->forge()->singleton(['app', Application::class], static::$instance);

        # snoop on middleware events
        $this->kernel->events()->on(static::NOTIFY_MIDDLEWARE, [$this, 'middlewareSnooper']);
    }

    /**
     * initialize the server/request/response and register with the service container.
     */
    private function initialize_server()
    {
        # create and register the server, request and response
        $this->server = $server = Server::createServer($this->kernel->middleware(), $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        # register the server
        $this->kernel->forge()->singleton(['server', Server::class], $server);

        # register request
        $this->kernel->forge()->add(['request', ServerRequestInterface::class,],
            function () use ($server)
            {
                # return the active request
                return new Request($server->{'request'});
            }
        );

        # register the response
        $this->kernel->forge()->add(['response', ResponseInterface::class,],
            function () use ($server)
            {
                # return the active response
                return new Response($server->{'response'});
            }
        );
    }

    /**
     * "Listen" to an incoming request
     *
     * If provided a $finalHandler, that callable will be used for
     * incomplete requests.
     *
     * Output buffering is enabled prior to invoking the attached
     * callback; any output buffered will be sent prior to any
     * response body content.
     *
     * @param null|callable $finalHandler
     */
    private function traverse_middleware_pipeline($finalHandler = NULL)
    {
        $pipeline = $this->kernel->middleware();
        $emitter = new SapiEmitter();
        $this->server->setEmitter($emitter);

        ob_start();
        $bufferLevel = ob_get_level();

        # the work is done here
        $response = $pipeline($this->server->{'request'}, $this->server->{'response'}, $finalHandler);

        if ( ! $response instanceof ResponseInterface)
            $response = $this->server->{'response'};

        $emitter->emit($response, $bufferLevel);
    }
}
