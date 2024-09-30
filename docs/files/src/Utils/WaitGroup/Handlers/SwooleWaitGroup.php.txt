<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

use Swoole\Coroutine\WaitGroup;

class SwooleWaitGroup implements WaitGroupInterface
{
    /** @var WaitGroup|null  */
    protected ?WaitGroup $_waitGroup;

    /** @inheritdoc  */
    public function __construct()
    {
        $this->_waitGroup = new WaitGroup();
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        $this->_waitGroup = null;
    }

    /** @inheritdoc  */
    public function add(int $delta = 1): bool
    {
        $this->_waitGroup->add($delta);
        return true;
    }

    /** @inheritdoc  */
    public function done(): bool
    {
        $this->_waitGroup->done();
        return true;
    }

    /** @inheritdoc  */
    public function count(): int
    {
        return $this->_waitGroup->count();
    }

    /** @inheritdoc  */
    public function wait(int $timeout = -1): void
    {
        $this->_waitGroup->wait($timeout);
    }
}
