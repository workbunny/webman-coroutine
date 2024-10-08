<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Channel\Handlers;

use Swow\Channel;

class SwowChannel implements ChannelInterface
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
    public function pop(int|float $timeout = -1): mixed
    {
        return $this->_channel->pop($this->_second2microsecond($timeout));
    }

    /** @inheritdoc */
    public function push(mixed $data, int|float $timeout = -1): mixed
    {
        return $this->_channel->push($data, $this->_second2microsecond($timeout));
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
        return $this->_channel->getCapacity();
    }

    /**
     * @param int|float $timeout
     * @return int
     */
    protected function _second2microsecond(int|float $timeout): int
    {
        return $timeout > 0 ? (int)($timeout * 1000 * 1000) : -1;
    }
}
