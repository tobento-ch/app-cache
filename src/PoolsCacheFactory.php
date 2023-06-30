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

namespace Tobento\App\Cache;

use Tobento\Service\Cache\Simple\CacheFactoryInterface;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Cache\Simple\Psr6Cache;
use Tobento\Service\Cache\CacheException as ServiceCacheException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\CacheException;

/**
 * PoolsCacheFactory
 */
class PoolsCacheFactory implements CacheFactoryInterface
{
    /**
     * Create a new PoolsCacheFactory.
     *
     * @param CacheItemPoolsInterface $pools
     */
    public function __construct(
        protected CacheItemPoolsInterface $pools,
    ) {}
    
    /**
     * Create a new Cache based on the configuration.
     *
     * @param string $name
     * @param array $config
     * @return CacheInterface
     * @throws CacheException
     */
    public function createCache(string $name, array $config = []): CacheInterface
    {
        $poolName = $config['pool'] ?? '';
        
        if (!$this->pools->has($poolName)) {
            throw new ServiceCacheException(sprintf('Pool "%s" not found!', $poolName));
        }
        
        return new Psr6Cache(

            pool: $this->pools->get($poolName), // \Psr\Cache\CacheItemPoolInterface

            // A namespace used as prefix for cache item keys:
            namespace: $config['namespace'] ?? 'default', // string

            // The default Time To Live in seconds, null forever:
            ttl: $config['ttl'] ?? null, // null|int
        );
    }
}