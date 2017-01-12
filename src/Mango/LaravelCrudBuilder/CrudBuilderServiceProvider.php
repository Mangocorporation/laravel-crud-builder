<?php namespace Mango\LaravelCrudBuilder;

use Illuminate\Support\ServiceProvider;

use File;

class CrudBuilderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands('Mango\LaravelCrudBuilder\Console\CrudViewsCommand');
        $this->commands('Mango\LaravelCrudBuilder\Console\CrudControllersCommand');
    }


    public function boot()
    {
        $this->publishes([
            __DIR__ . '/publish/mango.blade.php' => base_path('resources/views/layouts/mango.blade.php'),
        ]);
    }

    public function provides()
    {
        return ['laravel-crud-builder'];
    }
}