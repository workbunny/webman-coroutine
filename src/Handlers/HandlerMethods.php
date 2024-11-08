<?php

declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use WeakMap;
use Workerman\Worker;

trait HandlerMethods
{
    /**
     * @var WeakMap<object, array>|null <挂起对象, ['id' => int|string, 'event' => string|null, 'startTime' => float|int]>
     */
    protected static ?WeakMap $_suspensionsWeakMap = null;

    /**
     * 获取挂起对象
     *
     * @return WeakMap
     */
    public static function listSuspensionsWeakMap(): WeakMap
    {
        return static::$_suspensionsWeakMap ?: new WeakMap();
    }

    /**
     * 添加挂起对象
     *
     * @param object $object
     * @param string|int $id
     * @param string|null $event
     * @param float|int $startTime
     * @return void
     */
    protected static function _setSuspensionsWeakMap(object $object, string|int $id, ?string $event, float|int $startTime): void
    {
        static::$_suspensionsWeakMap = static::$_suspensionsWeakMap ?: new WeakMap;
        static::$_suspensionsWeakMap->offsetSet($object, [
            'id'        => $id,
            'event'     => $event,
            'startTime' => $startTime
        ]);
    }

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
