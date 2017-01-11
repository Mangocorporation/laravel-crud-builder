<?php namespace Mango\LaravelCrudGenerator;

use Illuminate\Support\ServiceProvider;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands('Mango\LaravelCrudGenerator\Console\CrudMakeCommand');
    }


    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../publish/mango.blade.php' => base_path('resources/views/layouts/mango.blade.php'),
        ]);

    }

    public function provides()
    {
        return ['laravel-crud-generator'];
    }
}