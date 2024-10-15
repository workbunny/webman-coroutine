<?php

declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workerman\Worker;

trait HandlerMethods
{
    /**
     * @codeCoverageIgnore 为了测试可以mock
     *
     * @return string
     */
    protected static function _getWorkerVersion(): string
    {
        preg_match('/^(\d+\.\d+\.\d+)/', Worker::VERSION, $matches);

        return $matches[1] ?? '0.0.0';
    }
}
