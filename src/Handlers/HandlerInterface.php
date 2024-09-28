<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Closure;
use InvalidArgumentException;
use Webman\Http\Request;
use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workbunny\WebmanCoroutine\Exceptions\RuntimeException;
use Workerman\Connection\ConnectionInterface;

interface HandlerInterface
{
    /**
     * 用于判断当前环境是否可用
     *
     * @return bool 返回是否可用
     */
    public static function isAvailable(): bool;

    /**
     * 执行协程处理
     *
     * @param CoroutineServerInterface $app
     * @param mixed|ConnectionInterface $connection
     * @param mixed|Request $request
     * @return mixed
     * @throws RuntimeException
     */
    public static function onMessage(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed;

    /**
     * 执行协程处理
     *
     * @param CoroutineWorkerInterface $app
     * @param mixed $worker
     * @return mixed
     * @throws RuntimeException
     */
    public static function onWorkerStart(CoroutineWorkerInterface $app, mixed $worker): mixed;

    /**
     * 创建一个协程
     *
     * @param Closure $function
     * @param string|null $waitGroupId
     * @return mixed 返回await的期待值
     * @throws RuntimeException
     */
    public static function coroutineCreate(Closure $function, null|string $waitGroupId = null): mixed;

    /**
     * 创建一个waitGroup
     *
     * @return string
     */
    public static function waitGroupCreate(): string;

    /**
     * 等待一个waitGroup
     *
     * @param string $waitGroupId
     * @param int $timeout
     * @return void
     * @throws RuntimeException
     */
    public static function waitGroupWait(string $waitGroupId, int $timeout = -1): void;
}
