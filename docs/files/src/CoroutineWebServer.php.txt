<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Webman\App;

/**
 * @desc 协程化web服务进程
 */
class CoroutineWebServer extends App implements CoroutineServerInterface
{
    use CoroutineServerMethods;

    /** @inheritdoc  */
    public function onWorkerStart($worker)
    {
        if (!\config('plugin.workbunny.webman-coroutine.app.enable', false)) {
            return;
        }
        parent::onWorkerStart($worker);
    }
}
