<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workerman\Worker;

class SwooleWorkerman5Handler extends SwooleHandler
{
    /** @inheritdoc  */
    public static function available(): bool
    {
        return version_compare(Worker::VERSION, '5.0.0', '>=') and extension_loaded('swoole');
    }
}
