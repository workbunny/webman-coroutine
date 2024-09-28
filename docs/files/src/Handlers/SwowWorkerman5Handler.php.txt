<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workerman\Worker;

/**
 * @desc 基于swow实现的Workerman5.X的协程处理器
 */
class SwowWorkerman5Handler extends SwowHandler
{
    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swow');
    }
}
