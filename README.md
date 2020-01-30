# Laravel Watchdog

This package allows you to easily manage invite codes for your Laravel application.

<p align="center">
    <a href="https://packagist.org/packages/mateusjunges/laravel-watchdog" target="_blank"><img src="https://poser.pugx.org/mateusjunges/laravel-acl/d/total.svg" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/mateusjunges/laravel-watchdog" target="_blank"><img src="https://poser.pugx.org/mateusjunges/laravel-acl/v/stable.svg" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/mateusjunges/laravel-watchdog" target="_blank"><img src="https://poser.pugx.org/mateusjunges/laravel-acl/license.svg" alt="License"></a>
    <a href="https://travis-ci.org/mateusjunges/laravel-watchdog"><img src="https://img.shields.io/travis/mateusjunges/laravel-watchdog/master.svg?style=flat" alt="Build Status"></a>
</p>

# Documentation

## Installation

To get started with Laravel Watchdog, use Composer to add the package to your project's dependencies:

```bash
composer require mateusjunges/laravel-watchdog
```
Or add this line in your composer.json, inside of the require section:
```bash
{
    "require": {
        "mateusjunges/laravel-watchdog": "^1.0",
    }
}
```
then run `composer install`.

After installing the laravel watchdog package, register the service provider in your `config/app.php` file:

> Optional in Laravel 5.5 or above

```php
'providers' => [
    Junges\Watchdog\WatchdogEventServiceProvider::class,
    Junges\Watchdog\WatchdogEventServiceProvider::class,
];
```

All migrations required for this package are already included. If you need to customize the tables, you can [publish them][migrations] with:

```bash
php artisan vendor:publish --provider="Junges\Watchdog\WatchdogServiceProvider" --tag="watchdog-migrations"
```

and set the config for `custom_migrations` to `true`, which is `false` by default.

```php
'custom_migrations' => true,
```

After the migrations has been published you can create the tables on your database by running the migrations:

```bash
php artisan migrate
```

If you change the table names on migrations, please publish the config file and update the tables array. You can publish the config file with:

```bash
php artisan vendor:publish --provider="Junges\Watchdog\WatchdogServiceProvider" --tag="watchdog-config"
```
When published, the `config/watchdog.php` config file contains:


```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    |  Models
    |--------------------------------------------------------------------------
    |
    | When using this package, we need to know which Eloquent Model should be used
    | to retrieve your invites. Of course, it is just the basics models
    | needed, but you can use whatever you like.
    |
     */
    "models" => [
        "invite_model" => \Junges\Watchdog\Http\Models\Invite::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    | Specify the basics authentication tables that you are using.
    | Once you required this package, the following tables are
    | created by default when you run the command
    |
    | php artisan migrate
    |
    | If you want to change this tables, please keep the basic structure unchanged.
    |
     */
    "tables" => [
        "invites_table" => "invites"
    ],

    /*
   |--------------------------------------------------------------------------
   | User
   |--------------------------------------------------------------------------
   | To use the ProtectedByInviteCode middleware provided by this package, you need to
   | specify the email column you use in the model you use for authentication.
   | If not specified, only invite code with no use restrictions can be used in this middleware.
   |
    */
    "user" => [
        "email_column" => "email"
    ]
];
```

# Tests
Run `composer test` to test this package.

# Contributing
Thank you for considering contributing for the Laravel Watchdog package! The contribution guide can be found [here](https://github.com/mateusjunges/laravel-watchdog/blob/master/CONTRIBUTING.md).

# Changelog
Please see [changelog](https://github.com/mateusjunges/laravel-watchdog/blob/master/CHANGELOG.md) for more information about the changes on this package.

# License
The Laravel Watchdog package is open-sourced software licenced under the [MIT License](https://opensource.org/licenses/MIT). 
Please see the [License File](https://github.com/mateusjunges/laravel-watchdog/blob/master/LICENSE) for more information.



[migrations]: https://github.com/mateusjunges/laravel-watchdog/tree/master/database/migrations
