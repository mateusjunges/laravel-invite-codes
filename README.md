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

# Tests
Run `composer test` to test this package.

# Contributing
Thank you for considering contributing for the Laravel Watchdog package! The contribution guide can be found [here](https://github.com/mateusjunges/laravel-watchdog/blob/master/CONTRIBUTING.md).

# Changelog
Please see [changelog](https://github.com/mateusjunges/laravel-watchdog/blob/master/CHANGELOG.md) for more information about the changes on this package.

# License
The Laravel Watchdog package is open-sourced software licenced under the [MIT License](https://opensource.org/licenses/MIT). 
Please see the [License File](https://github.com/mateusjunges/laravel-watchdog/blob/master/LICENSE) for more information.
