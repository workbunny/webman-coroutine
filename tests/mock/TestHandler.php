<?php

declare(strict_types=1);

namespace Workbunny\Tests\mock;

use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;

class TestHandler implements HandlerInterface
{
    public static function isAvailable(): bool
    {
        return true;
    }

    public static function initEnv(): void
    {
        echo 'initEnv';
    }

    public static function waitFor(?\Closure $closure = null, float|int $timeout = -1): void
    {
        if ($closure) {
            call_user_func($closure);
        }
    }
}
