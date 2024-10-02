<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

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
}
