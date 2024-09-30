<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

interface CoroutineInterface
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
     * 创建一个协程
     *
     * @param \Closure $func
     * @return string 协程id
     */
    public function create(\Closure $func): string;

    /**
     * 获取协程对象，部分实现不支持
     *
     * @param string $id
     * @return mixed null:不存在 false:不支持
     */
    public function query(string $id): mixed;
}