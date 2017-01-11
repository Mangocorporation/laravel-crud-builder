# Laravel 5 CRUD Builder

[![Build Status](https://travis-ci.org/raulmangolin/laravel-crud-builder.svg?branch=master)](https://travis-ci.org/raulmangolin/laravel-crud-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/raulmangolin/laravel-crud-builder.svg?style=flat)](https://packagist.org/packages/raulmangolin/laravel-crud-builder)
[![Latest Stable Version](https://img.shields.io/packagist/v/raulmangolin/laravel-crud-builder.svg?style=flat)](https://packagist.org/packages/raulmangolin/laravel-crud-builder)

CRUD builder inspired by CakePHP's bake. With this component you can create simple admin views to start your code faster. 

All views are in Twitter Bootstrap scheme to make life easier for everyone. 

###Installation

```
composer require raulmangolin/laravel-crud-builder
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
        'Html' => Collective\Html\HtmlFacade::class, //You probably already have this installed to
    ]
```

### How do I do this magic?

If you want to create admin views to **User model**, in your terminal just run this command:

```
    php artisan mango:views Users
```

After that you will receive 4 questions:

* Create index view? (yes/no):
* Create show view? (yes/no):
* Create edit view? (yes/no):
* Create create view? (yes/no):

Just answer this questions and be happy!

### Do I need to do anything else?

Maybe. :)

All views are based in your Model name, because of this they use variables like `$users` and `$user`.

If your index method in your UsersController return all data like `return view('users.index', compact('users'))` it will work fine.

Otherwise you can change this variables in your views or controller.

The next version will come with a controller builder to make it easier.

### Twitter Bootstrap layout
When you install this component, we add a bootstrap based layout (`mango.blade.php`) in your `views/layout`. 