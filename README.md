# App Cache

Cache support for the app.

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Cache Boot](#cache-boot)
        - [Cache Config](#cache-config)
        - [Cache Usage](#cache-usage)
        - [Adding and Registering Caches](#adding-and-registering-caches)
    - [Deleting Expired Items](#deleting-expired-items)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app cache project running this command.

```
composer require tobento/app-cache
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Cache Boot

The cache boot does the following:

* installs and loads cache config file
* implements PSR-6 and PSR-16 interfaces based on cache config

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Cache\Boot\Cache::class);

// Run the app:
$app->run();
```

You may check out the [**Cache Service**](https://github.com/tobento-ch/service-cache) to learn more about it.

### Cache Config

The configuration for the cache is located in the ```app/config/cache.php``` file at the default App Skeleton config location where you can specify the pools and caches for your application.

### Cache Usage

You can access the pools and caches in several ways:

**Using the app**

```php
use Tobento\App\AppFactory;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Cache\Simple\CachesInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Cache\Boot\Cache::class);
$app->booting();

// PSR-6 cache:

// using the default pool:
$pool = $app->get(CacheItemPoolInterface::class);

// using the pools:
$pools = $app->get(CacheItemPoolsInterface::class);


// PSR-16 simple cache:

// using the default pool:
$cache = $app->get(CacheInterface::class);

// using the caches:
$caches = $app->get(CachesInterface::class);

// Run the app:
$app->run();
```

Check out the [**Cache Item Pools Interface**](https://github.com/tobento-ch/service-cache#cache-item-pools-interface) to learn more about it.

Check out the [**Caches Interface**](https://github.com/tobento-ch/service-cache#caches-interface) to learn more about it.

**Using autowiring**

You can also request the interfaces in any class resolved by the app:

```php
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Cache\Simple\CachesInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

class SomeService
{
    public function __construct(
        protected CacheItemPoolsInterface $pools,
        protected CacheItemPoolInterface $pool,
        protected CachesInterface $caches,
        protected CacheInterface $cache,
    ) {}
}
```

## Adding and Registering Caches

You may add and register more pools and caches by the following way instead of using the cache config file:

```php
use Tobento\App\AppFactory;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Cache\Simple\CachesInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// PSR-6:
$app->on(CacheItemPoolsInterface::class, function(CacheItemPoolsInterface $pools) {
    // using the add method:
    $pools->add(name: 'name', pool: $pool);
    
    // using the register method:
    $pools->register(
        name: 'name',
        pool: function(string $name): CacheItemPoolInterface {
            // create the pool:
            return $pool;
        },
    );
});

// PSR-16:
$app->on(CachesInterface::class, function(CachesInterface $caches) {
    // using the add method:
    $caches->add(name: 'name', cache: $cache);
    
    // using the register method:
    $caches->register(
        name: 'name',
        cache: function(string $name): CacheInterface {
            // create the cache:
            return $cache;
        },
    );
});

// Adding boots:
$app->boot(\Tobento\App\Cache\Boot\Cache::class);

// Run the app:
$app->run();
```

## Deleting Expired Items

You may delete expired items automatically with the task manager (in development).

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)