<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workerman\Worker;

class SwooleHandler implements HandlerInterface
{
    /**
     * @var bool
     */
    protected static bool $enable = false;

    /** @inheritdoc  */
    public static function available(): bool
    {
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swoole');
    }

    /** @inheritdoc  */
    public static function run(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        if (!self::$enable) {
            self::$enable = true;
            \Swoole\Runtime::enableCoroutine();
        }
        $requestChannel = new \Swoole\Coroutine\Channel(config('plugin.workbunny.webman-coroutine.app.channel_size', 1));
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
                $res = $app->parentOnMessage($connection, $request);
            }
            $waitGroup->done();
        });
        $waitGroup->wait();

        return $res;
    }
}
