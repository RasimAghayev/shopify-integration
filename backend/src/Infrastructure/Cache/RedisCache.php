<?php

declare(strict_types=1);

namespace Src\Infrastructure\Cache;

use Illuminate\Support\Facades\Cache;
use Src\Application\Contracts\CacheInterface;

final readonly class RedisCache implements CacheInterface
{
    public function __construct(
        private string $prefix = 'shopify:',
    ) {}

    public function get(string $key): mixed
    {
        return Cache::get($this->prefixKey($key));
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        Cache::put($this->prefixKey($key), $value, $ttl);
    }

    public function forget(string $key): void
    {
        Cache::forget($this->prefixKey($key));
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($this->prefixKey($key), $ttl, $callback);
    }

    public function has(string $key): bool
    {
        return Cache::has($this->prefixKey($key));
    }

    public function flush(): void
    {
        Cache::flush();
    }

    public function setWithTags(array $tags, string $key, mixed $value, int $ttl = 3600): void
    {
        Cache::tags($tags)->put($this->prefixKey($key), $value, $ttl);
    }

    public function rememberWithTags(array $tags, string $key, int $ttl, callable $callback): mixed
    {
        return Cache::tags($tags)->remember($this->prefixKey($key), $ttl, $callback);
    }

    public function flushTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    private function prefixKey(string $key): string
    {
        return $this->prefix.$key;
    }
}
