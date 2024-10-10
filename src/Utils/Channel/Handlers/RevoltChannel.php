<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Channel\Handlers;

use Workbunny\WebmanCoroutine\Handlers\RevoltHandler;

class RevoltChannel implements ChannelInterface
{
    /** @var \SplQueue */
    protected \SplQueue $_queue;

    /** @var int */
    protected int $_capacity;

    /** @inheritdoc  */
    public function __construct(int $capacity = -1)
    {
        $this->_queue = new \SplQueue();
        $this->_capacity = $capacity;
    }

    /** @inheritdoc  */
    public function __destruct()
    {
    }

    /** @inheritdoc  */
    public function pop(int|float $timeout = -1): mixed
    {
        $time = microtime(true);
        while (1) {
            if (!$this->isEmpty()) {
                return $this->_queue->dequeue();
            } else {
                // timeout
                if ($timeout > 0 and microtime(true) - $time >= $timeout) {
                    return false;
                }
                RevoltHandler::sleep(max($timeout, 0));
            }
        }
    }

    /** @inheritdoc */
    public function push(mixed $data, int|float $timeout = -1): bool
    {
        $time = microtime(true);
        while (1) {
            if (!$this->isFull()) {
                $this->_queue->enqueue($data);

                return true;
            } else {
                // timeout
                if ($timeout > 0 and microtime(true) - $time >= $timeout) {
                    return false;
                }
                RevoltHandler::sleep(max($timeout, 0));
            }
        }
    }

    /** @inheritdoc  */
    public function isEmpty(): bool
    {
        return $this->_queue->isEmpty();
    }

    /** @inheritdoc  */
    public function isFull(): bool
    {
        return !($this->capacity() < 0) && $this->capacity() <= $this->_queue->count();
    }

    /** @inheritdoc  */
    public function close(): void
    {
        $this->_queue = new \SplQueue();
    }

    /** @inheritdoc  */
    public function capacity(): int
    {
        return $this->_capacity;
    }
}
