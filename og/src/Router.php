<?php namespace Og;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use FastRoute\Dispatcher;
use FastRoute\routeCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\Http\Request;
use Zend\Stratigility\Http\Response;

class Router
{
    protected $arguments = [];

    /** @var Dispatcher */
    protected $router;

    /**
     * Constructor. Set Dispatcher instance
     *
     * @param Dispatcher $router
     */
    public function __construct(Dispatcher $router = NULL)
    {
        $router = $router ?:
            \FastRoute\simpleDispatcher(
                function (routeCollector $routes)
                {
                    $path = APP . "http/routes.php";
                    include $path;
                },
                [
                    'routeParser' => 'FastRoute\\RouteParser\\Std',
                    'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
                    'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
                ]
            );

        #set the router
        $this->router = $router;
    }

    /**
     * Execute the middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($this->router === NULL)
            throw new \RuntimeException('No FastRoute\\Dispatcher instance has been provided');

        $route = $this->router->dispatch($request->getMethod(), $request->getUri()->getPath());

        if ($route[0] === Dispatcher::NOT_FOUND)
            return $response->withStatus(404);

        if ($route[0] === Dispatcher::METHOD_NOT_ALLOWED)
            return $response->withStatus(405);

        foreach ($route[2] as $name => $value)
            $request = $request->withAttribute($name, $value);

        $response = self::executeTarget($route[1], $request, $response, $this->arguments);

        return $response;
    }

    /**
     * Extra arguments passed to the controller
     *
     * @return self
     */
    public function arguments()
    {
        $this->arguments = func_get_args();

        return $this;
    }

    /**
     * Extra arguments passed to the controller
     *
     * @param Dispatcher $router
     *
     * @return self
     */
    public function router(Dispatcher $router)
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Execute the target
     *
     * @param mixed                          $target
     * @param ServerRequestInterface|Request $request
     * @param ResponseInterface|Response     $response
     *
     * @param array                          $extraArguments
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    protected static function executeTarget($target, ServerRequestInterface $request, ResponseInterface $response, $extraArguments = [])
    {
        try
        {
            ob_start();

            $arguments = array_merge([$request, $response], $extraArguments);
            $target = static::getCallable($target, $arguments);
            $return = call_user_func_array($target, $arguments);

            if ($return instanceof ResponseInterface)
            {
                $response = $return;
                $return = '';
            }

            $return = ob_get_contents() . $return;
            $body = $response->getBody();

            if ($return !== '' && $body->isWritable())
            {
                $body->write($return);
            }

            return $response;
        }
        catch (\Exception $exception)
        {
            throw $exception;
        }
        finally
        {
            if (ob_get_level() > 0)
            {
                ob_end_clean();
            }
        }
    }

    /**
     * Resolves the target of the route and returns a callable
     *
     * @param mixed $target
     * @param array $construct_args
     *
     * @throws \RuntimeException If the target is not callable
     *
     * @return callable
     */
    protected static function getCallable($target, array $construct_args)
    {
        if (is_string($target))
        {
            //is a static function
            if (function_exists($target))
                return $target;

            //is a class "class_name::method"
            if (strpos($target, '::') === FALSE)
            {
                $class = $target;
                $method = '__invoke';
            }
            else
                list($class, $method) = explode('::', $target, 2);

            if ( ! class_exists($class))
            {
                throw new \RuntimeException("The class {$class} does not exists");
            }

            $class = new \ReflectionClass($class);
            $instance = $class->hasMethod('__construct') ? $class->newInstanceArgs($construct_args) : $class->newInstance();
            $target = [$instance, $method];
        }

        if (is_callable($target))
            return $target;

        throw new \RuntimeException('The route target is not callable');
    }
}
