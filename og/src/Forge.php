<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Closure;
use Illuminate\Container\Container as IlluminateContainer;
use Og\Exceptions\ForgeNotPermittedError;
use Og\Support\Interfaces\ContainerInterface;

/**
 * The Forge class is a Service Container and Dependency Injector/Inverter.
 * Forge depends on the Illuminate\Container\Container. It implements two internal interfaces:
 *      ContainerInterface - the COGS Forge interface
 *
 * @note   Forge does _not_ encapsulate the league container but rather inherits from the same interface.
 * @author Taylor Otwell and others (illuminate), Phil Bennett and others (league), Greg Truesdell (DI)
 *
 * @method void     alias($abstract, $alias)
 * @method mixed    build($class)
 * @method array    tagged($tag)
 * @method null     tag($abstracts, $tags)
 *
 * @see    [\illuminate\container\container](https://github.com/illuminate/container)
 */
final class Forge implements ContainerInterface, ArrayAccess
{
    /** @var array */
    private $callables = [];

    /** @var IlluminateContainer $container */
    private static $container;

    /** @var static $instance */
    private static $instance = NULL;

    /** @var Services */
    private static $services;

    /**
     * Forge is a final Singleton.
     *
     */
    function __construct()
    {
        if ( ! static::$instance)
        {
            static::$container = new IlluminateContainer;
            static::$services = new Services($this);
            static::$instance = $this;

            # DI encapsulates illuminate and league containers
            $this->register_aliases();
        }

        return static::$instance;
    }

    /**
     * Implements calls to the encapsulated container.
     * Methods may be restricted from executing.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed|null
     */
    function __call($method, $arguments = NULL)
    {
        if ($method and method_exists(static::$container, $method))
            return $this->service($method, $arguments);
        else
            throw new \BadMethodCallException("The `$method` method is not associated with DI or its service container.");
    }

    function __clone() { throw new ForgeNotPermittedError('Cloning the DI is not permitted.'); }

    function __set_state() { throw new ForgeNotPermittedError('Setting the DI state is not permitted.'); }

    function __sleep() { throw new ForgeNotPermittedError('Putting the DI to sleep is not permitted.'); }

    function __wakeup() { throw new ForgeNotPermittedError('Waking the DI is not permitted.'); }

