<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Channel\Handlers;

use Swoole\Coroutine\Channel;

class SwooleChannel implements ChannelInterface
{

    /** @var Channel  */
    protected Channel $_channel;

    /** @inheritdoc  */
    public function __construct(int $capacity = -1)
    {
        $this->_channel = new Channel($capacity);
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        $this->close();
    }

    /** @inheritdoc  */
    public function pop(int $timeout = -1): mixed
    {
        return $this->_channel->pop($timeout);
    }

    /** @inheritdoc */
    public function push(mixed $data, int $timeout = -1): mixed
    {
        return $this->_channel->push($data, $timeout);
    }

    /** @inheritdoc  */
    public function isEmpty(): bool
    {
        return $this->_channel->isEmpty();
    }

    /** @inheritdoc  */
    public function isFull(): bool
    {
        return $this->_channel->isFull();
    }

    /** @inheritdoc  */
    public function close(): void
    {
        $this->_channel->close();
    }

    /** @inheritdoc  */
    public function capacity(): int
    {
        return $this->_channel->capacity;
    }
}
