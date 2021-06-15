<?php

namespace Arquiteto;

use Arquiteto;
use Config;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Log;
use Muleta\Traits\Providers\ConsoleTools;

use Route;

use Arquiteto\Facades\Arquiteto as ArquitetoFacade;
use Arquiteto\Services\ArquitetoService;

class ArquitetoProvider extends ServiceProvider
{
    use ConsoleTools;

    public $packageName = 'arquiteto';
    const pathVendor = 'ricasolucoes/arquiteto';

    public static $aliasProviders = [
        'Arquiteto' => \Arquiteto\Facades\Arquiteto::class,
    ];

    public static $providers = [
        
    ];

    /**
     * Rotas do Menu
     */
    public static $menuItens = [
        
    ];

    /**
     * Alias the services in the boot.
     */
    public function boot()
    {
        
        // Register configs, migrations, etc
        $this->registerDirectories();

        // COloquei no register pq nao tava reconhecendo as rotas para o adminlte
        $this->app->booted(
            function () {
                $this->routes();
            }
        );
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }


        /**
         * Transmissor; Routes
         */
        $this->loadRoutesForRiCa(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'routes');
    }

    /**
     * Register the services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            $this->getPublishesPath('config/arquiteto.php'),
            'arquiteto'
        );
        

        $this->setProviders();
        // $this->routes();



        // Register Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $loader = AliasLoader::getInstance();
        $loader->alias('Arquiteto', ArquitetoFacade::class);

        $this->app->singleton(
            'arquiteto',
            function () {
                return new Arquiteto();
            }
        );
        
        /*
        |--------------------------------------------------------------------------
        | Register the Utilities
        |--------------------------------------------------------------------------
        */
        /**
         * Singleton Arquiteto
         */
        $this->app->singleton(
            ArquitetoService::class,
            function ($app) {
                Log::info('Singleton Arquiteto');
                return new ArquitetoService(\Illuminate\Support\Facades\Config::get('arquiteto'));
            }
        );

        // Register commands
        $this->registerCommandFolders(
            [
            base_path('vendor/ricasolucoes/arquiteto/src/Console/Commands') => '\Arquiteto\Console\Commands',
            ]
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'arquiteto',
        ];
    }

    /**
     * Register configs, migrations, etc
     *
     * @return void
     */
    public function registerDirectories()
    {
        // Publish config files
        $this->publishes(
            [
            // Paths
            $this->getPublishesPath('config'.DIRECTORY_SEPARATOR.'arquiteto.php') => config_path('arquiteto.php'),
            ],
            ['config',  'sitec', 'sitec-config']
        );

        // // Publish arquiteto css and js to public directory
        // $this->publishes([
        //     $this->getDistPath('arquiteto') => public_path('assets/arquiteto')
        // ], ['public',  'sitec', 'sitec-public']);

        $this->loadViews();
        $this->loadTranslations();
    }

    private function loadViews()
    {
        // View namespace
        $viewsPath = $this->getResourcesPath('views');
        $this->loadViewsFrom($viewsPath, 'arquiteto');
        $this->publishes(
            [
            $viewsPath => base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'arquiteto'),
            ],
            ['views',  'sitec', 'sitec-views']
        );
    }
    
    private function loadTranslations()
    {
        // Publish lanaguage files
        $this->publishes(
            [
            $this->getResourcesPath('lang') => resource_path('lang'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'arquiteto')
            ],
            ['lang',  'sitec', 'sitec-lang', 'translations']
        );

        // Load translations
        $this->loadTranslationsFrom($this->getResourcesPath('lang'), 'arquiteto');
    }
}
