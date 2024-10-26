<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Swoole\Coroutine;
use Swoole\Event;
use Swoole\Runtime;
use Swoole\Timer;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workerman\Events\EventInterface;

/**
 *  基于swoole实现的协程处理器
 */
class SwooleHandler implements HandlerInterface
{
    use HandlerMethods;

    /**
     * @var int[]
     */
    protected static array $_suspensions = [];

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

    /** @inheritdoc */
    public static function waitFor(?\Closure $closure = null, float|int $timeout = -1, string $event = 'main'): void
    {
        if (!(static::$_suspensions[$event] ?? null)) {
            // 创建协程
            static::$_suspensions[$event] = Coroutine::getCid();
            // 创建1ms的repeat事件去恢复协程
            $eventId = Timer::tick(1, static function () use ($event, &$eventId) {
                if ($suspension = static::$_suspensions[$event] ?? null) {
                    Coroutine::resume($suspension);
                } else {
                    Timer::clear($eventId);
                }
            });
            $time = hrtime(true);
            // 挂起
            Coroutine::suspend();
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
            Coroutine::resume($suspension);
        }
    }

    /** @inheritDoc */
    public static function sleep(float|int $timeout = 0): void
    {
        $suspension = Coroutine::getCid();
        // 毫秒及以上
        if (($interval = (int) ($timeout * 1000)) >= 1) {
            Timer::after($interval, function () use ($suspension) {
                Coroutine::resume($suspension);
            });
        }
        // 毫秒以下
        else {
            $start = hrtime(true);
            Event::defer($fuc = static function () use (&$fuc, $suspension, $timeout, $start) {
                if (hrtime(true) - $start >= $timeout) {
                    Coroutine::resume($suspension);
                } else {
                    Event::defer($fuc);
                }
            });
        }
        Coroutine::suspend();
//        // 使用usleep实现
//        usleep(max((int)($timeout * 1000 * 1000), 0));
    }
}