    /**
     * Add a (non-shared) definition to the container
     *
     * @param string|array $abstract
     * @param mixed        $concrete
     * @param bool         $singleton
     *
     * @return $this
     */
    function add($abstract, $concrete = NULL, $singleton = FALSE)
    {
        $pseudonym = NULL;

        if (is_array($abstract))
        {
            list($pseudonym, $abstract) = array_values($abstract);
            static::$container->alias($abstract, $pseudonym);
        }

        if (is_object($concrete) and ! is_callable($concrete))
            static::$container->instance($abstract, $concrete);
        else
            static::$container->bind($abstract, $concrete, $singleton);

        return $this;
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * @param  callable|string $callback
     * @param  array           $args
     *
     * @return mixed
     */
    function call($callback, array $args = [])
    {
        return static::$container->call($callback, $args);
    }

    /**
     * Get an item from the container
     *
     * @param  string $abstract
     * @param  array  $args
     *
     * @return mixed
     */
    function get($abstract, array $args = [])
    {
        if (static::$container->bound($abstract))
            return static::$container->make($abstract, $args);

        throw new \InvalidArgumentException("Forge alias `$abstract` does not exist.");
    }

    /**
     * @param string $abstract
     *
     * @return bool
     */
    function has($abstract)
    {
        return static::$container->bound($abstract);
    }

    /**
     * Add a predefined instance.
     *
     * @param $abstract
     * @param $instance
     */
    function instance($abstract, $instance)
    {
        static::$container->instance($abstract, $instance);
    }

    /**
     * Add a callable definition to the container
     *
     * @param  string   $alias
     * @param  callable $concrete
     *
     */
    function invokable($alias, callable $concrete = NULL)
    {
        $this->add($alias, $concrete);
        $this->callables[] = $alias;
    }

    /**
     * Check if an item is registered with the container
     *
     * @param  string $alias
     *
     * @return boolean
     */
    function isRegistered($alias)
    {
        $this->has($alias);
    }

    /**
     * Check if an item is being managed as a singleton
     *
     * @param  string $alias
     *
     * @return boolean
     */
    function isSingleton($alias)
    {
        return static::$container->isShared(is_string($alias) ? $alias : get_class($alias));
    }

    /**
     * Static pseudonym of get()
     *
     * @param       $abstract
     * @param array $args
     *
     * @return mixed|object
     */
    static function make($abstract, array $args = [])
    {
        return static::$container->make((string) $abstract, (array) $args);
    }

    /**
     * Remove an entry from the DI
     *
     * @param $abstract
     *
     * @return void
     */
    function remove($abstract)
    {
        static::$container->offsetUnset($abstract);
    }

    /**
     * @param      $abstract
     * @param null $concrete
     *
     * @return void
     */
    function shared($abstract, $concrete)
    {
        static::$container->bind($abstract, $concrete, TRUE);
    }

    /**
     * Add a singleton definition to the container.
     * Not sure if generally required, but this call quietly wraps non-callable $concrete
     * implementations with an anonymous function. (i.e.: Simplifies illuminate/container call.)
     *
     * @param  string|array $abstract
     * @param  mixed        $concrete
     *
     * @return void
     */
    function singleton($abstract, $concrete = NULL)
    {
        $pseudonym = NULL;

        if (is_array($abstract))
        {
            $pseudonym = $abstract[0];
            $abstract = $abstract[1];
        }

        if ( ! is_callable($concrete))
            static::$container->singleton($abstract, function () use ($concrete) { return $concrete; });

        if (is_callable($concrete))
            static::$container->singleton($abstract, $concrete);

        if ($pseudonym)
            static::$container->alias($abstract, $pseudonym);
    }

    /**
     * Adds a service provider to the container
     *
     * @param string $provider
     * @param bool   $as_dependency
     *
     * @return void
     */
    function addServiceProvider($provider, $as_dependency = FALSE)
    {
        static::$services->add($provider, $as_dependency);
    }

    /**
     * Call a closure with dependency injection.
     *
     * @param  \Closure $callback
     * @param  array    $parameters
     *
     * @return \Closure
     */
    function callWithDependencies(Closure $callback, array $parameters = [])
    {
        return static::$container->wrap($callback, $parameters);
    }

    /**
     * Determines if a definition is registered via a service provider.
     *
     * @param  string $alias
     *
     * @return boolean
     */
    function isInServiceProvider($alias)
    {
        return static::$services->isInServiceProvider($alias);
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    function offsetGet($offset)
    {
        return static::get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    function offsetSet($offset, $value)
    {
        $this->add($offset, $value);
    }

    /**
     * @param mixed $offset
     *
     * @throws ForgeNotPermittedError
     */
    function offsetUnset($offset)
    {
        static::$container->offsetUnset($offset);
    }

    /**
     * call a method of the encapsulated container.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed|null
     */
    function service($method, $parameters = NULL)
    {
        //$method = lcfirst(Str::snakecase_to_camelcase($method));

        # override encapsulated getInstance() method
        if ($method === 'getInstance')
            return static::$container;

        return empty($parameters)
            ? call_user_func([static::$container, $method])
            : call_user_func_array([static::$container, $method], (array) $parameters);
    }

    /**
     * Return an instance of the DI. Enforces Singleton status.
     *
     * @return static
     */
    static function getInstance()
    {
        return self::$instance ?: new static();
    }

    /**
     * @return Services
     */
    public static function getServices()
    {
        return self::$services;
    }

    /**
     * Register classes and aliases associated with the Forge.
     */
    private function register_aliases()
    {
        $this->singleton(['di', Forge::class], $this->getInstance());
        static::$container->instance(['container', ContainerInterface::class], $this->getInstance());
        static::$container->instance(['ioc', IlluminateContainer::class], $this->service('getInstance'));
    }

}
