<?php namespace Og\Support;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Illuminate\Container\Container as Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Compilers\Compiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory as Environment;
use Illuminate\View\FileViewFinder;
use Illuminate\View\View as IlluminateView;
use Og\Context;
use Og\Events;
use Og\Forge;
use Og\Interfaces\ContainerInterface;
use Og\Interfaces\Renderable;
use Og\Interfaces\ViewInterface;
use Og\Views;

class BladeView extends Views implements ViewInterface, ArrayAccess, Renderable
{
    /** @var Compiler */
    protected $blade_compiler;

    /** @var CompilerEngine */
    protected $blade_engine;

    /** @var  Events */
    protected $dispatcher;

    /** @var  Environment */
    protected $factory;

    /** @var ContainerInterface */
    protected $forge;

    /** @var Container */
    protected $ioc;

    /** @var array $settings */
    protected $settings;

    /** @var array */
    protected $template_path = [];

    /** @var FileViewFinder */
    protected $view_finder;

    /** @var Context */
    private $context;

    /**
     * Construct a compatible environment for Blade template rendering
     *
     * @param Forge      $container
     * @param array|NULL $settings
     */
    public function __construct($container, array $settings = NULL)
    {
        parent::__construct($container);

        $this->forge = $container;
        # settings are located in the `config/views.php` configuration file.
        $this->settings = $settings ? $settings : config('views.blade');

        # get the global context                                                        
        //$this->context = clone $this->forge->get('context');
        $this->collection = (new Context($this->forge, $this->forge->get('context')))->copy();
        //$this->context['clone.test'] = "This is a clone test";

        //ddump([
        //    'local clone' => $this->context,
        //    'global' => $this->container->get('context'),
        //    'test clone' => clone $this->container->get('context')
        //]);

        $this->template_path = $this->settings['template_paths'];

        # Illuminate view requires an illuminate container.
        # Fortunately, we've registered a illuminate container 
        # when loading application providers.
        $this->ioc = $this->forge->service('getInstance');

        # instantiate an illuminate event dispatcher
        $this->dispatcher = $this->forge->get('events');

        # construct the blade factory and register classes
        $this->build_blade_factory();
    }

    /**
     * Implements a value-as-method syntax.
     * ie: context->{thing}('is this') where 'thing' exists in the context.
     *
     * This funky hint-of-templates syntax implements the following:
     *
     *      blade_view->{'thing'} = 'string'
     *      blade_view->{'thing'}('string')[->...]
     *
     * @param  string $method
     * @param  array  $arguments
     *
     * @return $this
     */
    public function __call($method, $arguments)
    {
        $this->context[$method] = count($arguments) > 0 ? $arguments[0] : TRUE;

        return $this;
    }

    /**
     * Append or Prepend (default) a path to the BladeView template_paths setting.
     *
     * @param string $path    : path to append to the template_path setting
     * @param bool   $prepend : TRUE to push the new path on the top, FALSE to append
     */
    public function add_template_path($path, $prepend = TRUE)
    {
        # grab a copy of the Blade template_paths configuration
        $view_settings = &config('views.blade');

        # for merging
        $work_path = [\VIEWS . $path];

        # get a copy of the app settings for merging
        $settings = config()->copy(); # $this->app->settings;

        # more often than not, prepend-ing will be the norm.
        if ($prepend)
        {
            # overwrite the modified blade template settings
            array_push($settings['views']['blade']['template_paths'], $work_path[0]);
        }
        # otherwise, append the path
        else
        {
            # overwrite the modified blade template settings
            $settings['views']['blade']['template_paths'] = array_merge($view_settings['template_paths'], $work_path);
        }

        # commit the change to the container
        config()->replace($settings);
    }

    /**
     * Construct the Blade factory and intermediate objects.
     *
     *  Laravel Blade Templating is embedded into illuminate/view and therefore requires
     *  the Container, Events and Filesystem components are present. Requiring
     *  illuminate/view should be enough.
     *
     * @return void
     */
    public function build_blade_factory()
    {
        $this->register_dependencies();

        $this->build_view_engines();

        # build the factory

        /** @noinspection PhpParamsInspection */
        $this->factory = new Environment (
            $this->ioc['view.engine.resolver'],
            $this->view_finder,
            $this->dispatcher
        );

        $this->factory->setContainer($this->ioc);
    }

    /**
     * Collect content from the shared View Context (etc.)
     *
     * @param array $merge_data
     *
     * @return mixed
     */
    public function collectContext($merge_data = [])
    {
        return (array) $this->merge($merge_data)->copy();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->ioc;
    }

    /**
     * @param string $template
     *
     * @return bool
     */
    public function has_view($template)
    {
        expose(['has_view' => $template]);
        // TODO: Implement has_view() method.
    }

    /**
     * Renders a Blade template with passed and stored symbol data.
     *
     * @param string $view - view name i.e.: 'sample' resolves to [template_path]/sample.blade.php
     * @param array  $data
     *
     * @return string
     */
    public function render($view, $data = [])
    {
        # resolve the view path based on the template paths and the view name
        $view = $this->view_finder->find($view);

        # collect data from the context and other places 
        $data = $this->collectContext($data);

        # create an illuminate view from stored factory and blade engine
        $blade_view = new IlluminateView($this->factory, $this->blade_engine, NULL, $view, (array) $data);

        # render and return the result
        return $blade_view->render();
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    protected function registerEngineResolver()
    {
        $this->forge->add('view.engine.resolver', function ()
        {
            $resolver = new EngineResolver;

            $resolver->register('php', function () { return new PhpEngine; });
            $resolver->register('blade', function ()
            {
                /** @noinspection PhpParamsInspection */
                return new CompilerEngine($this->forge['blade.compiler'], $this->forge['files']);
            });

            return $resolver;
        });
    }

    /**
     * Populate local properties with required illuminate/view objects
     *
     * @return void
     */
    protected function build_view_engines()
    {
        # create the view finder with the template path array
        $this->view_finder = new FileViewFinder(new Filesystem, $this->template_path);

        # create the Blade compiler using Filesystem and cache directory
        $this->blade_compiler = new BladeCompiler(new Filesystem, $this->settings['cache']);

        # get a blade compiler engine instance 
        $this->blade_engine = new CompilerEngine($this->blade_compiler);
    }

    /**
     * Register shared dependencies.
     *
     * @return void
     */
    public function register_dependencies()
    {
        # register the resolver engines
        $this->registerEngineResolver();

        #@formatter:off
            $this->forge->add('files',          function () { return new Filesystem; });
            //$this->di->add('events',         function () { return new Dispatcher($this->ioc); });
            $this->forge->add('view.finder',    function () { return $this->view_finder; });
            $this->forge->add('blade.compiler', function () { return $this->blade_compiler; });
            $this->forge->add('blade',          function () { return $this->blade_engine; });
            #@formatter:on

        $this->collection['blade.factory'] = $this->factory;
    }
}
