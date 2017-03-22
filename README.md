# Laravel 5 CRUD Builder

[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE)
[![Build Status](https://travis-ci.org/Mangocorporation/laravel-crud-builder.svg?branch=master)](https://travis-ci.org/Mangocorporation/laravel-crud-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/mangocorporation/laravel-crud-builder.svg?style=flat)](https://packagist.org/packages/mangocorporation/laravel-crud-builder)
[![Latest Stable Version](https://img.shields.io/packagist/v/mangocorporation/laravel-crud-builder.svg?style=flat)](https://packagist.org/packages/mangocorporation/laravel-crud-builder)

CRUD builder inspired by CakePHP's bake. With this component you can create simple admin views and controller to start your project faster. 

All views are in Twitter Bootstrap scheme to make life easier for everyone. 

### Installation

```
composer require mangocorporation/laravel-crud-builder
```

After install, don't forget to add this lines in your `config/app.php`:

``` php
    'providers' => [
        // ...
        Mango\LaravelCrudBuilder\CrudBuilderServiceProvider::class,
        Collective\Html\HtmlServiceProvider::class, //You probably already have this installed to
    ]
```

And the alias from [The Laravel Collective](https://github.com/laravelcollective)'s components:
``` php
    'aliases' => [
        // ...
        'Form' => Collective\Html\FormFacade::class, //You probably already have this installed to
    ]
```

### How do I do this magic?
In this example below we will imagine that you have a model named **User** and will work with it.

It' simple, just run this commands in your terminal:

#### Make Controller
```
    php artisan mango:controller User
```

This will create the `UsersController.php` file in the appropriate location with the following methods:
* index
* create
* edit
* show


#### Make Views

```
    php artisan mango:views User
```

After that you will receive 4 questions:

* Generate index view? (yes/no):
* Generate create view? (yes/no):
* Generate edit view? (yes/no):
* Generate show view? (yes/no):

Just answer this questions and be happy!

### Do I need to do anything else?

Maybe. :) If you already have a controller ready, you may need to change some things.

All views are based in your Model name, because of this they use variables like `$users` and `$user`.

If your index method in your UsersController return all data like `return view('users.index', compact('users'))` it will work fine.

Otherwise you can change this variables names in your views or controller.

### Twitter Bootstrap layout
When you install this component, we add a bootstrap based layout (`mango.blade.php`) in your `views/layout`. 
