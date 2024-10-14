<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */

declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

use LogicException;
use Revolt\EventLoop\Suspension;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

class RippleWaitGroup implements WaitGroupInterface
{
    /*** @var \Revolt\EventLoop\Suspension */
    protected Suspension $suspension;

    /*** @var bool */
    protected bool $done = true;

    /*** @var bool */
    protected bool $isSuspended = false;

    /*** @param int $count */
    public function __construct(protected int $count = 0)
    {
        $this->add($count);
    }

    /**
     * @param int $delta
     *
     * @return bool
     */
    public function add(int $delta = 1): bool
    {
        if ($delta > 0) {
            $this->count += $delta;
            $this->done  = false;
            return true;
        } elseif ($delta < 0) {
            throw new LogicException('delta must be greater than or equal to 0');
        }

        // For the case where $delta is 0, no operation is performed
        return false;
    }

    /**
     * @return bool
     */
    public function done(): bool
    {
        if ($this->count <= 0) {
            throw new LogicException('No tasks to mark as done');
        }

        $this->count--;
        if ($this->count === 0) {
            $this->done = true;
            if ($this->isSuspended) {
                $this->suspension->resume();
            }
        }

        return true;
    }

    /**
     * @param int|float $timeout *
     *
     * @return void
     * @throws TimeoutException
     */
    public function wait(int|float $timeout = -1): void
    {
        if ($this->done) {
            return;
        }

        if (!isset($this->suspension)) {
            $this->suspension = \Co\getSuspension();
        }

        $this->isSuspended = true;

        if ($timeout > 0) {
            $timerId = \Co\delay(function () use ($timeout) {
                $this->suspension->throw(new TimeoutException("Timeout after {$timeout} seconds."));
            }, $timeout);
        }

        $this->suspension->suspend();
        isset($timerId) && \Co\cancel($timerId);
        $this->isSuspended = false;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->suspension);
        $this->count = 0;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }
}
