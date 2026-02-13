<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases;

use PHPUnit\Framework\MockObject\MockObject;
use Src\Application\Contracts\CacheInterface;
use Src\Application\Contracts\EventDispatcherInterface;
use Src\Application\Contracts\LoggerInterface;
use Src\Application\Contracts\ShopifyClientInterface;
use Src\Application\Services\CacheKeyGenerator;
use Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductDTO;
use Src\Application\UseCases\Product\SyncProductFromShopify\SyncProductFromShopifyUseCase;
use Src\Domain\Product\Entities\Product;
use Src\Domain\Product\Events\ProductSynced;
use Src\Domain\Product\Repositories\ProductRepositoryInterface;
use Tests\TestCase;

final class SyncProductFromShopifyUseCaseTest extends TestCase
{
    private MockObject&ShopifyClientInterface $shopifyClient;

    private MockObject&ProductRepositoryInterface $productRepository;

    private MockObject&EventDispatcherInterface $eventDispatcher;

    private MockObject&LoggerInterface $logger;

    private MockObject&CacheInterface $cache;

    private CacheKeyGenerator $cacheKeyGenerator;

    private SyncProductFromShopifyUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopifyClient = $this->createMock(ShopifyClientInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->cacheKeyGenerator = new CacheKeyGenerator();

        // Allow cache flushTags to be called any number of times (void method)
        $this->cache->method('flushTags');

        $this->useCase = new SyncProductFromShopifyUseCase(
            $this->shopifyClient,
            $this->productRepository,
            $this->eventDispatcher,
            $this->logger,
            $this->cache,
            $this->cacheKeyGenerator,
        );
    }

    /** @test */
    public function it_syncs_product_successfully(): void
    {
        $dto = new SyncProductDTO(shopifyId: '123456');

        $shopifyData = [
            'id' => '123456',
            'title' => 'Test Product',
            'body_html' => 'Description',
            'status' => 'active',
            'variants' => [
                [
                    'id' => '111',
                    'sku' => 'TEST-001',
                    'price' => '19.99',
                    'inventory_quantity' => 50,
                ],
            ],
        ];

        $this->shopifyClient
            ->expects($this->once())
            ->method('getProduct')
            ->with('123456')
            ->willReturn($shopifyData);

        $this->productRepository
            ->expects($this->once())
            ->method('findByShopifyId')
            ->with('123456')
            ->willReturn(null);

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn (Product $p) => $p);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ProductSynced::class));

        $this->logger
            ->expects($this->exactly(2))
            ->method('info');

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('TEST-001', $result->sku->value);
        $this->assertEquals('Test Product', $result->title);
    }

    /** @test */
    public function it_updates_existing_product(): void
    {
        $dto = new SyncProductDTO(shopifyId: '123456');

        $shopifyData = [
            'id' => '123456',
            'title' => 'Updated Product',
            'status' => 'active',
            'variants' => [
                [
                    'sku' => 'TEST-001',
                    'price' => '29.99',
                    'inventory_quantity' => 100,
                ],
            ],
        ];

        $existingProduct = Product::create(
            sku: new \Src\Domain\Product\ValueObjects\Sku('TEST-001'),
            title: 'Old Title',
            price: new \Src\Domain\Product\ValueObjects\Price(1999, \Src\Domain\Product\ValueObjects\Currency::USD),
            shopifyId: '123456',
        );

        $this->shopifyClient
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($shopifyData);

        $this->productRepository
            ->expects($this->once())
            ->method('findByShopifyId')
            ->willReturn($existingProduct);

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn (Product $p) => $p);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch');

        $result = $this->useCase->execute($dto);

        $this->assertEquals('Updated Product', $result->title);
        $this->assertEquals(2999, $result->price->amount);
    }

    /** @test */
    public function it_handles_shopify_api_error(): void
    {
        $dto = new SyncProductDTO(shopifyId: '123456');

        $this->shopifyClient
            ->expects($this->once())
            ->method('getProduct')
            ->willThrowException(new \RuntimeException('Shopify API Error'));

        $this->logger
            ->expects($this->once())
            ->method('error');

        $this->expectException(\Src\Application\UseCases\Product\SyncProductFromShopify\ProductSyncFailedException::class);

        $this->useCase->execute($dto);
    }

    /** @test */
    public function it_logs_sync_start_and_completion(): void
    {
        $dto = new SyncProductDTO(shopifyId: '123456');

        $shopifyData = [
            'id' => '123456',
            'title' => 'Test Product',
            'variants' => [
                ['sku' => 'TEST-001', 'price' => '19.99'],
            ],
        ];

        $this->shopifyClient
            ->method('getProduct')
            ->willReturn($shopifyData);

        $this->productRepository
            ->method('findByShopifyId')
            ->willReturn(null);

        $this->productRepository
            ->method('save')
            ->willReturnCallback(fn (Product $p) => $p);

        $this->logger
            ->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function (string $message, array $context): void {
                static $callCount = 0;
                $callCount++;

                if ($callCount === 1) {
                    $this->assertStringContainsString('Starting', $message);
                } else {
                    $this->assertStringContainsString('successfully', $message);
                }
            });

        $this->useCase->execute($dto);
    }

    /** @test */
    public function it_skips_update_when_force_is_false_and_product_exists(): void
    {
        $dto = new SyncProductDTO(shopifyId: '123456', forceUpdate: false);

        $existingProduct = Product::create(
            sku: new \Src\Domain\Product\ValueObjects\Sku('TEST-001'),
            title: 'Existing Product',
            price: new \Src\Domain\Product\ValueObjects\Price(1999, \Src\Domain\Product\ValueObjects\Currency::USD),
            shopifyId: '123456',
        );

        $this->productRepository
            ->expects($this->once())
            ->method('findByShopifyId')
            ->willReturn($existingProduct);

        $this->shopifyClient
            ->expects($this->never())
            ->method('getProduct');

        $result = $this->useCase->execute($dto);

        $this->assertEquals($existingProduct, $result);
    }
}
