<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);
 
namespace Tobento\App\Cache\Boot;

use Tobento\App\Boot;
use Tobento\App\Boot\Functions;
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;
use Tobento\App\FileStorage\Boot\FileStorage;
use Tobento\Service\Cache\CacheItemPoolFactoryInterface;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Cache\CacheItemPools;
use Tobento\Service\Cache\CacheException as ServiceCacheException;
use Tobento\Service\Cache\Simple\CacheFactoryInterface;
use Tobento\Service\Cache\Simple\CachesInterface;
use Tobento\Service\Cache\Simple\Caches;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Cache
 */
class Cache extends Boot
{
    public const INFO = [
        'boot' => [
            'installs and loads cache config file',
            'implements PSR-6 and PSR-16 interfaces based on cache config',
        ],
    ];

    public const BOOT = [
        Functions::class,
        Config::class,
        Migration::class,
        FileStorage::class,
    ];
    
    /**
     * Boot application services.
     *
     * @param Migration $migration
     * @param Config $config
     * @return void
     */
    public function boot(Migration $migration, Config $config): void
    {
        // install cache config:
        $migration->install(\Tobento\App\Cache\Migration\Cache::class);
        
        // PSR-6 pools implementation:
        $this->app->set(CacheItemPoolsInterface::class, function() use ($config): CacheItemPoolsInterface {
            
            // Load the cache configuration without storing it:
            $config = $config->load(file: 'cache.php');
            
            // Create and register the pools:
            $pools = new CacheItemPools();
            
            foreach($config['default_pools'] ?? [] as $name => $pool) {
                $pools->addDefault(name: $name, pool: $pool);
            }
            
            foreach($config['pools'] ?? [] as $name => $params)
            {
                $pools->register(name: $name, pool: function() use ($name, $params) {
                    
                    if (is_callable($params)) {
                        return $params($this->app->container());
                    }
                    
                    $factory = $this->app->get($params['factory']);

                    if (! $factory instanceof CacheItemPoolFactoryInterface){ 
                        throw new ServiceCacheException(
                            sprintf(
                                'Pool config factory needs to be an instance of %s!',
                                CacheItemPoolFactoryInterface::class
                            )
                        );
                    }

                    return $factory->createCacheItemPool($name, $params['config']);
                });
            }
            
            return $pools;
        });
        
        // Default PSR-6 CacheItemPoolInterface:
        $this->app->set(CacheItemPoolInterface::class, function(): CacheItemPoolInterface {
            return $this->app->get(CacheItemPoolsInterface::class)->default('primary');
        });
        
        // PSR-16 caches implementation:
        $this->app->set(CachesInterface::class, function() use ($config): CachesInterface {
            
            // Load the cache configuration without storing it:
            $config = $config->load(file: 'cache.php');
            
            // Create and register the pools:
            $caches = new Caches();
            
            foreach($config['default_caches'] ?? [] as $name => $cache) {
                $caches->addDefault(name: $name, cache: $cache);
            }
            
            foreach($config['caches'] ?? [] as $name => $params)
            {
                $caches->register(name: $name, cache: function() use ($name, $params) {

                    if (is_callable($params)) {
                        return $params($this->app->container());
                    }
                    
                    $factory = $this->app->get($params['factory']);

                    if (! $factory instanceof CacheFactoryInterface){ 
                        throw new ServiceCacheException(
                            sprintf(
                                'Cache config factory needs to be an instance of %s!',
                                CacheFactoryInterface::class
                            )
                        );
                    }

                    return $factory->createCache($name, $params['config']);
                });
            }
            
            return $caches;
        });
        
        // Default PSR-16 CacheInterface:
        $this->app->set(CacheInterface::class, function(): CacheInterface {
            return $this->app->get(CachesInterface::class)->default('primary');
        });
    }
}