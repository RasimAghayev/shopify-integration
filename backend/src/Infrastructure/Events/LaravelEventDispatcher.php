<?php

declare(strict_types=1);

namespace Src\Infrastructure\Events;

use Illuminate\Support\Facades\Event;
use Src\Application\Contracts\EventDispatcherInterface;

final class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): void
    {
        Event::dispatch($event);
    }

    public function dispatchMany(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatch($event);
        }
    }
}
