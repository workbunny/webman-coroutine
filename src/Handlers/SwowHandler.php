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
    /** @inheritdoc  */
    public static function available(): bool
    {
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swow');
    }

    /** @inheritdoc  */
    public static function run(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        $requestChannel = new \Swow\Channel(config('plugin.workbunny.webman-coroutine.app.channel_size', 1));
        $requestChannel->push([
            $connection,
            $request,
        ]);
        $waitGroup = new \Swow\Sync\WaitGroup();
        $res = null;
        $waitGroup->add();
        \Swow\Coroutine::run(function () use (&$res, $app, $requestChannel, $waitGroup) {
            while (1) {
                if (!$data = $requestChannel->pop()) {
                    break;
                }
                [$connection, $request] = $data;
                $res = $app->parentOnMessage($connection, $request);
            }
            $waitGroup->done();
        });
        $waitGroup->wait();

        return $res;
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
