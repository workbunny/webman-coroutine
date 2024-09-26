<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanSwow\Handlers;

use Webman\App;
use Webman\Http\Request;
use Workerman\Connection\ConnectionInterface;

interface HandlerInterface
{
    /**
     * 用于判断当前环境是否可用
     *
     * @return bool 返回是否可用
     */
    public static function available(): bool;

    /**
     * 执行协程处理
     *
     * @param App $app
     * @param mixed|ConnectionInterface $connection
     * @param mixed|Request $request
     * @return mixed
     */
    public static function run(App $app, mixed $connection, mixed $request): mixed;
}
