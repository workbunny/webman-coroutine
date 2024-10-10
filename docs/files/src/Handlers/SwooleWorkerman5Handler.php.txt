<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

/**
 *  基于swoole实现的Workerman5.x版本协程处理器
 */
class SwooleWorkerman5Handler extends SwooleHandler
{
    use HandlerMethods;

    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return version_compare(static::_getWorkerVersion(), '5.0.0', '>=') and extension_loaded('swoole');
    }
}
