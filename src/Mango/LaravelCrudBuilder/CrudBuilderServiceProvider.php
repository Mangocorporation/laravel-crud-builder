<?php namespace Mango\LaravelCrudBuilder;

use Illuminate\Support\ServiceProvider;

class CrudBuilderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands('Mango\LaravelCrudBuilder\Console\CrudMakeCommand');
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