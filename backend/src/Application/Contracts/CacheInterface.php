<?php

declare(strict_types=1);

namespace Src\Application\Contracts;

interface CacheInterface
{
    /**
     * Get an item from the cache
     */
    public function get(string $key): mixed;

    /**
     * Store an item in the cache
     *
     * @param  int  $ttl  Time to live in seconds
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void;

    /**
     * Remove an item from the cache
     */
    public function forget(string $key): void;

    /**
     * Get an item from cache, or execute callback and store the result
     */
    public function remember(string $key, int $ttl, callable $callback): mixed;

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool;

    /**
     * Clear all cache
     */
    public function flush(): void;

    /**
     * Store an item in the cache with tags
     *
     * @param  array<string>  $tags
     * @param  int  $ttl  Time to live in seconds
     */
    public function setWithTags(array $tags, string $key, mixed $value, int $ttl = 3600): void;

    /**
     * Get an item from cache with tags, or execute callback and store the result
     *
     * @param  array<string>  $tags
     */
    public function rememberWithTags(array $tags, string $key, int $ttl, callable $callback): mixed;

    /**
     * Flush all cache entries for given tags
     *
     * @param  array<string>  $tags
     */
    public function flushTags(array $tags): void;
}
