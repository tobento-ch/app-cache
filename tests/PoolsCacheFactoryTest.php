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

namespace Tobento\App\Cache\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\Cache\PoolsCacheFactory;
use Tobento\Service\Cache\Simple\CacheFactoryInterface;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Cache\CacheItemPools;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Clock\SystemClock;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\CacheException;

/**
 * PoolsCacheFactoryTest
 */
class PoolsCacheFactoryTest extends TestCase
{
    protected function createPools(): CacheItemPoolsInterface
    {
        $pool = new ArrayCacheItemPool(
            clock: new SystemClock(),
        );
        
        $pools = new CacheItemPools();
        $pools->addDefault('cache', 'arr');
        $pools->add(name: 'arr', pool: $pool);
        return $pools;
    }
    
    public function testConstructMethod()
    {
        $factory = new PoolsCacheFactory(
            pools: $this->createPools(),
        );
        
        $this->assertInstanceof(CacheFactoryInterface::class, $factory);
    }
    
    public function testCreateCacheMethod()
    {
        $factory = new PoolsCacheFactory(
            pools: $this->createPools(),
        );
        
        $this->assertInstanceof(
            CacheInterface::class,
            $factory->createCache(
                name: 'name',
                config: [
                    'pool' => 'arr',
                ]
            )
        );
    }
    
    public function testCreateCacheMethodThrowsCacheExceptionIfPoolDoesNotExist()
    {
        $this->expectException(CacheException::class);
        
        $factory = new PoolsCacheFactory(
            pools: $this->createPools(),
        );
        
        $this->assertInstanceof(
            CacheInterface::class,
            $factory->createCache(
                name: 'name',
                config: [
                    'pool' => 'inexistence',
                ]
            )
        );
    }
}