<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanSwow;

use Webman\App;
use Workerman\Worker;

class CoroutineWebServer extends App
{
    public const WORKERMAN_SWOW   = 'Workerman\Events\Swow';
    public const WORKBUNNY_SWOW   = SwowEvent::class;
    public const WORKERMAN_SWOOLE = 'Workerman\Events\Swoole';
    public const WORKBUNNY_SWOOLE = SwooleEvent::class;


    /** @inheritdoc  */
    public function onWorkerStart($worker)
    {
        if (!\config('plugin.workbunny.webman-swow.app.enable', false)) {
            return;
        }
        parent::onWorkerStart($worker);
    }

    /** @inheritdoc  */
    public function onMessage($connection, $request)
    {
        switch (Worker::$globalEvent::class) {
            case self::WORKBUNNY_SWOW:
            case self::WORKERMAN_SWOW:
                $requestChannel = new \Swow\Channel(1);
                $requestChannel->push([
                    $connection,
                    $request,
                ]);
                $waitGroup = new \Swow\Sync\WaitGroup();
                $waitGroup->add();
                \Swow\Coroutine::run(function () use ($requestChannel, $waitGroup) {
                    while (1) {
                        if (!$data = $requestChannel->pop()) {
                            break;
                        }
                        [$connection, $request] = $data;
                        parent::onMessage($connection, $request);
                    }
                    $waitGroup->done();
                });
                $waitGroup->wait();
                break;
            case self::WORKBUNNY_SWOOLE:
            case self::WORKERMAN_SWOOLE:
                $requestChannel = new \Swoole\Coroutine\Channel();
                $requestChannel->push([
                    $connection,
                    $request,
                ]);
                $waitGroup = new \Swoole\Coroutine\WaitGroup();
                $waitGroup->add();
                \Swoole\Coroutine::create(function () use ($requestChannel, $waitGroup) {
                    while (1) {
                        if (!$data = $requestChannel->pop()) {
                            break;
                        }
                        [$connection, $request] = $data;
                        parent::onMessage($connection, $request);
                    }
                    $waitGroup->done();
                });
                $waitGroup->wait();
                break;
            default:
                return parent::onMessage($connection, $request);
        }
        // 交还控制权给event-loop
        return null;
    }
}
