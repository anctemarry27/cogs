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
use Og\Interfaces\ContainerInterface;

/**
 * The Forge class is a Service Container and Dependency Injector/Inverter.
 * Forge depends on the Illuminate\Container\Container. It implements
 * ContainerInterface - the COGS Forge interface. This provides for encapsulation
 * of other DI solutions.
 *
 * @note   Forge does _not_ encapsulate the league container but rather inherits from the same interface.
 * @author Taylor Otwell and others (illuminate), Greg Truesdell (forge)
 *
 * @ method void     alias($abstract, $alias)
 * @ method mixed    build($class)
 * @ method array    tagged($tag)
 * @ method null     tag($abstracts, $tags)
 *
 * @see    [\illuminate\container\container](https://github.com/illuminate/container)
 */
final class Forge implements ContainerInterface, ArrayAccess
{
    /** @var IlluminateContainer $container */
    private static $container;

    /** @var static $instance */
    private static $instance = NULL;

    /**
     * Forge is a final Singleton.
     *
     * The Forge requires the illuminate/container for compatibility.
     * Although the constructor exposes the dependency and auto-instantiates
     * the illuminate/container, the option is provided to instantiate the
     * illuminate/container outside of the Forge.
     *
     * @param IlluminateContainer $container
     */
    public function __construct(IlluminateContainer $container = NULL)
    {
        if ( ! static::$instance)
        {
            static::$container = $container ?: new IlluminateContainer;
            static::$instance = $this;

            $this->register_aliases();
        }

        return static::$instance;
    }

    /**
     * Register classes and aliases associated with the Forge.
     */
    private function register_aliases()
    {
        $this->singleton(['forge', Forge::class], $this->getInstance());
        static::$container->instance(['container', ContainerInterface::class], $this->getInstance());
        static::$container->instance(['ioc', IlluminateContainer::class], $this->container());
    }

    /**
     * Return an instance of the Forge.
     *
     * If the forge has already been instantiated then return the object reference.
     * Otherwise, return a new instantiation.
     *
     * @return static
     */
    static function getInstance()
    {
        /**
         * If the internal container has been instantiated then return
         * the current static instance.
         *
         * Otherwise, return a new forge instance.
         */
        return (static::$container instanceof IlluminateContainer)
            ? static::$instance
            : new static(new IlluminateContainer);
    }

    /**
     * Return a reference to, or call a method of, the embedded illuminate/container.
     *
     * The only real purpose of this method is to expose the illuminate/container
     * for use with imported or encapsulated packages that require the container.
     *
     * (ie: BladeView.)
     *
     * @todo - consider another way to do this. Breaking the 'information hiding' tenet is bad.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return IlluminateContainer|mixed|null
     */
    public function container($method = '', $parameters = NULL)
    {
        # return the encapsulated illuminate/container instance is no method name is passed
        if (empty($method))
            return static::$container;

        # otherwise, pass the method name on to the illuminate/container
        return empty($parameters)
            ? call_user_func([static::$container, $method])
            : call_user_func_array([static::$container, $method], (array) $parameters);
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
    public function __call($method, $arguments = NULL)
    {
        if ($method and method_exists(static::$container, $method))
            return $this->container($method, $arguments);
        else
            throw new \BadMethodCallException("The `$method` method is not associated with DI or its service container.");
    }

    /*
     *  Discourage cloning and serialization.
     */
    # @formatter:off
    public function __clone()     { throw new ForgeNotPermittedError('Cloning the Forge is not permitted.'); }
    public function __set_state() { throw new ForgeNotPermittedError('Setting the Forge state is not permitted.'); }
    public function __sleep()     { throw new ForgeNotPermittedError('Putting the Forge to sleep is not permitted.'); }
    public function __toString()  { return get_class($this); }
    public function __wakeup()    { throw new ForgeNotPermittedError('Waking the Forge is not permitted.'); }
    # @formatter:on

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
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
    public function offsetGet($offset)
    {
        return static::get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->add($offset, $value);
    }

    /**
     * @param mixed $offset
     *
     * @throws ForgeNotPermittedError
     */
    public function offsetUnset($offset)
    {
        static::$container->offsetUnset($offset);
    }

    /**
     * Add (bind) a abstract to an implementation with optional alias.
     *
     * Notes:
     *      $abstract is either [alias,abstract] or abstract.
     *      $concrete objects that are anonymous functions are added as instances.
     *      All other cases result in binding.
     *
     * @param string|array $abstract
     * @param mixed        $concrete
     * @param bool         $singleton
     *
     * @return $this
     */
    public function add($abstract, $concrete = NULL, $singleton = FALSE)
    {
        // normalize $abstract array for illuminate/container
        if (is_array($abstract))
        {
            list($alias, $abstract) = array_values($abstract);
            $abstract = [$abstract => $alias];
        }

        // `add` treats non-callable concretes as instances
        if (is_object($concrete) and ! is_callable($concrete))
            static::$container->instance($abstract, $concrete);
        else
            static::$container->bind($abstract, $concrete, $singleton);
    }

    /**
     * Call the given Closure / class@method and inject its dependencies.
     *
     * Note: Uses the illuminate/container `call` method.
     *
     * @param  callable|string $callback
     * @param  array           $args
     *
     * @return mixed
     */
    public function callWithDependencyInjection($callback, array $args = [])
    {
        return static::$container->call($callback, $args);
    }

    /**
     * Get a concrete item from the container.
     *
     * Note: Uses illuminate/container `bound()` and `make()` methods.
     *
     * @param  string $abstract
     * @param  array  $args
     *
     * @return mixed
     */
    public function get($abstract, array $args = [])
    {
        if (static::$container->bound($abstract))
            return static::$container->make($abstract, $args);

        throw new \InvalidArgumentException("Dependency alias `$abstract` does not exist.");
    }

    /**
     * Report whether an abstract exists in the container.
     *
     * @param string $abstract
     *
     * @return bool
     */
    public function has($abstract)
    {
        return static::$container->bound($abstract);
    }

    /**
     * Associate an abstract with a concrete object.
     *
     * @param $abstract
     * @param $instance
     */
    public function instance($abstract, $instance)
    {
        static::$container->instance($abstract, $instance);
    }

    /**
     * Check if an item is being managed as a singleton
     *
     * @param  string $alias
     *
     * @return boolean
     */
    public function isSingleton($alias)
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
    public static function make($abstract, array $args = [])
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
    public function remove($abstract)
    {
        static::$container->offsetUnset($abstract);
    }

    /**
     * @param Closure $closure
     */
    public function share(Closure $closure)
    {
        static::$container->share($closure);
    }

    /**
     * Shared is a pseudonym for `bind()` as singleton.
     *
     * @param      $abstract
     * @param null $concrete
     *
     * @return void
     */
    public function shared($abstract, $concrete = NULL)
    {
        // translate ['alias','concrete'] to ['alias'=>'concrete'] for embedded DI 
        if (is_array($abstract))
            $abstract = [$abstract[0] => $abstract[1]];

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
    public function singleton($abstract, $concrete = NULL)
    {
        $alias = NULL;

        if (is_array($abstract))
        {
            $alias = $abstract[0];
            $abstract = $abstract[1];

            # register the alias because the alias is provided
            static::$container->alias($abstract, $alias);
        }

        if ( ! is_callable($concrete))
            static::$container->singleton($abstract, function () use ($concrete) { return $concrete; });

        if (is_callable($concrete))
            static::$container->singleton($abstract, $concrete);
    }
}
