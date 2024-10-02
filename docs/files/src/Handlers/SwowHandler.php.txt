<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workerman\Worker;

/**
 *  基于swow实现的协程处理器
 */
class SwowHandler implements HandlerInterface
{

    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return !version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swow');
    }

    /**
     * swow handler无需初始化
     *
     * @inheritdoc
     */
    public static function initEnv(): void
    {
    }
}
