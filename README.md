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

## Usage
This package provides a middleware called `ProtectedByInviteCodeMiddleware`. If you want to use it to protect your routes, you need to register it in
your `$routeMiddleware` array, into `app/Http/Kernel.php` file:


```php
$routeMiddleware = [
    'protected_by_invite_codes' => ProtectedByInviteCodeMiddleware::class,
];
```

Now you can protect your routes using middleware rules:

```php
Route::get('some-route', function() {
    //
})->middleware('protected_by_invite_codes');
```
You can also add it to the `__construct()`, in your controllers:

```php
public function __construct()
{
    $this->middleware('protected_by_invite_codes');
}
```

# Creating invite codes
To create a new invite code, you must use the `Watchdog` facade. Here is a simple example:

```php
$invite_code = \Junges\Watchdog\Facades\Watchdog::create()
    ->expiresAt('2020-02-01')
    ->maxUsages(10)
    ->restrictUsageTo('contato@mateusjunges.com')
    ->save();
```

The code above will create a new invite code, which can be used 10 times only by a logged in user who has the specified email `contato@mateusjunges.com`.

The methods you can use with the `Watchdog` facade are listed below:

### Set the expiration date of your invite code

To set the expiration date of your invite code you can use one of the following methods:

- `expiresAt()`: This method accept a date string in `yyyy-mm-dd` format or a `Carbon` instance, and set the expiration date to the specified date.
- `expiresIn()`: This method accept an integer, and set the expiration date to now plus the specified amount of days.

### Restrict usage to some specific user:

To restrict the usage of an invite code you can use the `restrictUsageTo()` method, and pass in the `email` of the user who will be able to use this invite code.

### Set the maximum allowed usages for an invite code:

If you want that your invite code be used a limited amount of times, you can set the max usages limit with the `maxUsages()` method, and pass an integer with the amount
of allowed usages.

Also, you can use the declarative syntax, and use the `canBeUsedXTimes()` method, where `X` is the amount of times your invite code will be usable.
For example:

- `->canBeUsed10Times()`: This invite code can be used 10 times.
- `->canBeUsed50Times()`: This invite code can be used 50 times.

> You can use any integer number you want with this method.

### Create multiple invite codes

If you want to create more than one invite code with the same configs, you can use the `make()` method.
This method generate the specified amount of invite codes. For example:

```php
\Junges\Watchdog\Facades\Watchdog::create()
    ->maxUsages(10)
    ->expiresIn(30)
    ->make(10);
```

The code above will create 10 new invite codes which can be used 10 times each, and will expire in 30 days from now.

# Redeeming invite codes
To redeem a invite code, you can use the `redeem` method:

```php
\Junges\Watchdog\Facades\Watchdog::redeem('YOUR-INVITE-CODE');
```
When any invite is redeemed, the `InviteRedeemedEvent` will be dispatched.

# Handling watchdog exceptions

If you want to override the default `403` response, you can catch the exceptions using the laravel exception handler:

```php
public function render($request, Exception $exception)
{
    if ($exception instanceof \Junges\Watchdog\Exceptions\InviteWithRestrictedUsageException) {
        //
    }
    if ($exception instanceof \Junges\Watchdog\Exceptions\ExpiredInviteCodeException) {
            //
    }
    if ($exception instanceof \Junges\Watchdog\Exceptions\DuplicateInviteCodeException) {
        //
    }
    if ($exception instanceof \Junges\Watchdog\Exceptions\InvalidInviteCodeException) {
            //
    }
    if ($exception instanceof \Junges\Watchdog\Exceptions\UserLoggedOutException) {
        //
    }
    if ($exception instanceof \Junges\Watchdog\Exceptions\InviteMustBeAbleToBeRedeemedException) {
        //
    }
    if ($exception instanceof \Junges\Watchdog\Exceptions\SoldOutException) {
        //
    }
    if ($exception instanceof \Junges\Watchdog\Exceptions\RouteProtectedByInviteCodeException) {
        //
    }
    
    return parent::render($request, $exception);
}
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
