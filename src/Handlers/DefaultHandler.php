<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;

class DefaultHandler implements HandlerInterface
{
    /** @inheritdoc  */
    public static function available(): bool
    {
        return true;
    }

    /** @inheritdoc  */
    public static function run(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        return $app->parentOnMessage($connection, $request);
    }

    /** @inheritdoc  */
    public static function start(CoroutineWorkerInterface $app, mixed $worker): mixed
    {
        return $app->parentOnWorkerStart($worker);
    }
}
