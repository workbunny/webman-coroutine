<?php

declare(strict_types=1);

namespace Workbunny\Tests\mock;

use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;

class TestByFailHandler implements HandlerInterface
{
    public static function isAvailable(): bool
    {
        return false;
    }

    public static function initEnv(): void
    {
        echo 'initEnv';
    }
}
