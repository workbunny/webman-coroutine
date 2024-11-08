<?php

declare(strict_types=1);

namespace Workbunny\Tests\mock;

use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;
use Workbunny\WebmanCoroutine\Handlers\HandlerMethods;

class TestHandler implements HandlerInterface
{
    use HandlerMethods;

    public static function isAvailable(): bool
    {
        return true;
    }

    public static function initEnv(): void
    {
        echo 'initEnv';
    }

    public static function waitFor(?\Closure $action = null, float|int $timeout = -1, ?string $event = null): void
    {
        if ($action) {
            call_user_func($action);
        }
    }

    public static function wakeup(string $event): void
    {
    }

    public static function sleep(float|int $timeout = 0, ?string $event = null): void
    {
    }

    public static function kill(object|int|string $suspensionOrSuspensionId, string $message = 'kill', int $exitCode = 0): void
    {
        // TODO: Implement kill() method.
    }
}
