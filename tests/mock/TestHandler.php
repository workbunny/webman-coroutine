<?php

declare(strict_types=1);

namespace Workbunny\Tests\mock;

use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workbunny\WebmanCoroutine\CoroutineServerInterface;

class TestHandler implements HandlerInterface
{
    public static function isAvailable(): bool
    {
        return true;
    }

    public static function onMessage(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        return 'response';
    }

    public static function onWorkerStart(CoroutineWorkerInterface $app, mixed $worker): mixed
    {
        return 'response';
    }

    public static function coroutineCreate(\Closure $function, ?string $waitGroupId = null): mixed
    {
        return $function();
    }

    public static function waitGroupCreate(): string
    {
        return 'waitGroupId';
    }

    public static function waitGroupWait(string $waitGroupId, int $timeout = -1): void
    {
        // Do nothing
    }
}
