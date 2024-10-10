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

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(\Closure $func)
    {
        $this->_suspension = EventLoop::getSuspension();
        EventLoop::queue(function () {
            $this->_suspension->resume();
        });
        $this->_suspension->suspend();
        try {
            call_user_func($func);
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
