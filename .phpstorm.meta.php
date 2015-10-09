<?php namespace PHPSTORM_META {

    /** @noinspection PhpIllegalArrayKeyTypeInspection */
    /** @noinspection PhpUnusedLocalVariableInspection */
    $STATIC_METHOD_TYPES = [
        app("")            => [
            "" instanceof Og\Application,
            "app" instanceof Og\Application,
            Og\Application::class instanceof Og\Application,

            "logger" instanceof Tracy\Firelogger,
            Tracy\Firelogger::class instanceof Tracy\Firelogger,

            "forge" instanceof Og\Forge,
            Og\Forge::class instanceof Og\Forge,

            "collection" instanceof Og\Collection,
            Og\Collection::class instanceof Og\Collection,

            "config" instanceof Og\Config,
            Og\Config::class instanceof Og\Config,

            "context" instanceof Og\Context,
            Og / Context::class instanceof Og\Context,

            "events" instanceof Og\Events,
            Og\Events::class instanceof Og\Events,

            "ioc" instanceof Illuminate\Container\Container,
            Illuminate\Container\Container::class instanceof Illuminate\Container\Container,

            "paths" instanceof Og\Paths,
            Og\Paths::class instanceof Og\Paths,

            "routing" instanceof Og\Routing,
            Og\Routing::class instanceof Og\Routing,

            "session" instanceof Aura\Session\Session,
            Aura\Session\Session::class instanceof Aura\Session\Session,

            "server" instanceof Zend\Diactoros\Server,
            Zend\Diactoros\Server::class instanceof Zend\Diactoros\Server,

            "request" instanceof Zend\Stratigility\Http\Request,
            Zend\Stratigility\Http\Request::class instanceof Zend\Stratigility\Http\Request,

            "response" instanceof Zend\Stratigility\Http\Response,
            Zend\Stratigility\Http\Response::class instanceof Zend\Stratigility\Http\Response,
        ],
        forge("")          => [
            "" instanceof Og\Forge,
            "app" instanceof Og\Application,
            Og\Application::class instanceof Og\Application,

            "forge" instanceof Og\Forge,
            Og\Forge::class instanceof Og\Forge,

            "logger" instanceof Tracy\Firelogger,
            Tracy\Firelogger::class instanceof Tracy\Firelogger,

            "collection" instanceof Og\Collection,
            Og\Collection::class instanceof Og\Collection,

            "config" instanceof Og\Config,
            Og\Config::class instanceof Og\Config,

            "context" instanceof Og\Context,
            Og / Context::class instanceof Og\Context,

            "events" instanceof Og\Events,
            Og\Events::class instanceof Og\Events,

            "ioc" instanceof Illuminate\Container\Container,
            Illuminate\Container\Container::class instanceof Illuminate\Container\Container,

            "paths" instanceof Og\Paths,
            Og\Paths::class instanceof Og\Paths,

            "routing" instanceof Og\Routing,
            Og\Routing::class instanceof Og\Routing,

            "session" instanceof Aura\Session\Session,
            Aura\Session\Session::class instanceof Aura\Session\Session,

            "server" instanceof Zend\Diactoros\Server,
            Zend\Diactoros\Server::class instanceof Zend\Diactoros\Server,

            "request" instanceof Zend\Stratigility\Http\Request,
            Zend\Stratigility\Http\Request::class instanceof Zend\Stratigility\Http\Request,

            "response" instanceof Zend\Stratigility\Http\Response,
            Zend\Stratigility\Http\Response::class instanceof Zend\Stratigility\Http\Response,
        ],
        Og\Forge::make("") => [
            "app" instanceof Og\Application,
            Og\Application::class instanceof Og\Application,

            "logger" instanceof Tracy\Firelogger,
            Tracy\Firelogger::class instanceof Tracy\Firelogger,

            "forge" instanceof Og\Forge,
            Og\Forge::class instanceof Og\Forge,

            "collection" instanceof Og\Collection,
            Og\Collection::class instanceof Og\Collection,

            "config" instanceof Og\Config,
            Og\Config::class instanceof Og\Config,

            "context" instanceof Og\Context,
            Og / Context::class instanceof Og\Context,

            "events" instanceof Og\Events,
            Og\Events::class instanceof Og\Events,

            "ioc" instanceof Illuminate\Container\Container,
            Illuminate\Container\Container::class instanceof Illuminate\Container\Container,

            "paths" instanceof Og\Paths,
            Og\Paths::class instanceof Og\Paths,

            "routing" instanceof Og\Routing,
            Og\Routing::class instanceof Og\Routing,

            "server" instanceof Zend\Diactoros\Server,
            Zend\Diactoros\Server::class instanceof Zend\Diactoros\Server,

            "request" instanceof Zend\Stratigility\Http\Request,
            Zend\Stratigility\Http\Request::class instanceof Zend\Stratigility\Http\Request,

            "response" instanceof Zend\Stratigility\Http\Response,
            Zend\Stratigility\Http\Response::class instanceof Zend\Stratigility\Http\Response,
        ],
        Og\Forge::get("")  => [
            "app" instanceof Og\Application,
            Og\Application::class instanceof Og\Application,

            "logger" instanceof Tracy\Firelogger,
            Tracy\Firelogger::class instanceof Tracy\Firelogger,

            "forge" instanceof Og\Forge,
            Og\Forge::class instanceof Og\Forge,

            "collection" instanceof Og\Collection,
            Og\Collection::class instanceof Og\Collection,

            "config" instanceof Og\Config,
            Og\Config::class instanceof Og\Config,

            "context" instanceof Og\Context,
            Og / Context::class instanceof Og\Context,

            "events" instanceof Og\Events,
            Og\Events::class instanceof Og\Events,

            "ioc" instanceof Illuminate\Container\Container,
            Illuminate\Container\Container::class instanceof Illuminate\Container\Container,

            "paths" instanceof Og\Paths,
            Og\Paths::class instanceof Og\Paths,

            "router" instanceof Og\Router,
            Og\Router::class instanceof Og\Router,

            "server" instanceof Zend\Diactoros\Server,
            Zend\Diactoros\Server::class instanceof Zend\Diactoros\Server,

            "request" instanceof Zend\Stratigility\Http\Request,
            Zend\Stratigility\Http\Request::class instanceof Zend\Stratigility\Http\Request,

            "response" instanceof Zend\Stratigility\Http\Response,
            Zend\Stratigility\Http\Response::class instanceof Zend\Stratigility\Http\Response,
        ],
    ];
}
