<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

class RippleWaitGroup implements WaitGroupInterface
{
    /** @var int  */
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
        } catch (\Throwable) {} finally {
            $this->_count = 0;
        }

    }

    /** @inheritdoc  */
    public function add(int $delta = 1): bool
    {
        $this->_count ++;
        return true;
    }

    /** @inheritdoc  */
    public function done(): bool
    {
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
        $time = time();
        while (1) {
            if ($timeout > 0 and time() - $time >= $timeout) {
                return;
            }
            if ($this->_count <= 0) {
                return;
            }
            $this->_sleep(0);
        }
    }

    /**
     * @param int $second
     * @return void
     */
    protected function _sleep(int $second): void
    {
        \Co\sleep($second);
    }
}
