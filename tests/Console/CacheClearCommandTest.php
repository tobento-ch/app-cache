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

namespace Tobento\App\Cache\Test\Console;

use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;
use Tobento\App\Cache\Console\CacheClearCommand;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Cache\Simple\Caches;
use Tobento\Service\Cache\Simple\CachesInterface;
use Tobento\Service\Cache\Simple\Psr6Cache;
use Tobento\Service\Clock\FrozenClock;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\Service\Container\Container;

class CacheClearCommandTest extends TestCase
{
    protected function createCache(): CacheInterface
    {
        $pool = new ArrayCacheItemPool(clock: new FrozenClock());
        return new Psr6Cache(pool: $pool, namespace: 'ns', ttl: null);
    }
    
    public function testClearAllCaches()
    {
        $container = new Container();
        $caches = new Caches();
        $cache = $this->createCache();
        $cache->set('key', 'value');
        $caches->add(name: 'foo', cache: $cache);
        $caches->add(name: 'bar', cache: $this->createCache());
        $container->set(CachesInterface::class, $caches);
        
        $this->assertTrue($cache->has('key'));
        
        (new TestCommand(command: CacheClearCommand::class))
            ->expectsOutput('Cache foo cleared')
            ->expectsOutput('Cache bar cleared')
            ->expectsExitCode(0)
            ->execute($container);
        
        $this->assertFalse($cache->has('key'));
    }
    
    public function testClearSpecificCaches()
    {
        $container = new Container();
        $caches = new Caches();
        $cache = $this->createCache();
        $cache->set('key', 'value');
        $caches->add(name: 'foo', cache: $cache);
        $barCache = $this->createCache();
        $barCache->set('bar:key', 'value');
        $caches->add(name: 'bar', cache: $barCache);
        $container->set(CachesInterface::class, $caches);
        
        $this->assertTrue($cache->has('key'));
        $this->assertTrue($barCache->has('bar:key'));
        
        (new TestCommand(
            command: CacheClearCommand::class,
            input: ['--cache' => ['foo']]
        ))
        ->expectsOutput('Cache foo cleared')
        ->doesntExpectOutput('Cache bar cleared')
        ->expectsExitCode(0)
        ->execute($container);
        
        $this->assertFalse($cache->has('key'));
        $this->assertTrue($barCache->has('bar:key'));
    }
    
    public function testCacheNotFound()
    {
        $container = new Container();
        $caches = new Caches();
        $caches->add(name: 'foo', cache: $this->createCache());
        $container->set(CachesInterface::class, $caches);
        
        (new TestCommand(
            command: CacheClearCommand::class,
            input: ['--cache' => ['bar']]
        ))
        ->expectsOutput('Cache bar not found to clear')
        ->expectsExitCode(0)
        ->execute($container);
    }
}