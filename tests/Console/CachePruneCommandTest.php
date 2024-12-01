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
use Tobento\App\Cache\Console\CachePruneCommand;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Cache\Simple\Caches;
use Tobento\Service\Cache\Simple\CachesInterface;
use Tobento\Service\Cache\Simple\Psr6Cache;
use Tobento\Service\Clock\FrozenClock;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\Service\Container\Container;

class CachePruneCommandTest extends TestCase
{
    protected function createCache(): CacheInterface
    {
        $pool = new ArrayCacheItemPool(clock: new FrozenClock());
        return new Psr6Cache(pool: $pool, namespace: 'ns', ttl: null);
    }
    
    public function testPruneAllCaches()
    {
        $container = new Container();
        $caches = new Caches();
        $caches->add(name: 'foo', cache: $this->createCache());
        $caches->add(name: 'bar', cache: $this->createCache());
        $container->set(CachesInterface::class, $caches);
        
        (new TestCommand(command: CachePruneCommand::class))
            ->expectsOutput('Cache foo pruned')
            ->expectsOutput('Cache bar pruned')
            ->expectsExitCode(0)
            ->execute($container);
    }
    
    public function testPruneSpecificCaches()
    {
        $container = new Container();
        $caches = new Caches();
        $caches->add(name: 'foo', cache: $this->createCache());
        $caches->add(name: 'bar', cache: $this->createCache());
        $container->set(CachesInterface::class, $caches);
        
        (new TestCommand(
            command: CachePruneCommand::class,
            input: ['--cache' => ['foo']]
        ))
        ->expectsOutput('Cache foo pruned')
        ->doesntExpectOutput('Cache bar pruned')
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testCacheNotFound()
    {
        $container = new Container();
        $caches = new Caches();
        $caches->add(name: 'foo', cache: $this->createCache());
        $container->set(CachesInterface::class, $caches);
        
        (new TestCommand(
            command: CachePruneCommand::class,
            input: ['--cache' => ['bar']]
        ))
        ->expectsOutput('Cache bar not found to prune')
        ->expectsExitCode(0)
        ->execute($container);
    }
}