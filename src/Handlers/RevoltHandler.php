<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Revolt\EventLoop;
use Workbunny\WebmanCoroutine\Exceptions\KilledException;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

use function Workbunny\WebmanCoroutine\package_installed;

/**
 * 基于Ripple插件的协程处理器，支持PHP-fiber
 */
class RevoltHandler implements HandlerInterface
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
            version_compare(static::_getWorkerVersion(), '5.0.0', '>=') and
            package_installed('revolt/event-loop') and
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

    /** @inheritdoc  */
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
                static::sleep($event ? $timeout : (rand(0, 2) / 1000), $event);
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
            $suspension->resume();
        }
    }

    /** @inheritDoc */
    public static function sleep(int|float $timeout = 0, ?string $event = null): void
    {
        try {
            $suspension = EventLoop::getSuspension();
            static::_setSuspensionsWeakMap($suspension, spl_object_hash($suspension), $event, microtime(true));
            if ($event) {
                static::$_suspensions[$event] = $suspension;
                if ($timeout < 0) {
                    $suspension->suspend();

                    return;
                }
            }
            // 毫秒及以上
            if ($timeout >= 0.001) {
                EventLoop::delay((float) $timeout, static function () use ($suspension) {
                    $suspension?->resume();
                });
            }
            // 毫秒以下
            else {
                $start = hrtime(true);
                $timeout = max($timeout, 0);
                EventLoop::defer($fuc = static function () use (&$fuc, $suspension, $timeout, $start) {
                    if ((hrtime(true) - $start) / 1e9 >= $timeout) {
                        $suspension?->resume();
                    } else {
                        EventLoop::defer($fuc);
                    }
                });
            }
            $suspension->suspend();
        } finally {
            if ($event) {
                unset(static::$_suspensions[$event]);
            }
        }
    }

    /** @inheritdoc  */
    public static function kill(object|int|string $suspensionOrSuspensionId, string $message = 'kill', int $exitCode = 0): void
    {
        if ($suspensionOrSuspensionId instanceof EventLoop\Suspension) {
            $info = static::$_suspensionsWeakMap->offsetGet($suspensionOrSuspensionId);
            $suspensionOrSuspensionId->throw(new KilledException($message, $exitCode, $info['event'] ?? null));
        } else {
            /**
             * @var EventLoop\Suspension $object
             * @var array $info
             */
            foreach (static::listSuspensionsWeakMap() as $object => $info) {
                if ($info['id'] === $suspensionOrSuspensionId) {
                    $object->throw(new KilledException($message, $exitCode, $info['event'] ?? null));
                }
            }
        }
    }
}
