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
use Tobento\App\Cache\FileStoragesCacheItemPoolFactory;
use Tobento\Service\Cache\CacheItemPoolFactoryInterface;
use Tobento\Service\FileStorage\Storages;
use Tobento\Service\FileStorage\Flysystem;
use Tobento\Service\FileStorage\StoragesInterface;
use Tobento\Service\Clock\SystemClock;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheException;
    
/**
 * FileStoragesCacheItemPoolFactoryTest
 */
class FileStoragesCacheItemPoolFactoryTest extends TestCase
{
    protected function createStorages(): StoragesInterface
    {
        $filesystem = new \League\Flysystem\Filesystem(
            adapter: new \League\Flysystem\Local\LocalFilesystemAdapter(
                location: __DIR__.'/../tmp/'
            )
        );

        $storage = new Flysystem\Storage(
            name: 'local',
            flysystem: $filesystem,
            fileFactory: new Flysystem\FileFactory(
                flysystem: $filesystem,
                streamFactory: new Psr17Factory()
            ),
        );
        
        $storages = new Storages();
        $storages->addDefault('cache', 'local');
        $storages->add($storage);
        return $storages;
    }
    
    public function testConstructMethod()
    {
        $factory = new FileStoragesCacheItemPoolFactory(
            storages: $this->createStorages(),
            clock: new SystemClock(),
        );
        
        $this->assertInstanceof(CacheItemPoolFactoryInterface::class, $factory);
    }
    
    public function testCreateCacheItemPoolMethodWithDefaultStorage()
    {
        $factory = new FileStoragesCacheItemPoolFactory(
            storages: $this->createStorages(),
            clock: new SystemClock(),
        );
        
        $this->assertInstanceof(
            CacheItemPoolInterface::class,
            $factory->createCacheItemPool(
                name: 'name',
                config: [
                    'default_storage' => 'cache',
                ]
            )
        );
    }
    
    public function testCreateCacheItemPoolMethodWithDefaultStorageThrowsCacheExceptionIfNotExists()
    {
        $this->expectException(CacheException::class);
        
        $factory = new FileStoragesCacheItemPoolFactory(
            storages: $this->createStorages(),
            clock: new SystemClock(),
        );
        
        $this->assertInstanceof(
            CacheItemPoolInterface::class,
            $factory->createCacheItemPool(
                name: 'name',
                config: [
                    'default_storage' => 'inexistence',
                ]
            )
        );
    }
    
    public function testCreateCacheItemPoolMethodWithStorage()
    {
        $factory = new FileStoragesCacheItemPoolFactory(
            storages: $this->createStorages(),
            clock: new SystemClock(),
        );
        
        $this->assertInstanceof(
            CacheItemPoolInterface::class,
            $factory->createCacheItemPool(
                name: 'name',
                config: [
                    'storage' => 'local',
                ]
            )
        );
    }
    
    public function testCreateCacheItemPoolMethodWithStorageThrowsCacheExceptionIfNotExists()
    {
        $this->expectException(CacheException::class);
        
        $factory = new FileStoragesCacheItemPoolFactory(
            storages: $this->createStorages(),
            clock: new SystemClock(),
        );
        
        $this->assertInstanceof(
            CacheItemPoolInterface::class,
            $factory->createCacheItemPool(
                name: 'name',
                config: [
                    'storage' => 'inexistence',
                ]
            )
        );
    }
    
    public function testCreateCacheItemPoolMethodThrowsCacheExceptionIfNoStorageExists()
    {
        $this->expectException(CacheException::class);
        
        $factory = new FileStoragesCacheItemPoolFactory(
            storages: $this->createStorages(),
            clock: new SystemClock(),
        );
        
        $this->assertInstanceof(
            CacheItemPoolInterface::class,
            $factory->createCacheItemPool(
                name: 'name',
                config: []
            )
        );
    }
}