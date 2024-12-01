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

use Psr\Cache\CacheItemPoolInterface;
use PHPUnit\Framework\TestCase;
use Tobento\App\Cache\Console\CachePoolClearCommand;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Cache\CacheItemPools;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Clock\FrozenClock;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\Service\Container\Container;

class CachePoolClearCommandTest extends TestCase
{
    protected function createPool(): CacheItemPoolInterface
    {
        return new ArrayCacheItemPool(
            clock: new FrozenClock(),
        );
    }
    
    public function testClearAllPools()
    {
        $container = new Container();
        $pools = new CacheItemPools();
        $pool = $this->createPool();
        $pool->save($pool->getItem('foo'));
        $pools->add(name: 'foo', pool: $pool);
        $pools->add(name: 'bar', pool: $this->createPool());
        $container->set(CacheItemPoolsInterface::class, $pools);
        
        $this->assertTrue($pool->hasItem('foo'));
        
        (new TestCommand(command: CachePoolClearCommand::class))
            ->expectsOutput('Cache item pool foo cleared')
            ->expectsOutput('Cache item pool bar cleared')
            ->expectsExitCode(0)
            ->execute($container);
        
        $this->assertFalse($pool->hasItem('foo'));
    }
    
    public function testClearSpecificPools()
    {
        $container = new Container();
        $pools = new CacheItemPools();
        $pool = $this->createPool();
        $pool->save($pool->getItem('foo'));
        $barPool = $this->createPool();
        $barPool->save($barPool->getItem('bar'));
        $pools->add(name: 'foo', pool: $pool);
        $pools->add(name: 'bar', pool: $barPool);
        $container->set(CacheItemPoolsInterface::class, $pools);
        
        $this->assertTrue($pool->hasItem('foo'));
        $this->assertTrue($barPool->hasItem('bar'));
        
        (new TestCommand(
            command: CachePoolClearCommand::class,
            input: ['--pool' => ['foo']]
        ))
        ->expectsOutput('Cache item pool foo cleared')
        ->doesntExpectOutput('Cache item pool bar cleared')
        ->expectsExitCode(0)
        ->execute($container);
        
        $this->assertFalse($pool->hasItem('foo'));
        $this->assertTrue($barPool->hasItem('bar'));
    }
    
    public function testPoolNotFound()
    {
        $container = new Container();
        $pools = new CacheItemPools();
        $pools->add(name: 'foo', pool: $this->createPool());
        $container->set(CacheItemPoolsInterface::class, $pools);
        
        (new TestCommand(
            command: CachePoolClearCommand::class,
            input: ['--pool' => ['bar']]
        ))
        ->expectsOutput('Cache item pool bar not found to clear')
        ->expectsExitCode(0)
        ->execute($container);
    }
}