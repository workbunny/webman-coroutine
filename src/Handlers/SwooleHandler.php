<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Swoole\Runtime;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

/**
 *  基于swoole实现的协程处理器
 */
class SwooleHandler implements HandlerInterface
{
    use HandlerMethods;

    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return version_compare(static::_getWorkerVersion(), '5.0.0', '<') and extension_loaded('swoole');
    }

    /** @inheritdoc */
    public static function initEnv(): void
    {
        Runtime::enableCoroutine();
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
            /**
             * @codeCoverageIgnoreStart 忽略覆盖，该部分直接由swoole接管，无需覆盖
             */
            usleep(max((int) ($timeout * 1000 * 1000), 0));
            /** @codeCoverageIgnoreEnd */
        }
    }
}
