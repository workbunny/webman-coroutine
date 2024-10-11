<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Revolt\EventLoop;

class RevoltCoroutine implements CoroutineInterface
{
    /**
     * @var EventLoop\Suspension|null
     */
    protected ?EventLoop\Suspension $_suspension = null;

    /** @inheritdoc */
    public function __construct(\Closure $func)
    {
        $this->_suspension = EventLoop::getSuspension();
        // 将fiber恢复到队列中
        EventLoop::queue(function () {
            $this->_suspension->resume();
        });
        // 当前fiber挂起
        $this->_suspension->suspend();
        try {
            // 等待恢复后执行逻辑
            call_user_func($func, spl_object_hash($this->_suspension));
        } finally {
            $this->_suspension = null;
        }
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        $this->_suspension = null;
    }

    /** @inheritdoc  */
    public function origin(): ?EventLoop\Suspension
    {
        return $this->_suspension;
    }

    /** @inheritdoc  */
    public function id(): ?string
    {
        return $this->_suspension ? spl_object_hash($this->_suspension) : null;
    }
}
