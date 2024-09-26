<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanSwow\Handlers;

use Webman\App;
use Workerman\Worker;

class SwooleHandler implements HandlerInterface
{

    /** @inheritdoc  */
    public static function available(): bool
    {
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swoole');
    }

    /** @inheritdoc  */
    public static function run(App $app, mixed $connection, mixed $request): mixed
    {
        $requestChannel = new \Swoole\Coroutine\Channel();
        $requestChannel->push([
            $connection,
            $request,
        ]);
        $waitGroup = new \Swoole\Coroutine\WaitGroup();
        $waitGroup->add();
        $res = null;
        \Swoole\Coroutine::create(function () use (&$res, $app, $requestChannel, $waitGroup) {
            while (1) {
                if (!$data = $requestChannel->pop()) {
                    break;
                }
                [$connection, $request] = $data;
                $res = $app->onMessage($connection, $request);
            }
            $waitGroup->done();
        });
        $waitGroup->wait();
        return $res;
    }
}
