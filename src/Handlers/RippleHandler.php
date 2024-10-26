<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

use function Workbunny\WebmanCoroutine\package_installed;

/**
 * 基于Ripple插件的协程处理器，支持PHP-fiber
 */
class RippleHandler implements HandlerInterface
{
    use HandlerMethods;

    /**
     * @var EventLoop\Suspension[]
     */
    protected static array $_suspensions = [];

    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return
            version_compare(static::_getWorkerVersion(), '5.0.0', '<') and
            package_installed('cloudtay/ripple-driver') and
            PHP_VERSION_ID >= 80100;
    }

    /**
     * ripple handler无需初始化
     *
     * @inheritdoc
     */
    public static function initEnv(): void
    {
    }

    /** @inheritdoc */
    public static function waitFor(?\Closure $closure = null, float|int $timeout = -1, string $event = 'main'): void
    {
        if (!(static::$_suspensions[$event] ?? null)) {
            // 创建协程
            static::$_suspensions[$event] = $suspension = static::_getSuspension();
            // 创建1ms的repeat事件去恢复协程
            $eventId = static::_repeat(static function () use ($event, &$eventId) {
                if ($suspension = static::$_suspensions[$event] ?? null) {
                    $suspension->suspend();
                } else {
                    static::_cancel($eventId);
                }
            }, 0.001);
            $time = hrtime(true);
            // 挂起
            $suspension->suspend();
            try {
                // 被检查的回调
                if ($closure and call_user_func($closure) === true) {
                    return;
                }
                // 超时检查
                if ($timeout > 0 and hrtime(true) - $time >= $timeout) {
                    throw new TimeoutException("Timeout after $timeout seconds.");
                }
            } finally {
                // 回收协程
                unset(static::$_suspensions[$event]);
            }
        }
    }

    /** @inheritDoc */
    public static function arouse(string $event = 'main'): void
    {
        if ($suspension = static::$_suspensions[$event] ?? null) {
            $suspension->resume();
        }
    }

    /** @inheritDoc */
    public static function sleep(int|float $timeout = 0): void
    {
        // @codeCoverageIgnoreStart
        \Co\sleep(max($timeout, 0));
        // @codeCoverageIgnoreEnd
    }

    /**
     * @codeCoverageIgnore 用于测试mock，忽略覆盖
     * @return Suspension
     */
    protected static function _getSuspension(): Suspension
    {
        return \Co\getSuspension();
    }

    /**
     * @codeCoverageIgnore 用于测试mock，忽略覆盖
     * @param \Closure $closure
     * @param int|float $timeout
     * @return string
     */
    protected static function _repeat(\Closure $closure, int|float $timeout): string
    {
        return \Co\repeat($closure, $timeout);
    }

    /**
     * @codeCoverageIgnore 用于测试mock，忽略覆盖
     * @param string $eventId
     * @return void
     */
    protected static function _cancel(string $eventId): void
    {
        \Co\cancel($eventId);
    }
}
