<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

class DefaultWaitGroup implements WaitGroupInterface
{
    /** @inheritdoc  */
    public function __construct()
    {
    }

    /** @inheritdoc  */
    public function __destruct()
    {
    }

    /** @inheritdoc  */
    public function add(int $delta = 1): bool
    {
        return true;
    }

    /** @inheritdoc  */
    public function done(): bool
    {
        return true;
    }

    /** @inheritdoc  */
    public function count(): int
    {
        return 0;
    }

    /** @inheritdoc  */
    public function wait(int|float $timeout = -1): void
    {
    }
}
