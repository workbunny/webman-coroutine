<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers;

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
     * 阻塞等待
     *
     * @param int $timeout
     * @return void
     */
    public function wait(int $timeout = -1): void;
}