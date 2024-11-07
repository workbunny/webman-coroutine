<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

interface WaitGroupInterface
{
    /**
     * 初始化
     */
    public function __construct();

    /**
     * 销毁
     */
    public function __destruct();

    /**
     * 增加一个计数
     *
     * @param int $delta
     * @return bool
     */
    public function add(int $delta = 1): bool;

    /**
     * 完成一个计数
     *
     * @return bool
     */
    public function done(): bool;

    /**
     * 返回计数
     *
     * @return int
     */
    public function count(): int;

    /**
     * 阻塞等待
     *
     * @param int|float $timeout 单位：秒
     * @return void
     * @throws TimeoutException
     */
    public function wait(int|float $timeout = -1): void;
}
