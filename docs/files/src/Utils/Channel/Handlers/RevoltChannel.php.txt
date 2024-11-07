<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Channel\Handlers;

use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
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
        $eventId = spl_object_hash($this);
        try {
            // 等待空闲
            RevoltHandler::waitFor(function () {
                return !$this->isEmpty();
            }, timeout: $timeout, event: "channel.pop.$eventId");
        } catch (TimeoutException) {
            return false;
        }
        // 读取数据
        $res = $this->_queue->dequeue();
        // 唤醒push事件
        RevoltHandler::wakeup("channel.push.$eventId");

        return $res;
    }

    /** @inheritdoc */
    public function push(mixed $data, int|float $timeout = -1): bool
    {
        $eventId = spl_object_hash($this);
        try {
            // 等待空闲
            RevoltHandler::waitFor(function () {
                return !$this->isFull();
            }, timeout: $timeout, event: "channel.push.$eventId");
        } catch (TimeoutException) {

            return false;
        }
        // 放入队列
        $this->_queue->enqueue($data);
        // 唤醒pop事件
        RevoltHandler::wakeup("channel.pop.$eventId");

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
