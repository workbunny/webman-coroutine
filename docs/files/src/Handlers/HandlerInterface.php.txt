<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Closure;
use Webman\Http\Request;
use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workbunny\WebmanCoroutine\Exceptions\RuntimeException;
use Workerman\Connection\ConnectionInterface;

/**
 * @desc 协程处理器接口
 */
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
     * @param Closure $function 协程执行逻辑
     * @param string|null $waitGroupId null:不使用waitGroup
     * @return mixed
     * @throws RuntimeException
     */
    public static function coroutineCreate(Closure $function, null|string $waitGroupId = null): mixed;

    /**
     * 创建一个waitGroup
     *
     * @return string 返回waitGroupId
     */
    public static function waitGroupCreate(): string;

    /**
     * 阻塞等待一个waitGroup完成
     *
     * @param string $waitGroupId
     * @param int $timeout 默认无超时时间
     * @return void
     * @throws RuntimeException
     */
    public static function waitGroupWait(string $waitGroupId, int $timeout = -1): void;
}
