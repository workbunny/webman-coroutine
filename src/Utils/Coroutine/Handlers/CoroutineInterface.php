<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers;

use Throwable;

interface CoroutineInterface
{
    /**
     * 创建协程
     *
     * @param \Closure $func = function (mixed $coroutineId) {}
     */
    public function __construct(\Closure $func);

    /**
     * 销毁协程
     */
    public function __destruct();

    /**
     * 获取协程原始返回
     *
     * @return mixed
     */
    public function origin(): mixed;

    /**
     * 获取协程id
     *
     * @return string|int|null
     */
    public function id(): string|int|null;

    /**
     * 杀死协程
     *
     * @param Throwable $throwable 抛出的异常
     * @return void
     */
    public function kill(Throwable $throwable): void;
}
