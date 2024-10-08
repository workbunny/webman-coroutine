<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Psc\Core\Coroutine\Promise;
use function Co\async;

class RippleCoroutine implements CoroutineInterface
{
    /**
     * @var null|Promise
     */
    protected ?Promise $_promise;

    /**
     * @var string
     */
    protected string $_id;

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(\Closure $func)
    {
        $this->_promise = $this->_async(function () use (&$promise, $func) {
            try {
                call_user_func($func);
            } finally {
                // 移除协程id及promise
                $this->_promise = null;
            }
        });
        $this->_id = spl_object_hash($this->_promise);
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        $this->_promise = null;
    }

    /** @inheritdoc  */
    public function origin(): ?Promise
    {

        return $this->_promise;
    }

    /** @inheritdoc  */
    public function id(): string
    {
        return $this->_id;
    }

    /**
     * @param \Closure $closure
     * @return mixed
     */
    protected function _async(\Closure $closure)
    {
        return async($closure);
    }
}
