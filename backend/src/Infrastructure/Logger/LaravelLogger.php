<?php

declare(strict_types=1);

namespace Src\Infrastructure\Logger;

use Illuminate\Support\Facades\Log;
use Src\Application\Contracts\LoggerInterface;

final class LaravelLogger implements LoggerInterface
{
    public function info(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        Log::debug($message, $context);
    }
}
