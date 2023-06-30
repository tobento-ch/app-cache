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

use Tobento\Service\Cache\CacheItemPoolFactoryInterface;
use Tobento\Service\Cache\FileStorageCacheItemPool;
use Tobento\Service\Cache\CacheException as ServiceCacheException;
use Tobento\Service\FileStorage\StoragesInterface;
use Tobento\Service\FileStorage\StorageException;
use Tobento\Service\Clock\SystemClock;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheException;
use Psr\Clock\ClockInterface;

/**
 * FileStoragesCacheItemPoolFactory
 */
class FileStoragesCacheItemPoolFactory implements CacheItemPoolFactoryInterface
{
    /**
     * Create a new FileStoragesCacheItemPoolFactory.
     *
     * @param StoragesInterface $storages
     * @param null|ClockInterface $clock
     */
    public function __construct(
        protected StoragesInterface $storages,
        protected null|ClockInterface $clock = null,
    ) {}
    
    /**
     * Create a new CacheItemPool based on the configuration.
     *
     * @param string $name
     * @param array $config
     * @return CacheItemPoolInterface
     * @throws CacheException
     */
    public function createCacheItemPool(string $name, array $config = []): CacheItemPoolInterface
    {
        $storage = null;

        if (!empty($config['default_storage'])) {
            try {
                $storage = $this->storages->default($config['default_storage']);
            } catch (StorageException $e) {
                throw new ServiceCacheException($e->getMessage(), $e->getCode(), $e);
            }
        }
        
        if (
            is_null($storage)
            && !empty($config['storage'])
        ) {
            try {
                $storage = $this->storages->get($config['storage']);
            } catch (StorageException $e) {
                throw new ServiceCacheException($e->getMessage(), $e->getCode(), $e);
            }
        }
        
        if (is_null($storage)) {
            throw new ServiceCacheException('No "default_storage" or "storage" defined!');
        }
        
        if (is_null($this->clock)) {
            $this->clock = new SystemClock();
        }
        
        return new FileStorageCacheItemPool(

            // Any storage where to store cache items:
            storage: $storage,

            // A path used as the path prefix for the storage:
            path: $config['path'] ?? 'cache', // string

            // The clock used for calculating expiration:
            clock: $this->clock, // ClockInterface

            // The default Time To Live in seconds, null forever:
            ttl: $config['ttl'] ?? null, // null|int
        );
    }
}