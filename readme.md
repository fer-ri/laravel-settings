# Laravel Settings

[![Build Status](https://travis-ci.org/ghprod/laravel-settings.svg?branch=master)](https://travis-ci.org/ghprod/laravel-settings)
[![Total Downloads](https://poser.pugx.org/ferri/laravel-settings/d/total.svg)](https://packagist.org/packages/ferri/laravel-settings)
[![License](https://poser.pugx.org/ferri/laravel-settings/license.svg)](https://packagist.org/packages/ferri/laravel-settings)

Laravel 5 persistent settings in database package

Support Laravel 5.1, 5.2, 5.3

## Installation

```
composer require ferri/laravel-settings
```

And add service provider to you `config/app.php`

```
...

Ferri\LaravelSettings\ServiceProvider::class,

...
```

Publish config and migration

```
php artisan vendor:publish --provider="Ferri\LaravelSettings\ServiceProvider" --tag=config
php artisan vendor:publish --provider="Ferri\LaravelSettings\ServiceProvider" --tag=migrations
```

## Cache

You can disable cache when get setting value. Default is `true`. Cache is inherit from active driver in `config/cache.php`

```php
'cache' => false,
```

## Usage

### Set value
Set setting value
```php
Settings::set('key', 'value');
Settings::set('keyArray', ['arrayKey' => 'arrayValue']);
```

### Get value
Get setting value
```php
Settings::get('key'); // value
Settings::get('keyArray'); // ['arrayKey' => 'arrayValue']
Settings::get('keyNotExists', 'default'); // default
```

### Check value
Determine if setting exists.
```php
Settings::has('key'); // true
Settings::has('keyNotExists'); // false
```

### Forget value
Remove setting from database and cache if enabled
```php
Settings::forget('key');
```

### Flush settings
Remove all setting from database (truncate) and cache if enabled
```php
Settings::truncate();
```

### Set Extra Columns
Sometime you want to specify some criteria for each your setting. This columns will be added to each query.
Extra columns always resetted after call of one these methods `set`, `get`, `has`, `forget`.
```php
Settings::setExtraColumns(['tenant_id' => 1])->set('site_title', 'Awesome Blog');
Settings::setExtraColumns(['tenant_id' => 1])->get('site_title'); // Awesome Blog
Settings::setExtraColumns(['tenant_id' => 2])->get('site_title'); // null
```

## Helpers

### Settings Instance
Resolve settings service instance.
```php
settings();
```

### Set value
Set setting value
```php
settings([$key => $value]);
```

Set setting value with extra columns
```php
settings([$key => $value], null, ['tenant_id' => 1])
```

### Get value
Get setting value
```php
settings('key'); // value
```

Get setting value with extra columns
```php
settings($key, $default, ['tenant_id' => 1])
```

## Testing

```

composer install
vendor/bin/phpunit

```

## Inspiration
This package was inspired by these great packages
- https://github.com/anlutro/laravel-settings
- https://github.com/edvinaskrucas/settings
- https://github.com/efriandika/laravel-settings