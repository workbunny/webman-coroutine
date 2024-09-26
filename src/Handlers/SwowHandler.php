<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Webman\App;
use Workerman\Worker;

class SwowHandler implements HandlerInterface
{
    /** @inheritdoc  */
    public static function available(): bool
    {
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swow');
    }

    /** @inheritdoc  */
    public static function run(App $app, mixed $connection, mixed $request): mixed
    {
        $requestChannel = new \Swow\Channel(1);
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
                $res = $app->onMessage($connection, $request);
            }
            $waitGroup->done();
        });
        $waitGroup->wait();

        return $res;
    }
}
