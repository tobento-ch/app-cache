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
use Tobento\App\Cache\Console\CachePoolPruneCommand;
use Tobento\Service\Cache\ArrayCacheItemPool;
use Tobento\Service\Cache\CacheItemPools;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Clock\FrozenClock;
use Tobento\Service\Console\Test\TestCommand;
use Tobento\Service\Container\Container;

class CachePoolPruneCommandTest extends TestCase
{
    protected function createPool(): CacheItemPoolInterface
    {
        return new ArrayCacheItemPool(
            clock: new FrozenClock(),
        );
    }
    
    public function testPruneAllPools()
    {
        $container = new Container();
        $pools = new CacheItemPools();
        $pools->add(name: 'foo', pool: $this->createPool());
        $pools->add(name: 'bar', pool: $this->createPool());
        $container->set(CacheItemPoolsInterface::class, $pools);
        
        (new TestCommand(command: CachePoolPruneCommand::class))
            ->expectsOutput('Cache item pool foo does not support pruning')
            ->expectsOutput('Cache item pool bar does not support pruning')
            ->expectsExitCode(0)
            ->execute($container);
    }
    
    public function testPruneSpecificPools()
    {
        $container = new Container();
        $pools = new CacheItemPools();
        $pools->add(name: 'foo', pool: $this->createPool());
        $pools->add(name: 'bar', pool: $this->createPool());
        $container->set(CacheItemPoolsInterface::class, $pools);
        
        (new TestCommand(
            command: CachePoolPruneCommand::class,
            input: ['--pool' => ['foo']]
        ))
        ->expectsOutput('Cache item pool foo does not support pruning')
        ->doesntExpectOutput('Cache item pool bar does not support pruning')
        ->expectsExitCode(0)
        ->execute($container);
    }
    
    public function testPoolNotFound()
    {
        $container = new Container();
        $pools = new CacheItemPools();
        $pools->add(name: 'foo', pool: $this->createPool());
        $container->set(CacheItemPoolsInterface::class, $pools);
        
        (new TestCommand(
            command: CachePoolPruneCommand::class,
            input: ['--pool' => ['bar']]
        ))
        ->expectsOutput('Cache item pool bar not found to prune')
        ->expectsExitCode(0)
        ->execute($container);
    }
}