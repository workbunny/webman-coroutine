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

class SwowHandler implements HandlerInterface
{

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
     * @return \Swow\Channel
     */
    protected static function _createChannel(string $id): \Swow\Channel
    {
        return self::$_connectionChannels[$id] ?? new \Swow\Channel(config('plugin.workbunny.webman-coroutine.app.channel_size', 1));
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
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swow');
    }

    /** @inheritdoc  */
    public static function run(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
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
        $waitGroup = new \Swow\Sync\WaitGroup();
        $waitGroup->add();
        // 请求消费协程
        \Swow\Coroutine::run(function () use ($app, $connectionChannel, $waitGroup) {
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
        $waitGroup->wait();
        // 关闭通道
        self::_closeChannel($id);

        return null;
    }

    /** @inheritdoc  */
    public static function start(CoroutineWorkerInterface $app, mixed $worker): mixed
    {
        $waitGroup = new \Swow\Sync\WaitGroup();
        $res = null;
        $waitGroup->add();
        \Swow\Coroutine::run(function () use (&$res, $app, $worker, $waitGroup) {
            $res = $app->parentOnWorkerStart($worker);
            $waitGroup->done();
        });
        $waitGroup->wait();

        return $res;
    }
}
