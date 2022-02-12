# Changelog

All notable changes to `mateusjunges/laravel-invite-codes` will be documented in this file.

## 1.5.0 2022-02-12
### Added
- Add support for Laravel v9.x (by @jbrooksuk)

## 1.4.2 2021-10-14
### Fixed
- Refactored redeem method to use happy path ([#37](https://github.com/mateusjunges/laravel-invite-codes/pull/37))
- Removed unused method parameter for private method `inviteCanBeRedeemed` ([#37](https://github.com/mateusjunges/laravel-invite-codes/pull/37))
- Optimezed `canBeUsedXTimes` regex ([#36](https://github.com/mateusjunges/laravel-invite-codes/pull/36))

### Added
- Added `canBeUsedXTimes` tests ([#36](https://github.com/mateusjunges/laravel-invite-codes/pull/36))
- Added missing return type in magic `__call` method ([#36](https://github.com/mateusjunges/laravel-invite-codes/pull/36))

## 1.4.1 2021-10-06
### Fixed 
- Removed void type hint `DuplicateInviteCodeException::forEmail` method ([#33](https://github.com/mateusjunges/laravel-invite-codes/pull/33))

## 1.4.0 2020-01-20
- Add support for PHP v8
- Drop support for Laravel 5.8

## 1.3.0 2020-09-08
- Add support for laravel v8.x
- Drop support for Larvel v5.6 and v5.7

## 1.2.2 2020-11-03
- Change test suite to run on github actions instead of TravisCi

## 1.2.1 2020-03-08
- Ensure all generated codes are unique (#12)

## 1.2.0 2020-03-03
- Add support for Laravel v7.x

## 1.1.0 2020-02-22
- Add path to default invite model where missing (allows to use this package without publishing the config)
- Allows Route model binding by passing the invite code to the route
- Added the `custom_migration` key to the configuration file

## 1.0.1 2020-02-20
- Add path to default invite model (#4)

## 1.0.0 2020-02-15
- Initial release
