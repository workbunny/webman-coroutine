<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workerman\Worker;

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
     * 创建通道
     *
     * @param string $id
     * @return \Swoole\Coroutine\Channel
     */
    protected static function _createChannel(string $id): \Swoole\Coroutine\Channel
    {
        return self::$_connectionChannels[$id] ?? new \Swoole\Coroutine\Channel(config('plugin.workbunny.webman-coroutine.app.channel_size', 1));
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
    public static function available(): bool
    {
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swoole');
    }

    /** @inheritdoc  */
    public static function run(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        if (!is_object($connection)) {
            return null;
        }
        if (!self::$_enable) {
            self::$_enable = true;
            \Swoole\Runtime::enableCoroutine();
        }
        // 为每一个连接创建一个通道
        $connectionChannels = self::_createChannel($id = spl_object_hash($connection));
        // 将请求信息推送到通道
        $connectionChannels->push([
            $connection,
            $request,
        ]);
        $waitGroup = new \Swoole\Coroutine\WaitGroup();
        // 根据request consumer数量创建协程
        $consumerCount = config('plugin.workbunny.webman-coroutine.app.consumer_count', 1);
        foreach (range(1, $consumerCount) as $ignored) {
            $waitGroup->add();
            // 协程监听通道，消费
            \Swoole\Coroutine::create(function () use ($app, $connectionChannels, $waitGroup) {
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
        }
        $waitGroup->wait();
        // 关闭通道
        self::_closeChannel($id);

        return null;
    }

    /** @inheritdoc  */
    public static function start(CoroutineWorkerInterface $app, mixed $worker): mixed
    {
        if (!self::$_enable) {
            self::$_enable = true;
            \Swoole\Runtime::enableCoroutine();
        }
        $waitGroup = new \Swoole\Coroutine\WaitGroup();
        $waitGroup->add();
        $res = null;
        \Swoole\Coroutine::create(function () use (&$res, $app, $worker, $waitGroup) {
            $res = $app->parentOnWorkerStart($worker);
            $waitGroup->done();
        });
        $waitGroup->wait();

        return $res;
    }
}
