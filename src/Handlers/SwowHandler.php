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
        $connectionChannel = self::_createChannel($id = spl_object_hash($connection));
        $connectionChannel->push([
            $connection,
            $request,
        ]);
        $waitGroup = new \Swow\Sync\WaitGroup();
        $waitGroup->add();
        \Swow\Coroutine::run(function () use ($app, $connectionChannel, $waitGroup) {
            while (true) {
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
