<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Application\Contracts\CacheInterface;
use Src\Application\Contracts\EventDispatcherInterface;
use Src\Application\Contracts\LoggerInterface;
use Src\Application\Contracts\ShopifyClientInterface;
use Src\Domain\Product\Repositories\ProductRepositoryInterface;
use Src\Infrastructure\Cache\RedisCache;
use Src\Infrastructure\Events\LaravelEventDispatcher;
use Src\Infrastructure\External\Shopify\MockShopifyClient;
use Src\Infrastructure\External\Shopify\ShopifyClient;
use Src\Infrastructure\Logger\LaravelLogger;
use Src\Infrastructure\Persistence\Repositories\EloquentProductRepository;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CacheInterface::class, RedisCache::class);
        $this->app->bind(LoggerInterface::class, LaravelLogger::class);
        $this->app->bind(EventDispatcherInterface::class, LaravelEventDispatcher::class);

        $this->app->bind(ShopifyClientInterface::class, function ($app) {
            // Use mock client for development/testing
            if (config('services.shopify.use_mock', false)) {
                return new MockShopifyClient(
                    logger: $app->make(LoggerInterface::class),
                );
            }

            // Use real Shopify client
            return new ShopifyClient(
                shopDomain: config('services.shopify.store_domain', ''),
                accessToken: config('services.shopify.access_token', ''),
                logger: $app->make(LoggerInterface::class),
                apiVersion: config('services.shopify.api_version', '2026-01'),
                clientId: config('services.shopify.api_key', ''),
                clientSecret: config('services.shopify.api_secret', ''),
                savedCatalog: config('services.shopify.saved_catalog', ''),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
