<?php namespace Og\Support\Services;

/**
 * @package Radium Codex
 * @author  : Greg Truesdell <odd.greg@gmail.com>
 */

use Aura\Router\Generator;
use Aura\Router\Route;
use Aura\Router\RouteCollection;
use Aura\Router\RouteFactory;
use Aura\Router\Router;
use Og\Forge;
use Og\Support\AuraRouteExtender;
use Og\Support\Interfaces\ContainerInterface;
use Og\Support\Interfaces\RouteServiceInterface;
use Og\Support\Traits\WithEvents;

/**
 * Aura Router Service
 *
 * @package Radium Codex
 */
class AuraRouteService implements RouteServiceInterface
{
    use WithEvents;

    /** @var array */
    protected $action_list;

    /** @var ContainerInterface|Forge */
    protected $forge;

    /** @var Route */
    protected $route;

    /** @var RouteCollection */
    protected $routeCollection;

    /** @var Router */
    protected $router;

    /**
     * @var array
     */
    private $server;

    /**
     * @param ContainerInterface $forge
     * @param                    $server
     */
    public function __construct(ContainerInterface $forge, $server)
    {
        $this->forge = $forge;
        $this->server = $server;

        $factory = new RouteFactory();
        $this->routeCollection = new RouteCollection($factory);
        $generator = new Generator();
        $router = new Router($this->routeCollection, $generator);

        $this->router = $router;
        $this->forge->instance(\router, $this->router);
        $this->forge->bindShared(\routes, function () use ($router) { return $router->getRoutes(); });
    }

    /**
     * Redirect method calls that match Aura Route methods to Aura.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->router, $name))
        {
            return call_user_func_array([$this->router, $name], $arguments);
        }
        else
            raise(new AuraRouteServiceInvalidMethodError("Call to non-existent Router method `$name`.", E_USER_ERROR));

        return NULL;
    }

    /**
     * Get the internal (Aura) Router instance.
     *
     * The same effect as calling ($this)->getRouter().
     *
     * @return Router
     */
    public function __invoke()
    {
        return $this->router;
    }

    /**
     * Get the list of matched route parameters ready for dispatch logic.
     *
     * @param null|string $index
     *
     * @return array
     */
    public function getActionList($index = NULL)
    {
        return $index ? $this->action_list[$index] : $this->action_list;
    }

    /**
     * @param Route $route
     *
     * @return array
     */
    public function getMethod(Route $route)
    {
        AuraRouteExtender::setRoute($route);

        return AuraRouteExtender::getMethod();
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routeCollection;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    public function getRoutes()
    {
        return $this->router->getRoutes();
    }

    public function getValues()
    {
        return $this->route->values;
    }

    /**
     * @param $path
     *
     * @return Route
     */
    public function match($path)
    {
        $found = $this->router->match($path, $this->server);

        if ($found instanceof Route)
        {
            $this->route = $found;
            $this->action_list = new \ArrayObject([
                'name' => $found->name,
                'path' => $found->path,
                'params' => new \ArrayObject($found->params, \ArrayObject::ARRAY_AS_PROPS),
                'values' => new \ArrayObject($found->values, \ArrayObject::ARRAY_AS_PROPS),
                'matches' => $found->matches,
            ], \ArrayObject::ARRAY_AS_PROPS);

            $this->notify(\Radium\NOTIFY_ROUTING_MATCH, [&$found]);
        }
        else
        {
            /** @var Route $fail */
            $fail = $this->router->getFailedRoute();

            // inspect the failed route
            if ($fail->failedMethod())
            {
                $code = 405;
                $this->notify(\Radium\NOTIFY_ROUTING_FAIL, compact('code', 'fail', 'path'));
            }
            elseif ($fail->failedAccept())
            {
                $code = 406;
                $this->notify(\Radium\NOTIFY_ROUTING_FAIL, compact('code', 'fail', 'path'));
            }
            else
            {
                $code = 404;
                $fail = 'Page not found.';
                $this->notify(\Radium\NOTIFY_ROUTING_FAIL, compact('code', 'fail', 'path'));
            }
        }

        return $this->route;
    }

    /**
     * @param null $name
     * @param null $function
     * @param null $path
     * @param null $route_descriptor
     *
     * @return Route|RouteCollection|null
     * @throws \Exception
     */
    public function route($name = NULL, $function = NULL, $path = NULL, $route_descriptor = NULL)
    {
        /** @var RouteCollection $router */
        $router = $this->router;

        # declare the route targets
        $controller = $action = $class = NULL;

        # handle the case where route($name) returns the named route if it exists
        if ($name and ! ($function || $path || $route_descriptor))
        {
            # query the route collection
            if ($router->offsetExists($name))
                return $router->offsetGet($name);
            else
                return NULL;
        }

        if ($name)
        {
            # concatenate 'add' to the function name to build a valid call
            $method = 'add' . ucfirst($function);

            # extract 1, 2 or 3 segments from the route
            if (is_callable($route_descriptor))
                # if the route is a closure, then define the closure as the controller.
                return $router->{$method}($name, $path)->addValues(['controller' => $route_descriptor]);

            # deconstruct the route declaration by number of elements
            switch (str_word_count($route_descriptor))
            {
                # [controller]@[method]:[class]

                case 3:
                    if (strpos($route_descriptor, '@') and strpos($route_descriptor, ':'))
                    {
                        list($controller, $action) = explode('@', $route_descriptor);
                        list($action, $class) = explode(':', $action);
                    };

                    break;

                # [controller]@[method]

                case 2:
                    if (strpos($route_descriptor, '@'))
                    {
                        list($controller, $action) = explode('@', $route_descriptor);
                        $class = NULL;
                    };

                    break;

                # [controller]

                case 1:
                    $controller = $route_descriptor;
                    $action = 'index';
                    $class = NULL;

                    break;

                default :
                    $error = debug_backtrace(2)[0];
                    raise(new AuraRouteServiceInvalidParameterError(
                        'The route parameter to router() is not valid. String value expected.',
                        E_USER_ERROR,
                        $error['file'],
                        $error['line']
                    ));

                    break;
            }

            # send the route to the Aura Router
            $route = $router->{$method}($name, $path)->addValues(compact('controller', 'action', 'class'));
            # create an action list that represents the route
            $this->action_list[$route_descriptor] = compact('route', 'controller', 'action', 'class');

            $this->route = $route;

            return $route;
        }

        return $router;

    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed|null
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (method_exists(static::getRouter(), $name))
        {
            return call_user_func_array([static::getRouter(), $name], $arguments);
        }
        else
            raise(new AuraRouteServiceInvalidMethodError("Static call to non-existent Router method `$name`.", E_USER_ERROR));

        return NULL;
    }

}

#@formatter:off
    class AuraRouteServiceInvalidParameterError extends \Exception {} 
    class AuraRouteServiceInvalidMethodError extends \Exception {} 
