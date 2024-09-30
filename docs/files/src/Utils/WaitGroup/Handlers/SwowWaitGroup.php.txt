<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

use Swow\Sync\WaitGroup;

class SwowWaitGroup implements WaitGroupInterface
{
    /** @var WaitGroup|null  */
    protected ?WaitGroup $_waitGroup;

    /** @var int 计数 */
    protected int $_count;

    /** @inheritdoc  */
    public function __construct()
    {
        $this->_waitGroup = new WaitGroup();
        $this->_count = 0;
    }

    /** @inheritdoc  */
    public function __destruct()
    {
        $this->_waitGroup = null;
        $this->_count = 0;
    }

    /** @inheritdoc  */
    public function add(int $delta = 1): bool
    {
        $this->_waitGroup->add($delta);
        $this->_count ++;
        return true;
    }

    /** @inheritdoc  */
    public function done(): bool
    {
        $this->_waitGroup->done();
        $this->_count --;
        return true;
    }

    /** @inheritdoc  */
    public function count(): int
    {
        return $this->_count;
    }

    /** @inheritdoc  */
    public function wait(int $timeout = -1): void
    {
        $this->_waitGroup->wait($timeout);
    }
}
