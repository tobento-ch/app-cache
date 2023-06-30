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

namespace Tobento\App\Cache\Test\Boot;

use PHPUnit\Framework\TestCase;
use Tobento\App\Cache\Boot\Cache;
use Tobento\Service\Cache\CacheItemPoolsInterface;
use Tobento\Service\Cache\Simple\CachesInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Tobento\Service\Config\ConfigInterface;
use Tobento\App\AppInterface;
use Tobento\App\AppFactory;
use Tobento\App\Boot\Config;
use Tobento\Service\Filesystem\Dir;
    
/**
 * CacheTest
 */
class CacheTest extends TestCase
{
    protected function createApp(bool $deleteDir = true): AppInterface
    {
        if ($deleteDir) {
            (new Dir())->delete(__DIR__.'/../app/');
        }
        
        (new Dir())->create(__DIR__.'/../app/');
        
        $app = (new AppFactory())->createApp();
        
        $app->dirs()
            ->dir(realpath(__DIR__.'/../../'), 'root')
            ->dir(realpath(__DIR__.'/../app/'), 'app')
            ->dir($app->dir('app').'config', 'config', group: 'config', priority: 10)
            ->dir($app->dir('root').'vendor', 'vendor')
            // for testing only we add public within app dir.
            ->dir($app->dir('app').'public', 'public');
        
        return $app;
    }
    
    public static function tearDownAfterClass(): void
    {
        (new Dir())->delete(__DIR__.'/../app/');
    }
    
    public function testInterfacesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Cache::class);
        $app->booting();
        
        $this->assertInstanceof(CacheItemPoolsInterface::class, $app->get(CacheItemPoolsInterface::class));
        $this->assertInstanceof(CachesInterface::class, $app->get(CachesInterface::class));
        $this->assertInstanceof(CacheItemPoolInterface::class, $app->get(CacheItemPoolInterface::class));
        $this->assertInstanceof(CacheInterface::class, $app->get(CacheInterface::class));
    }
    
    public function testDefaultPoolsAndCachesAreAvailable()
    {
        $app = $this->createApp();
        $app->boot(Cache::class);
        $app->booting();
        
        $pools = $app->get(CacheItemPoolsInterface::class);
        $caches = $app->get(CachesInterface::class);
        
        $this->assertInstanceof(CacheItemPoolInterface::class, $pools->default('primary'));
        $this->assertInstanceof(CacheInterface::class, $caches->default('primary'));
    }
    
    public function testWithClosureConfigStorage()
    {
        $app = $this->createApp();
        
        $app->dirs()->dir(realpath(__DIR__.'/../config/'), 'config-test', group: 'config', priority: 20);
        
        $app->boot(Cache::class);
        $app->booting();
        
        $pools = $app->get(CacheItemPoolsInterface::class);
        $caches = $app->get(CachesInterface::class);
        
        $this->assertInstanceof(CacheItemPoolInterface::class, $pools->get('file-closure'));
        $this->assertInstanceof(CacheInterface::class, $caches->get('file-closure'));
    }
}