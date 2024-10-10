<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Revolt\EventLoop;
use function Workbunny\WebmanCoroutine\package_installed;

/**
 * 基于Ripple插件的协程处理器，支持PHP-fiber
 */
class RevoltHandler implements HandlerInterface
{
    use HandlerMethods;

    /** @inheritdoc  */
    public static function isAvailable(): bool
    {
        return
            version_compare(static::_getWorkerVersion(), '5.0.0', '>=') and
            package_installed('revolt/event-loop') and
            PHP_VERSION_ID >= 81000;
    }

    /**
     * ripple handler无需初始化
     *
     * @inheritdoc
     */
    public static function initEnv(): void
    {
    }

    /** @inheritdoc  */
    public static function waitFor(?\Closure $closure = null, float|int $timeout = -1): void
    {
        $time = microtime(true);
        while (true) {
            if ($closure and call_user_func($closure) === true) {
                return;
            }
            if ($timeout > 0 && microtime(true) - $time >= $timeout) {
                return;
            }
            RevoltHandler::sleep($timeout);
        }
    }

    /**
     * @param int|float $second 单位：秒
     * @return void
     */
    public static function sleep(int|float $second): void
    {
        $suspension = EventLoop::getSuspension();
        // 毫秒及以上
        if ($second > 0.001) {
            EventLoop::delay((float) $second, function () use ($suspension) {
                $suspension->resume();
            });
        }
        // 毫秒以下
        else {
            $start = microtime(true);
            EventLoop::defer($fuc = function () use (&$fuc, $suspension, $second, $start) {
                if (microtime(true) - $start >= $second) {
                    $suspension->resume();
                } else {
                    EventLoop::defer($fuc);
                }
            });
        }
    }
}
