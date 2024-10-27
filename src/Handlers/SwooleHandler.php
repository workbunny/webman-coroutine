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
    public static function waitFor(?\Closure $action = null, float|int $timeout = -1, ?string $event = null): void
    {
        $time = hrtime(true);
        try {
            while (true) {
                if ($action and call_user_func($action) === true) {
                    return;
                }
                if ($timeout > 0 and hrtime(true) - $time >= $timeout) {
                    throw new TimeoutException("Timeout after $timeout seconds.");
                }
                // 随机协程睡眠0-2ms，避免过多的协程切换
                static::sleep(rand(0, 2) / 1000, $event);
            }
        } finally {
            if ($event) {
                static::wakeup($event);
            }
        }
    }

    /** @inheritDoc */
    public static function wakeup(string $event): void
    {
        if ($suspension = static::$_suspensions[$event] ?? null) {
            Coroutine::resume($suspension);
        }
    }

    /** @inheritDoc */
    public static function sleep(float|int $timeout = 0, ?string $event = null): void
    {
        try {
            $suspension = Coroutine::getCid();
            if ($event) {
                static::$_suspensions[$event] = $suspension;
            }
            // 毫秒及以上
            if (($interval = (int) ($timeout * 1000)) >= 1) {
                Timer::after($interval, function () use ($suspension) {
                    Coroutine::resume($suspension);
                });
            }
            // 毫秒以下
            else {
                $start = hrtime(true);
                $timeout = max($timeout, 0);
                Event::defer($fuc = static function () use (&$fuc, $suspension, $timeout, $start) {
                    if (hrtime(true) - $start >= $timeout) {
                        Coroutine::resume($suspension);
                    } else {
                        Event::defer($fuc);
                    }
                });
            }
            Coroutine::suspend();
        } finally {
            if ($event) {
                unset(static::$_suspensions[$event]);
            }
        }
    }
}
