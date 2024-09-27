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
    /** @var bool|null  */
    protected ?bool $_coroutineServerMethodsCheckClass = null;

    /** @var bool|null  */
    protected ?bool $_coroutineServerMethodsCheckParent = null;

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
            if ($this->_coroutineServerMethodsCheckClass === null) {
                $this->_coroutineServerMethodsCheckClass = $this instanceof CoroutineServerInterface;
            }
            if (!$this->_coroutineServerMethodsCheckClass) {
                throw new \RuntimeException("{$this::class} must implement CoroutineServerInterface. ");
            }
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
        if ($this->_coroutineServerMethodsCheckParent === null) {
            $parentClass = get_parent_class($this);
            $this->_coroutineServerMethodsCheckParent = $parentClass && method_exists($parentClass, 'onMessage');
        }
        if (!$this->_coroutineServerMethodsCheckParent) {
            throw new \RuntimeException("parent::onMessage must be implemented [{$this::class}].");
        }
        return parent::onMessage($connection, $request);
    }
}