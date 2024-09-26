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
        try {
            return Factory::run($this, $connection, $request, Worker::$globalEvent::class);
        } catch (\Throwable $e) {
            Worker::log($e->getMessage());
        }

        return null;
    }
}
