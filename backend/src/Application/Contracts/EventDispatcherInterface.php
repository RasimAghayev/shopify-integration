<?php

declare(strict_types=1);

namespace Src\Application\Contracts;

interface EventDispatcherInterface
{
    /**
     * Dispatch an event
     */
    public function dispatch(object $event): void;

    /**
     * Dispatch multiple events
     *
     * @param  array<object>  $events
     */
    public function dispatchMany(array $events): void;
}
