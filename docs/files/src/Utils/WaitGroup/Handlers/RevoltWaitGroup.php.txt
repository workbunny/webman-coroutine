<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

use Revolt\EventLoop\Suspension;
use Revolt\EventLoop;

class RevoltWaitGroup implements WaitGroupInterface
{
    /** @var int */
    protected int $_count;

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
        $this->_suspension = EventLoop::getSuspension();
        if ($timeout > 0) {
            EventLoop::delay($timeout, function () {
                $this->_suspension?->resume();
            });
        }
        $this->_suspension->suspend();
    }
}
