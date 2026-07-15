# Laravel Common

Shared Laravel common utilities for Curicows applications.

## Installation

Require the package from a Laravel application using the repository path or package registry configured for Duraludon:

```bash
composer require curicows/laravel-common
```

Laravel discovers the package service provider automatically.

Publish the package configuration when the application needs to override the defaults:

```bash
php artisan vendor:publish --tag=laravel-common-config
```

Publish the optional base migrations when an application wants to start from the common user schema:

```bash
php artisan vendor:publish --tag=laravel-common-migrations
```

## Development

```bash
composer install
composer test
composer lint
```
