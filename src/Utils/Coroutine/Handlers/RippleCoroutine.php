<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Closure;
use Psc\Core\Coroutine\Promise;
use Revolt\EventLoop\Suspension;

class RippleCoroutine implements CoroutineInterface
{
    /**
     * @var \Revolt\EventLoop\Suspension
     */
    protected Suspension $suspension;

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(Closure $func)
    {
        $this->_async(function (Closure $resolve, Closure $reject) use ($func) {
            $this->suspension = \Co\getSuspension();

            try {
                $result = call_user_func(
                    $func,
                    spl_object_hash($this->origin())
                );

                $resolve($result);
            } catch (\Throwable $exception) {
                $reject($exception);
            } finally {
                unset($this->promise);
            }
        });
    }

    /** @inheritdoc */
    public function __destruct()
    {
    }

    /** @inheritdoc */
    public function origin(): Suspension
    {
        return $this->suspension;
    }

    /** @inheritdoc */
    public function id(): ?string
    {
        return spl_object_hash($this->origin());
    }

    /**
     * @codeCoverageIgnore 用于测试mock，忽略覆盖
     *
     * @param \Closure $closure
     *
     * @return \Psc\Core\Coroutine\Promise
     */
    protected function _async(Closure $closure): Promise
    {
        return \Co\async($closure);
    }
}
