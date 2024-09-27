<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Webman\App;
use Workerman\Worker;

trait CoroutineServerMethods
{
    /**
     * 重写onMessage方法
     *
     * @link CoroutineServerInterface::onMessage()
     * @param mixed $connection
     * @param mixed $request
     * @return mixed|null
     */
    public function onMessage($connection, $request)
    {
        try {
            return Factory::run($this, $connection, $request, Worker::$globalEvent::class);
        } catch (\Throwable $e) {
            Worker::log($e->getMessage());
        }

        return null;
    }

    /**
     * 父类onMessage代理
     *
     * @link CoroutineServerInterface::parentOnMessage()
     * @link App::onMessage() 例：web服务的父类方法
     */
    public function parentOnMessage($connection, $request)
    {
        return parent::onMessage($connection, $request);
    }
}