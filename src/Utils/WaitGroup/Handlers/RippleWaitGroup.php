<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\RippleHandler;

class RippleWaitGroup implements WaitGroupInterface
{
    /** @var int */
    protected int $_count;

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
            RippleHandler::wakeup(spl_object_hash($this));
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
        RippleHandler::sleep($timeout, spl_object_hash($this));
        if ($this->count() > 0) {
            throw new TimeoutException("Timeout after $timeout seconds [WaitGroup]. ");
        }
    }
}
