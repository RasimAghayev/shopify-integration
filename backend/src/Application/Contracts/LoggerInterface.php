<?php

declare(strict_types=1);

namespace Src\Application\Contracts;

interface LoggerInterface
{
    /**
     * Log info message
     *
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, array $context = []): void;

    /**
     * Log warning message
     *
     * @param  array<string, mixed>  $context
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Log error message
     *
     * @param  array<string, mixed>  $context
     */
    public function error(string $message, array $context = []): void;

    /**
     * Log debug message
     *
     * @param  array<string, mixed>  $context
     */
    public function debug(string $message, array $context = []): void;
}
