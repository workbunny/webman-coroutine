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

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(\Closure $func)
    {
        $this->_promise = async(function () use (&$promise, $func) {
            call_user_func($func);
            // 移除协程id及promise
            $this->_promise = null;
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
}
