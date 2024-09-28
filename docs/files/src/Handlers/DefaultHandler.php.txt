<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Closure;
use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;

/**
 *  默认处理器，使用workerman基础事件
 */
class DefaultHandler implements HandlerInterface
{
    /**
     * default handler永远返回true
     *
     * @inheritdoc
     */
    public static function isAvailable(): bool
    {
        return true;
    }

    /**
     * default handler不会创建协程
     *
     * @inheritdoc
     */
    public static function onMessage(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        return $app->parentOnMessage($connection, $request);
    }

    /**
     * default handler不会创建协程
     *
     * @inheritdoc
     */
    public static function onWorkerStart(CoroutineWorkerInterface $app, mixed $worker): mixed
    {
        return $app->parentOnWorkerStart($worker);
    }

    /**
     * default handler不会创建协程
     *
     * @inheritdoc
     */
    public static function coroutineCreate(Closure $function, ?string $waitGroupId = null): mixed
    {
        return call_user_func($function);
    }

    /**
     * default handler不会创建waitGroup
     *
     * @inheritdoc
     */
    public static function waitGroupCreate(): string
    {
        return '';
    }

    /**
     * default handler不会等待waitGroup
     *
     * @param string $waitGroupId
     * @param int $timeout
     * @inheritdoc
     */
    public static function waitGroupWait(string $waitGroupId, int $timeout = -1): void
    {
    }
}
