<?php

namespace Iamgerwin\LaravelApiScaffold;

use Iamgerwin\LaravelApiScaffold\Commands\MakeServiceCommand;
use Illuminate\Support\ServiceProvider;

class LaravelApiScaffoldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/api-scaffold.php', 'api-scaffold');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/api-scaffold.php' => config_path('api-scaffold.php'),
            ], 'api-scaffold-config');

            $this->publishes([
                __DIR__.'/../resources/stubs' => resource_path('stubs/vendor/api-scaffold'),
            ], 'api-scaffold-stubs');

            $this->commands([
                MakeServiceCommand::class,
            ]);
        }
    }
}
