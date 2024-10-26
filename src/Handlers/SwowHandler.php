<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Swow\Channel;
use Swow\ChannelException;
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
     * @var Channel[]
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
    public static function waitFor(?\Closure $closure = null, float|int $timeout = -1, string $event = 'main'): void
    {
        if (!(static::$_suspensions[$event] ?? null)) {
            // 创建通道
            static::$_suspensions[$event] = $channel = new Channel();
            // 创建1ms的repeat事件去恢复通道
            $timerId = Worker::$globalEvent->add(0.001, EventInterface::EV_TIMER, static function () use (&$timerId, $event) {
                if ($channel = static::$_suspensions[$event]) {
                    $channel->push(1);
                } else {
                    Worker::$globalEvent->del($timerId, EventInterface::EV_TIMER);
                }
            });
            $time = hrtime(true);
            try {
                // 利用channel阻塞，挂起
                $channel->pop($timeout);
            } catch (ChannelException) {}
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
                // 回收
                static::$_suspensions[$event]?->close();
                unset(static::$_suspensions[$event]);
            }
        }
    }

    /** @inheritDoc */
    public static function arouse(string $event = 'main'): void
    {
        if ($channel = static::$_suspensions[$event]) {
            $channel->push(1);
        }
    }

    /** @inheritDoc */
    public static function sleep(float|int $timeout = 0): void
    {
        usleep(max((int)($timeout * 1000 * 1000), 0));
    }
}
