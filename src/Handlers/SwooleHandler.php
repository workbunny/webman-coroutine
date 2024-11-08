<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use stdClass;
use Swoole\Coroutine;
use Swoole\Event;
use Swoole\Runtime;
use Swoole\Timer;
use Throwable;
use Workbunny\WebmanCoroutine\Exceptions\KilledException;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

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
            if (Coroutine::exists($suspension)) {
                Coroutine::resume($suspension);
            }
        }
    }

    /** @inheritDoc */
    public static function sleep(float|int $timeout = 0, ?string $event = null): void
    {
        try {
            $suspension = Coroutine::getCid();
            /**
             * @var object{suspension:int, throw:Throwable} $object
             */
            $object = new stdClass();
            $object->suspension = $suspension;
            $object->throw      = null;
            static::setSuspensionsWeakMap($object, $suspension, $event, microtime(true));
            if ($event) {
                static::$_suspensions[$event] = $suspension;
                if ($timeout < 0) {
                    Coroutine::suspend();
                    if ($object->throw instanceof Throwable) {
                        throw $object->throw;
                    }

                    return;
                }
            }
            // 毫秒及以上
            if (($interval = (int) ($timeout * 1000)) >= 1) {
                Timer::after($interval, function () use ($suspension) {
                    if (Coroutine::exists($suspension)) {
                        Coroutine::resume($suspension);
                    }
                });
            }
            // 毫秒以下
            else {
                $start = hrtime(true);
                $timeout = max($timeout, 0);
                Event::defer($fuc = static function () use (&$fuc, $suspension, $timeout, $start) {
                    if ((hrtime(true) - $start) / 1e9 >= $timeout) {
                        if (Coroutine::exists($suspension)) {
                            Coroutine::resume($suspension);
                        }
                    } else {
                        Event::defer($fuc);
                    }
                });
            }
            Coroutine::suspend();
            if ($object->throw instanceof Throwable) {
                throw $object->throw;
            }
        } finally {
            if ($event) {
                unset(static::$_suspensions[$event]);
            }
            unset($object);
        }
    }

    /** @inheritdoc  */
    public static function kill(object|int|string $suspensionOrSuspensionId, string $message = 'kill', int $exitCode = 0): void
    {
        if ($suspensionOrSuspensionId instanceof stdClass) {
            if ($info = static::getSuspensionsWeakMap()->offsetGet($suspensionOrSuspensionId)) {
                $suspensionOrSuspensionId->throw = new KilledException($message, $exitCode, $info['event'] ?? null);
                Coroutine::resume($info['id']);
            }
        } else {
            /**
             * @var object{suspension:int, throw:Throwable} $object
             * @var array $info
             */
            foreach (static::getSuspensionsWeakMap() as $object => $info) {
                if ($info['id'] === $suspensionOrSuspensionId) {
                    $object->throw = new KilledException($message, $exitCode, $info['event'] ?? null);
                    Coroutine::resume($info['id']);
                }
            }
        }
    }
}
