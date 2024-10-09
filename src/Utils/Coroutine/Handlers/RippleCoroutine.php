<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use function Co\async;

use Psc\Core\Coroutine\Promise;

class RippleCoroutine implements CoroutineInterface
{
    /**
     * @var null|Promise
     */
    protected ?Promise $_promise = null;

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(\Closure $func)
    {
        $this->_promise = $promise = $this->_async(function () use (&$promise, $func) {
            try {
                call_user_func($func, spl_object_hash($this->_promise));
            } finally {
                // 移除协程promise
                $this->_promise = null;
            }
        });
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
    public function id(): ?string
    {
        return $this->_promise ? spl_object_hash($this->_promise) : null;
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
