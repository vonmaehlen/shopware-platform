<?php

namespace Shopware\Tests\Unit\Framework\Cache;

use Shopware\Core\Framework\Adapter\Cache\AbstractCacheTracer;
use Shopware\Storefront\Framework\Cache\CacheStateValidator;
use Shopware\Storefront\Framework\Cache\CacheStore;
use PHPUnit\Framework\TestCase;
use Shopware\Storefront\Framework\Cache\HttpCacheKeyGenerator;
use Shopware\Storefront\Framework\Routing\MaintenanceModeResolver;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @internal
 *
 * @covers \Shopware\Storefront\Framework\Cache\CacheStore
 */
class CacheStoreTest extends TestCase
{
    public function testGetLock(): void
    {
        $request = new Request();

        $cache = $this->createMock(TagAwareAdapterInterface::class);

        $cache->expects($this->once())->method('hasItem')->willReturn(false);

        $item = $this->createMock(ItemInterface::class);

        $cache->expects($this->once())->method('getItem')->willReturn($item);

        $item->expects($this->once())->method('set')->with(true);

        // expect that we set an expires date for the lock key to prevent endless locks
        $item->expects($this->once())->method('expiresAfter')->with(3);

        $cache->expects($this->once())->method('save')->with($item);

        $store = new CacheStore(
            $cache,
            $this->createMock(CacheStateValidator::class),
            new EventDispatcher(),
            $this->createMock(AbstractCacheTracer::class),
            new HttpCacheKeyGenerator('test', new EventDispatcher(), []),
            $this->createMock(MaintenanceModeResolver::class),
            []
        );

        $store->lock($request);
    }
}