<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Throwable;
use WeakMap;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

/**
 *  协程处理器接口
 */
interface HandlerInterface
{
    /**
     * 协程环境是否可用
     *
     * @return bool
     */
    public static function isAvailable(): bool;

    /**
     * 协程环境初始化加载
     *
     * @return void
     */
    public static function initEnv(): void;

    /**
     * 协程等待
     *
     * @param \Closure|null $action true|Throwable 都会跳出等待
     * @param int|float $timeout 单位：秒，< 0不限制等待时间
     * @param string|null $event 唤醒事件名
     * @return void
     * @throws TimeoutException 超时抛出
     */
    public static function waitFor(?\Closure $action = null, int|float $timeout = -1, ?string $event = null): void;

    /**
     * 协程唤醒
     *
     * @param string $event 唤醒事件名
     * @return void
     */
    public static function wakeup(string $event): void;

    /**
     * 协程睡眠
     *  - $event !== null && $timeout < 0 时，将阻塞当前协程，直到被唤醒
     *
     * @param int|float $timeout
     * @param string|null $event 唤醒事件名
     * @return void
     */
    public static function sleep(int|float $timeout = 0, ?string $event = null): void;

    /**
     * 协程强制终止
     *
     * @param object|int|string $suspensionOrSuspensionId
     * @param string $message
     * @param int $exitCode
     * @return void
     */
    public static function kill(object|int|string $suspensionOrSuspensionId, string $message = 'kill', int $exitCode = 0): void;

    /**
     * 获取所有挂起的对象
     *
     * @return WeakMap
     * @link HandlerMethods::getSuspensionsWeakMap()
     */
    public static function getSuspensionsWeakMap(): WeakMap;
}
