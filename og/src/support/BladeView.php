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
    /** @var Compiler - the Blade compiler */
    protected $blade_compiler;

    /** @var CompilerEngine - the blade compiler engine */
    protected $blade_engine;

    /** @var ContainerInterface|Forge - the forge, mainly */
    protected $di;

    /** @var  Events - the COGS event dispatcher */
    protected $dispatcher;

    /** @var  Environment - the Blade factory/environment */
    protected $factory;

    /** @var Container - the illuminate/container/container */
    protected $ioc;

    /** @var array $settings - a subset of the global Config 'views.blade' settings */
    protected $settings;

    /** @var array - a list of paths to search for the template file */
    protected $template_paths = [];

    /** @var FileViewFinder - the Blade view finder */
    protected $view_finder;

    /** @var Context - the local Context object */
    private $context;

    /**
     * Construct a compatible environment for Blade template rendering
     *
     * @param array|NULL $settings
     *
     * @internal param Forge $container
     */
    public function __construct(array $settings = NULL)
    {
        # use the framework forge/dependency injector
        $this->di = Forge::getInstance();
        parent::__construct($this->di);

        # settings are located in the `config/views.php` configuration file.
        $this->settings = $settings ? $settings : $this->di->make('config')['views.blade'];

        # assign the local collection from the global context
        /** @var Context $copy_global_context */
        $global_context = $this->di['context'];
        $this->collection = $global_context->copy();

        # obtain the core template paths from `view.blade` settings 
        $this->template_paths = $this->settings['template_paths'];

        # Illuminate view requires an illuminate container.
        # note that forge->service serves as a controlled gateway to
        #      the encapsulated illuminate/container/container. 
        $this->ioc = $this->di->service('getInstance');

        # assign the COGS illuminate-compatible event handler to BladeView
        $this->dispatcher = $this->di->get('events');

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
     *
     * @return $this|void
     */
    public function addViewPath($path, $prepend = TRUE)
    {
        if ($prepend)
        {
            # prepend the path to the current paths
            array_unshift($this->template_paths, $path);

            # we need to reconstruct the FileViewFinder 
            $this->view_finder = new FileViewFinder(new Filesystem, $this->template_paths);
            $this->di->add('view.finder', function () { return $this->view_finder; });
        }
        else
        {
            array_push($this->template_paths, $path);
            $this->view_finder->addLocation($path);
        }

        return $this;
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
    public function hasView($template)
    {
        try
        {
            $this->view_finder->find($template);
        }
        catch (\InvalidArgumentException $e)
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Register shared dependencies.
     *
     * @return void
     */
    public function registerDependencies()
    {
        # register the resolver engines
        $this->register_engine_resolver();

        #@formatter:off
        $this->di->add('files',          function () { return new Filesystem; });
        $this->di->add('view.finder',    function () { return $this->view_finder; });
        $this->di->add('blade.compiler', function () { return $this->blade_compiler; });
        $this->di->add('blade',          function () { return $this->blade_engine; });
        #@formatter:on

        $this->collection['blade.factory'] = $this->factory;
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
     * get the array of paths from the BladeView view finder.
     *
     * @return array
     */
    public function getViewPaths()
    {
        return $this->view_finder->getPaths();
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
    private function build_blade_factory()
    {
        $this->registerDependencies();

        $this->build_view_engines();

        # build the factory

        /** @noinspection PhpParamsInspection */
        $this->factory = new Environment (
            $this->ioc->make('view.engine.resolver'),
            $this->view_finder,
            $this->dispatcher
        );

        $this->factory->setContainer($this->ioc);
    }

    /**
     * Populate local properties with required illuminate/view objects
     *
     * @return void
     */
    private function build_view_engines()
    {
        # create the view finder with the template path array
        $this->view_finder = new FileViewFinder(new Filesystem, $this->template_paths);

        # create the Blade compiler using Filesystem and cache directory
        $this->blade_compiler = new BladeCompiler(new Filesystem, $this->settings['cache']);

        # get a blade compiler engine instance 
        $this->blade_engine = new CompilerEngine($this->blade_compiler);
    }

    /**
     * Register the engine resolver instance.
     *
     * @return void
     */
    private function register_engine_resolver()
    {
        $this->di->add('view.engine.resolver', function ()
        {
            $resolver = new EngineResolver;

            $resolver->register('php', function () { return new PhpEngine; });
            $resolver->register('blade', function ()
            {
                /** @noinspection PhpParamsInspection - Quieten PhpStorm type complaint */
                return new CompilerEngine($this->di['blade.compiler'], $this->di['files']);
            });

            return $resolver;
        });
    }
}
