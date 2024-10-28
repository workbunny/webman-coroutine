<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

/**
 *  基于swow实现的协程处理器
 */
class SwowHandler implements HandlerInterface
{
    use HandlerMethods;

    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return !version_compare(static::_getWorkerVersion(), '5.0.0', '>=') and extension_loaded('swow');
    }

    /**
     * swow handler无需初始化
     *
     * @inheritdoc
     */
    public static function initEnv(): void
    {
    }

    /** @inheritdoc  */
    public static function waitFor(?\Closure $closure = null, float|int $timeout = -1): void
    {
        $time = microtime(true);
        while (true) {
            if ($closure and call_user_func($closure) === true) {
                return;
            }
            if ($timeout > 0 && microtime(true) - $time >= $timeout) {
                throw new TimeoutException("Timeout after $timeout seconds.");
            }
            usleep(0);
        }
    }
}
