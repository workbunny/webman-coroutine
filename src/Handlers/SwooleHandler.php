<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Closure;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\WaitGroup;
use Swoole\Runtime;
use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workbunny\WebmanCoroutine\Exceptions\HandlerException;
use Workbunny\WebmanCoroutine\Exceptions\SkipWaitGroupDoneException;
use Workerman\Worker;

/**
 * @desc 基于swoole实现的协程处理器
 */
class SwooleHandler implements HandlerInterface
{
    /**
     * 启用标识
     *
     * @var bool
     */
    protected static bool $_enable = false;

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
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swoole');
    }

    /** @inheritdoc  */
    public static function onMessage(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        if (!is_object($connection)) {
            return null;
        }
        if (!self::$_enable) {
            self::$_enable = true;
            Runtime::enableCoroutine();
        }
        // 为每一个连接创建一个通道
        $connectionChannels = self::_createChannel($id = spl_object_hash($connection));
        // 将请求信息推送到通道
        $connectionChannels->push([
            $connection,
            $request,
        ]);
        $waitGroup = new WaitGroup();
        // 根据request consumer数量创建协程
        $consumerCount = config('plugin.workbunny.webman-coroutine.app.consumer_count', 1);
        foreach (range(1, $consumerCount) as $ignored) {
            // 协程监听通道，消费
            $res = Coroutine::create(function () use ($app, $connectionChannels, $waitGroup) {
                while (true) {
                    // 通道为空或者关闭时退出协程
                    if (
                        $connectionChannels->isEmpty() or
                        !$data = $connectionChannels->pop()
                    ) {
                        break;
                    }
                    [$connection, $request] = $data;
                    $app->parentOnMessage($connection, $request);
                }
                $waitGroup->done();
            });
            if ($res) {
                $waitGroup->add();
            }
        }
        $waitGroup->wait();
        // 关闭通道
        self::_closeChannel($id);

        return null;
    }

    /** @inheritdoc  */
    public static function onWorkerStart(CoroutineWorkerInterface $app, mixed $worker): mixed
    {
        if (!self::$_enable) {
            self::$_enable = true;
            Runtime::enableCoroutine();
        }
        $waitGroup = new WaitGroup();
        $waitGroup->add();
        $res = null;
        Coroutine::create(function () use (&$res, $app, $worker, $waitGroup) {
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
     * @return int|bool
     */
    public static function coroutineCreate(Closure $function, ?string $waitGroupId = null): int|bool
    {
        $res = Coroutine::create(function () use ($function, $waitGroupId) {
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
            if ($res) {
                $waitGroup->add();
            }
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
    }
}
