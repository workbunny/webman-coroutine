<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Swow\Coroutine;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workerman\Events\EventInterface;
use Workerman\Worker;

/**
 *  基于swow实现的协程处理器
 */
class SwowHandler implements HandlerInterface
{
    use HandlerMethods;

    /**
     * @var Coroutine[]
     */
    protected static array $_suspensions = [];

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

    /** @inheritdoc */
    public static function waitFor(?\Closure $action = null, float|int $timeout = -1, ?string $event = null): void
    {
        $time = hrtime(true);
        try {
            while (true) {
                if ($action and call_user_func($action) === true) {
                    return;
                }
                if ($timeout > 0 and (hrtime(true) - $time) / 1e9 >= $timeout) {
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
            if ($suspension->isAvailable()) {
                $suspension?->resume();
            }
        }
    }

    /** @inheritDoc */
    public static function sleep(float|int $timeout = 0, ?string $event = null): void
    {
        try {
            $suspension = Coroutine::getCurrent();
            if ($event) {
                static::$_suspensions[$event] = $suspension;
                if ($timeout < 0) {
                    Coroutine::yield();
                    return;
                }
            }
            Coroutine::run(function () use ($timeout, $suspension): void
            {
                usleep((int) ($timeout * 1000 * 1000));
                \call_user_func(function ($suspension) {
                    if ($suspension?->isAvailable()) {
                        $suspension?->resume();
                    }
                }, $suspension);
            });
            Coroutine::yield();
        } finally {
            if ($event) {
                unset(static::$_suspensions[$event]);
            }
        }
    }
}
