<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workbunny\WebmanCoroutine\CoroutineWebServer;

class DefaultHandler implements HandlerInterface
{
    /** @inheritdoc  */
    public static function run(CoroutineWebServer $app, mixed $connection, mixed $request): mixed
    {
        return $app->parentOnMessage($connection, $request);
    }

    /** @inheritdoc  */
    public static function available(): bool
    {
        return true;
    }
}
