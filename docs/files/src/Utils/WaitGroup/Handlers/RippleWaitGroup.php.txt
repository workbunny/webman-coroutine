<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

use Closure;
use Revolt\EventLoop\Suspension;

class RippleWaitGroup implements WaitGroupInterface
{
    /** @var int */
    protected int $_count;

    /**
     * @var Suspension|null
     */
    protected ?Suspension $_suspension = null;

    /** @inheritdoc  */
    public function __construct()
    {
        $this->_count = 0;
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        try {
            $count = $this->count();
            if ($count > 0) {
                foreach (range(1, $count) as $ignored) {
                    $this->done();
                }
            }
        } finally {
            $this->_count = 0;
            $this->_suspension = null;
        }
    }

    /** @inheritdoc  */
    public function add(int $delta = 1): bool
    {
        $this->_count++;

        return true;
    }

    /** @inheritdoc  */
    public function done(): bool
    {
        $this->_count--;
        if ($this->_count <= 0) {
            $this->_suspension?->resume();
        }

        return true;
    }

    /** @inheritdoc  */
    public function count(): int
    {
        return $this->_count;
    }

    /** @inheritdoc  */
    public function wait(int|float $timeout = -1): void
    {
        $this->_suspension = $this->_getSuspension();
        if ($timeout > 0) {
            $this->_delay(function () {
                $this->_suspension?->resume();
            }, $timeout);
        }
        $this->_suspension->suspend();
    }

    /**
     * @codeCoverageIgnore 用于测试mock，忽略覆盖
     * @param Closure $closure
     * @param int|float $timeout
     * @return string
     */
    protected function _delay(Closure $closure, int|float $timeout): string
    {
        return \Co\delay($closure, max($timeout, 0.1));
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
