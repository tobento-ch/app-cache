<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

use Tobento\App\AppInterface;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Clock\SystemClock;
use Tobento\Service\Cache\Simple\Psr6Cache;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Container\ContainerInterface;
use function Tobento\App\{directory};

return [

    /*
    |--------------------------------------------------------------------------
    | PSR 6 - Default Pool Names
    |--------------------------------------------------------------------------
    |
    | Specify the default pool names you wish to use for your application.
    |
    | The default "primary" is used by the application for the default
    | \Psr\Cache\CacheItemPoolInterface implementation
    | used for autowiring classes and may be used in other app bundles.
    | If you do not need it at all, just ignore or remove it.
    |
    */

    'default_pools' => [
        'primary' => 'file',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | PSR 6 - Cache Item Pools
    |--------------------------------------------------------------------------
    |
    | Configure any pools needed for your application.
    |
    */
    
    'pools' => [

        'file' => [
            'factory' => \Tobento\App\Cache\FileStoragesCacheItemPoolFactory::class,
            'config' => [
                // The default name of the file storage:
                'default_storage' => 'cache',
                
                // Or you might use a specific file storage by its name:
                //'storage' => 'cache',
                
                // A path used as the path prefix for the storage:
                'path' => 'default',
                
                // The default Time To Live in seconds (int), null forever:
                'ttl' => null,
            ],
        ],
        
        // example using closure:
        'file-closure' => function(ContainerInterface $c): CacheItemPoolInterface {
            
            $app = $c->get(AppInterface::class); // just for test
            
            return new ArrayCacheItemPool(
                // The clock used for calculating expiration:
                clock: new SystemClock(), // ClockInterface

                // The default Time To Live in seconds, null forever:
                ttl: null, // null|int
            );
        },
        
    ],
    
    /*
    |--------------------------------------------------------------------------
    | PSR 16 - Default Cache Names
    |--------------------------------------------------------------------------
    |
    | Specify the default cache names you wish to use for your application.
    |
    | The default "primary" is used by the application for the default
    | \Psr\SimpleCache\CacheInterface implementation
    | used for autowiring classes and may be used in other app bundles.
    | If you do not need it at all, just ignore or remove it.
    |
    */

    'default_caches' => [
        'primary' => 'file',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | PSR 16 - Caches
    |--------------------------------------------------------------------------
    |
    | Configure any caches needed for your application.
    |
    */
    
    'caches' => [
        
        'file' => [
            'factory' => \Tobento\App\Cache\PoolsCacheFactory::class,
            'config' => [
                // The name of the pool:
                'pool' => 'file',
                
                // A namespace used as prefix for cache item keys:
                'namespace' => 'default',
                
                // The default Time To Live in seconds (int), null forever:
                'ttl' => null,
            ],
        ],
        
        // example using closure:
        'file-closure' => function(ContainerInterface $c): CacheInterface {
            
            $app = $c->get(AppInterface::class); // just for test
            
            $pool = new ArrayCacheItemPool(
                // The clock used for calculating expiration:
                clock: new SystemClock(), // ClockInterface

                // The default Time To Live in seconds, null forever:
                ttl: null, // null|int
            );
            
            return new Psr6Cache(
                pool: $pool, // CacheItemPoolInterface

                // A namespace used as prefix for cache item keys:
                namespace: 'default', // string

                // The default Time To Live in seconds, null forever:
                ttl: null, // null|int
            );
        },
        
    ],    

];