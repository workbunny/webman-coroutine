<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Webman\Http\Request;
use Workerman\Connection\ConnectionInterface;

/**
 * @desc 自定义服务代理协程化接口
 */
interface CoroutineServerInterface
{
    /**
     * 子类需要将onMessage重写
     * 父类onMessage请使用@link parentOnMessage 实现
     *
     * @param mixed|ConnectionInterface $connection
     * @param mixed|Request|array|string $request
     * @return mixed
     */
    public function onMessage($connection, $request);

    /**
     * 父类onMessage将会被重写，将父类的onMessage方法代理到子类
     *
     * @param mixed|ConnectionInterface $connection
     * @param mixed|Request|array|string $request
     * @return mixed
     */
    public function parentOnMessage($connection, $request);
}
