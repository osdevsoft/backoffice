<?php

namespace Osds\Backoffice;

use Illuminate\Support\ServiceProvider;
use Twig_Environment;

class OsdsBackofficeServiceProvider extends ServiceProvider
{
    protected $middleware = [
        'middlewareName' => 'Osds\Backoffice\Laravel\RoleMiddleware'
    ];

    public function boot() {

        $this->publishes([
            __DIR__.'/database/migrations/' => database_path('migrations')
        ], 'migrations');

        foreach($this->middleware as $name => $class) {
//            $this->middleware($name, $class);
        }

    }

    public function register()
    {
        #include all routes
        $this->includeRoutes();
        $this->includeControllers();

    }

    public function includeRoutes()
    {

        $routes = glob(__DIR__ . '/../../packages/backoffice-*/Laravel/routes.php');
        foreach($routes as $r)
        {
            if(!strstr($r, 'backoffice-default'))
            {
                include $r;
            }
        }
        #this include needs to be the last one in order to give preference to the packages ones
        include __DIR__ . '/routes.php';
    }

    public function includeControllers()
    {
        $this->app->make('Osds\Backoffice\Application\Controllers\BackofficeController');
        $controllers = glob(__DIR__ . '/../../packages/backoffice-*/Application/Controllers/*.php');
        foreach($controllers as $c)
        {
            if(!strstr($c, 'backoffice-default'))
            {
                $this->app->make('Osds\Backoffice\Application\Controllers\\' . basename($c, '.php'));
            }
        }
    }
}