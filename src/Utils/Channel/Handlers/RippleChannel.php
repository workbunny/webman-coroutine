<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Channel\Handlers;

class RippleChannel implements ChannelInterface
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

    /**
     * @codeCoverageIgnore 用于测试mock，忽略覆盖
     * @param int|float $second
     * @return void
     */
    protected function _sleep(int|float $second): void
    {
        \Co\sleep(max($second, 0));
    }

    /** @inheritdoc  */
    public function pop(int|float $timeout = -1): mixed
    {
        $time = hrtime(true);
        while (1) {
            if (!$this->isEmpty()) {
                return $this->_queue->dequeue();
            } else {
                // timeout
                if ($timeout > 0 and hrtime(true) - $time >= $timeout) {
                    return false;
                }
                $this->_sleep(rand(0,1) / 1000);
            }
        }
    }

    /** @inheritdoc */
    public function push(mixed $data, int|float $timeout = -1): bool
    {
        $time = hrtime(true);
        while (1) {
            if (!$this->isFull()) {
                $this->_queue->enqueue($data);

                return true;
            } else {
                // timeout
                if ($timeout > 0 and hrtime(true) - $time >= $timeout) {
                    return false;
                }
                $this->_sleep(rand(0,1) / 1000);
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
