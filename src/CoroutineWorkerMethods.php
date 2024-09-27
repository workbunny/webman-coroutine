<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Webman\App;
use Workerman\Worker;

trait CoroutineWorkerMethods
{
    /**
     * 重写onWorkerStart方法
     *
     * @link CoroutineWorkerInterface::onWorkerStart
     * @param mixed|Worker $worker
     * @return mixed
     */
    public function onWorkerStart($worker)
    {
        try {
            if (!$this instanceof CoroutineWorkerInterface) {
                $classname = $this::class;
                throw new \RuntimeException("$classname must implement CoroutineWorkerInterface. ");
            }
            return Factory::start($this, $worker, Worker::$globalEvent::class);
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
    public function parentOnWorkerStart($worker)
    {
        // 获取当前类的父类名称
        $parentClassName = get_parent_class($this);
        $classname = $this::class;
        // 检查是否有父类
        if ($parentClassName === false) {
            throw new \RuntimeException("$classname does not have a parent class.");
        }
        // 检查父类是否实现了 onWorkerStart 方法
        if (!method_exists($parentClassName, 'onWorkerStart')) {
            throw new \RuntimeException("parent::onWorkerStart must be implemented [$classname].");
        }

        return parent::onWorkerStart($worker);
    }
}