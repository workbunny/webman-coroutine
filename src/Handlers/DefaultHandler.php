<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Closure;

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
     * default handler无需初始化
     *
     * @inheritdoc
     */
    public static function initEnv(): void
    {
    }

    /** @inheritdoc  */
    public static function waitFor(?\Closure $closure = null, float|int $timeout = -1): void
    {
        if ($closure) {
            call_user_func($closure);
        }
    }
}
