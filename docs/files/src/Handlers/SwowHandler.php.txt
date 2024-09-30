<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Closure;
use Swow\Channel;
use Swow\Coroutine;
use Swow\Sync\WaitGroup;
use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workbunny\WebmanCoroutine\Exceptions\HandlerException;
use Workbunny\WebmanCoroutine\Exceptions\SkipWaitGroupDoneException;
use Workerman\Worker;

/**
 *  基于swow实现的协程处理器
 */
class SwowHandler implements HandlerInterface
{
    /**
     * connection channel
     *
     * @var array
     */
    protected static array $_connectionChannels = [];

    /**
     * @var WaitGroup[]
     */
    protected static array $_waitGroups = [];

    /**
     * 创建通道
     *
     * @param string $id
     * @return Channel
     */
    protected static function _createChannel(string $id): Channel
    {
        return self::$_connectionChannels[$id] ?? new Channel(config('plugin.workbunny.webman-coroutine.app.channel_size', 1));
    }

    /**
     * 关闭通道
     *
     * @param string $id
     * @return void
     */
    protected static function _closeChannel(string $id): void
    {
        if (self::$_connectionChannels[$id] ?? null) {
            self::$_connectionChannels[$id]?->close();
            unset(self::$_connectionChannels[$id]);
        }
    }

    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swow');
    }

    /** @inheritdoc  */
    public static function onMessage(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        if (!is_object($connection)) {
            return null;
        }
        // 为每一个连接创建一个通道
        $connectionChannel = self::_createChannel($id = spl_object_hash($connection));
        // 请求生产
        $connectionChannel->push([
            $connection,
            $request,
        ]);
        $waitGroup = new WaitGroup();
        // 根据request consumer数量创建协程
        $consumerCount = config('plugin.workbunny.webman-coroutine.app.consumer_count', 1);
        foreach (range(1, $consumerCount) as $ignored) {
            $waitGroup->add();
            // 请求消费协程
            Coroutine::run(function () use ($app, $connectionChannel, $waitGroup) {
                while (true) {
                    // 通道为空或者关闭时退出协程
                    if (
                        $connectionChannel->isEmpty() or
                        !$data = $connectionChannel->pop()
                    ) {
                        break;
                    }
                    [$connection, $request] = $data;
                    $app->parentOnMessage($connection, $request);
                }
                $waitGroup->done();
            });
        }
        $waitGroup->wait();
        // 关闭通道
        self::_closeChannel($id);

        return null;
    }

    /** @inheritdoc  */
    public static function onWorkerStart(CoroutineWorkerInterface $app, mixed $worker): mixed
    {
        $waitGroup = new WaitGroup();
        $res = null;
        $waitGroup->add();
        Coroutine::run(function () use (&$res, $app, $worker, $waitGroup) {
            $res = $app->parentOnWorkerStart($worker);
            $waitGroup->done();
        });
        $waitGroup->wait();

        return $res;
    }

    /**
     * @inheritdoc
     * @param Closure $function
     * @param string|null $waitGroupId
     * @return Coroutine
     */
    public static function coroutineCreate(Closure $function, ?string $waitGroupId = null): Coroutine
    {
        $res = Coroutine::run(function () use ($function, $waitGroupId) {
            try {
                call_user_func($function);
            } catch (SkipWaitGroupDoneException) {
                // 特定异常才会跳过waitGroup等待
                return;
            }
            if ($waitGroup = self::$_waitGroups[$waitGroupId] ?? null) {
                $waitGroup->done();
            }
        });
        if ($waitGroupId !== null) {
            if (!($waitGroup = self::$_waitGroups[$waitGroupId] ?? null)) {
                throw new HandlerException("WaitGroup $waitGroupId not found [coroutine create]. ");
            }
            $waitGroup->add();
        }

        return $res;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public static function waitGroupCreate(): string
    {
        self::$_waitGroups[
            $id = spl_object_hash($waitGroup = new WaitGroup())
        ] = $waitGroup;

        return $id;
    }

    /**
     * @inheritdoc
     * @param string $waitGroupId
     * @param int $timeout
     * @return void
     */
    public static function waitGroupWait(string $waitGroupId, int $timeout = -1): void
    {
        if (!($waitGroup = self::$_waitGroups[$waitGroupId] ?? null)) {
            throw new HandlerException("WaitGroup $waitGroupId not found [coroutine create]. ");
        }
        $waitGroup->wait($timeout);
        unset(self::$_waitGroups[$waitGroupId]);
    }
}
