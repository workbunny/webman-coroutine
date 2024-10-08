<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Channel\Handlers;

interface ChannelInterface
{

    /**
     * 初始化通道
     *
     * @param int $capacity 初始容量
     */
    public function __construct(int $capacity = -1);

    /**
     * 摧毁通道
     */
    public function __destruct();

    /**
     * 获取一个数据
     *
     * @param int|float $timeout 单位：秒
     * @return mixed false:通道关闭
     */
    public function pop(int|float $timeout = -1): mixed;

    /**
     * 推送一个数据
     *
     * @param mixed $data
     * @param int|float $timeout 单位：秒
     * @return mixed
     */
    public function push(mixed $data, int|float $timeout = -1): mixed;

    /**
     * 通道是否为空
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * 通道是否满
     *
     * @return bool
     */
    public function isFull(): bool;

    /**
     * 获取通道容量
     *
     * @return int
     */
    public function capacity(): int;

    /**
     * 关闭通道
     *
     * @return void
     */
    public function close(): void;
}