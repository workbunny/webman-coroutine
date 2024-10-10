<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

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
        } catch (\Throwable) {
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
        $time = microtime(true);
        while (1) {
            if ($timeout > 0 and microtime(true) - $time >= $timeout) {
                return;
            }
            if ($this->_count <= 0) {
                return;
            }
            $this->_sleep(0);
        }
    }

    /**
     * @param int|float $second
     * @return void
     */
    protected function _sleep(int|float $second): void
    {
        \Co\sleep(max($second, 0));
    }
}
