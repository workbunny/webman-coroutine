<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Closure;
use Revolt\EventLoop\Suspension;
use Ripple\Promise;

class RippleCoroutine implements CoroutineInterface
{
    /**
     * @var null|Suspension
     */
    protected ?Suspension $_suspension = null;

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(Closure $func)
    {
        $this->_suspension = $this->_getSuspension();
        $this->_async(function (Closure $resolve, Closure $reject) use ($func) {
            try {
                $result = call_user_func(
                    $func,
                    $this->id()
                );

                $resolve($result);
            } catch (\Throwable $exception) {
                $reject($exception);
            } finally {
                $this->_suspension = null;
            }
        });
    }

    /** @inheritdoc */
    public function __destruct()
    {
        $this->_suspension = null;
    }

    /** @inheritdoc */
    public function origin(): ?Suspension
    {
        return $this->_suspension;
    }

    /** @inheritdoc */
    public function id(): ?string
    {
        return $this->_suspension ? spl_object_hash($this->_suspension) : null;
    }

    /**
     * @codeCoverageIgnore 用于测试mock，忽略覆盖
     *
     * @param \Closure $closure
     * @return Promise
     */
    protected function _async(Closure $closure): Promise
    {
        return \Co\async($closure);
    }

    /**
     * @codeCoverageIgnore 用于测试mock，忽略覆盖
     * @return Suspension
     */
    protected function _getSuspension(): Suspension
    {
        return \Co\getSuspension();
    }
}
