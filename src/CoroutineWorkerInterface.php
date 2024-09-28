<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Workerman\Worker;

/**
 * @desc 自定义worker代理协程化接口
 */
interface CoroutineWorkerInterface
{
    /**
     * 子类需要将onWorkerStart重写
     * 父类onWorkerStart请使用@link parentOnWorkerStart 实现
     *
     * @param mixed|Worker $worker
     * @return mixed
     */
    public function onWorkerStart($worker);

    /**
     * 父类onWorkerStart将会被重写，将父类的onWorkerStart方法代理到子类
     *
     * @param mixed|Worker $worker
     * @return mixed
     */
    public function parentOnWorkerStart($worker);
}
