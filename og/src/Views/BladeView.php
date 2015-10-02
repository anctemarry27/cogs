<?php namespace Og\Views;

/**
 * @package Og
 * @version 0.1.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
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

/**
 * @note:
 *      No attempt has been made to make this class portable.
 *      It is tightly bound to the COGS framework.
 *      That may change in the future.
 */
class BladeView extends AbstractView implements ViewInterface, ArrayAccess, Renderable
{
    /** @var Compiler - the Blade compiler */
    private $blade_compiler;

    /** @var CompilerEngine - the blade compiler engine */
    private $blade_engine;

    /** @var  Environment - the Blade factory/environment */
    private $factory;

    /** @var array $settings - a subset of the global Config 'views.blade' settings */
    private $settings;

    /** @var FileViewFinder - the Blade view finder */
    private $view_finder;

    /**
     * Construct a compatible environment for Blade template rendering by
     * connecting to COGS resources and the illuminate/container/container.
     *
     * @param array|NULL $settings
     */
    public function __construct(array $settings = NULL)
    {
        # settings are located in the `config/views.php` configuration file.
        $this->settings = $settings ? $settings : $this->di->get('config')['views.blade'];

        # construct with decorated settings
        parent::__construct($this->settings);
        
        # assign the blade symbol collection from the global context
        /** @var Context $global_context */
        $global_context = $this->di->get('context');
        $this->storage = $global_context->copy();

        # obtain the core template paths from `view.blade.template_paths` settings 
        $this->template_paths = $this->settings['template_paths'];

        # Illuminate View requires an illuminate container.
        # note that forge->service serves as a controlled gateway to
        #      the encapsulated illuminate/container/container. 
        $this->illuminate_container = $this->di->container();

        # assign the COGS illuminate-compatible event handler to BladeView
        $this->events = $this->di->get('events');

        # construct the blade factory and register classes
        $this->build_blade_factory();
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
        parent::addViewPath($path, $prepend);

        # as far as I can tell, we need to reconstruct the FileViewFinder 
        $this->view_finder = new FileViewFinder(new Filesystem, $this->template_paths);

        # also, re-register the view finder. The IOC will handle any update events
        $this->di->add('view.finder', function () { return $this->view_finder; });

        # fluent
        return $this;
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

        $this->storage['blade.factory'] = $this->factory;
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
        $data = parent::collectContext($data);

        # create an illuminate view from stored factory and blade engine
        $blade_view = new IlluminateView($this->factory, $this->blade_engine, NULL, $view, (array) $data);

        # render and return the result
        return $blade_view->render();
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
            $this->illuminate_container->make('view.engine.resolver'),
            $this->view_finder,
            $this->events
        );

        $this->factory->setContainer($this->illuminate_container);
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
