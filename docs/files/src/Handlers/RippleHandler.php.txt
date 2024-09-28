<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Closure;

use function Co\async;
use function Co\await;

use Psc\Core\Coroutine\Promise;
use Throwable;
use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workbunny\WebmanCoroutine\Exceptions\HandlerException;

use function Workbunny\WebmanCoroutine\package_installed;

use Workerman\Worker;

/**
 * 基于Ripple插件的协程处理器，支持PHP-fiber
 */
class RippleHandler implements HandlerInterface
{
    /**
     * @var \stdClass[]
     */
    protected static array $_waitGroups = [];

    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return package_installed('cclilshy/p-ripple-drive');
    }

    /** @inheritdoc  */
    public static function onMessage(CoroutineServerInterface $app, mixed $connection, mixed $request): mixed
    {
        try {
            return await(
                async(function () use ($app, $connection, $request) {
                    return $app->parentOnMessage($connection, $request);
                })
            );
        } catch (Throwable $e) {
            Worker::log($e->getMessage());
        }

        return null;
    }

    /** @inheritdoc  */
    public static function onWorkerStart(CoroutineWorkerInterface $app, mixed $worker): mixed
    {
        try {
            return await(
                async(function () use ($app, $worker) {
                    return $app->parentOnWorkerStart($worker);
                })
            );
        } catch (Throwable $e) {
            Worker::log($e->getMessage());
        }

        return null;
    }

    /**
     * @inheritdoc
     * @param Closure $function
     * @param string|null $waitGroupId
     * @return Promise
     * @throws HandlerException 使用一个不存在的waitGroupId会抛出异常
     */
    public static function coroutineCreate(Closure $function, ?string $waitGroupId = null): Promise
    {
        $promise = async($function);
        if ($waitGroupId !== null) {
            if (!($waitGroup = self::$_waitGroups[$waitGroupId] ?? null)) {
                throw new HandlerException("WaitGroup $waitGroupId not found [coroutine create]. ");
            }
            $waitGroup->promiseList[spl_object_hash($promise)] = $promise;
        }

        return $promise;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public static function waitGroupCreate(): string
    {
        $waitGroup = new \stdClass();
        self::$_waitGroups[$id = spl_object_hash($waitGroup)] = $waitGroup;

        return $id;
    }

    /**
     * @inheritdoc
     * @param string $waitGroupId
     * @param int $timeout ripple不生效
     * @return void
     * @throws Throwable
     */
    public static function waitGroupWait(string $waitGroupId, int $timeout = -1): void
    {
        if (!($waitGroup = self::$_waitGroups[$waitGroupId] ?? null)) {
            throw new HandlerException("WaitGroup $waitGroupId not found [wait]. ");
        }
        try {
            foreach ($waitGroup->promiseList as $key => $promise) {
                await($promise);
                unset($waitGroup->promiseList[$key]);
            }
        } finally {
            unset(self::$_waitGroups[$waitGroupId]);
        }
    }
}
