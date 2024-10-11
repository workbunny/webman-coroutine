<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

/**
 *  协程处理器接口
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
     * 用于环境加载初始化
     *
     * @return void
     */
    public static function initEnv(): void;

    /**
     * 等待直到回调返回true
     *
     * @param \Closure|null $closure 返回true或抛出异常则跳出等待
     * @param int|float $timeout 单位：秒| -1：不限制等待时间
     * @return void
     * @throws TimeoutException
     */
    public static function waitFor(?\Closure $closure = null, int|float $timeout = -1): void;
}
