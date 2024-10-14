<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Psc\Core\Coroutine\Promise;
use Revolt\EventLoop\Suspension;

use function Co\async;
use function Co\getSuspension;

class RippleCoroutine implements CoroutineInterface
{
    protected Promise|null $promise = null;

    /** @inheritdoc
     * @param \Closure $func
     */
    public function __construct(\Closure $func)
    {
        $this->promise = $this->_async(function (\Closure $resolve, \Closure $reject) use ($func) {
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
        return getSuspension();
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
     * @return mixed
     */
    protected function _async(\Closure $closure)
    {
        return async($closure);
    }
}
