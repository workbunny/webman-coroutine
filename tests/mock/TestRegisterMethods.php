<?php

declare(strict_types=1);

namespace Workbunny\Tests\mock;

use Workbunny\WebmanCoroutine\Utils\RegisterMethods;

class TestRegisterMethods
{
    use RegisterMethods;

    private static array $_handlers = [];

    public static function registerVerify(mixed $value): mixed
    {
        return is_string($value);
    }

    public static function unregisterExecute(string $key): bool
    {
        return true;
    }
}
