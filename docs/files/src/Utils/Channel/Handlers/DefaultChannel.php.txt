<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Channel\Handlers;

class DefaultChannel implements ChannelInterface
{
    /** @var \SplQueue|null */
    protected ?\SplQueue $_queue;

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

    public function pop(int|float $timeout = -1): mixed
    {
        return $this->_queue->dequeue();
    }

    /** @inheritdoc */
    public function push(mixed $data, int|float $timeout = -1): bool
    {
        $this->_queue->enqueue($data);

        return true;
    }

    /** @inheritdoc  */
    public function isEmpty(): bool
    {
        return $this->_queue->isEmpty();
    }

    /** @inheritdoc  */
    public function isFull(): bool
    {
        return !($this->capacity() < 0) && $this->capacity() <= intval($this->_queue?->count());
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
