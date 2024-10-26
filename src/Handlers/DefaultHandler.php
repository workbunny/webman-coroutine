<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Handlers;

use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;

/**
 *  默认处理器，使用workerman基础事件
 */
class DefaultHandler implements HandlerInterface
{
    /**
     * 测试用，为保证覆盖生成时不会无限等待
     *
     * @codeCoverageIgnore
     * @var bool
     */
    public static bool $debug = false;

    /**
     * default handler永远返回true
     *
     * @inheritdoc
     */
    public static function isAvailable(): bool
    {
        return true;
    }

    /**
     * default handler无需初始化
     *
     * @inheritdoc
     */
    public static function initEnv(): void
    {
    }

    /** @inheritdoc */
    public static function waitFor(?\Closure $closure = null, float|int $timeout = -1, string $event = 'main'): void
    {
        $time = hrtime(true);
        while (true) {
            if ($closure and call_user_func($closure) === true) {
                return;
            }
            if ($timeout > 0 && hrtime(true) - $time >= $timeout) {
                throw new TimeoutException("Timeout after $timeout seconds.");
            }
            // 测试用，为保证覆盖生成时不会无限等待
            // @codeCoverageIgnoreStart
            if (static::$debug and hrtime(true) - $time >= 20) {
                return;
            }
            // @codeCoverageIgnoreEnd
            static::sleep();
        }
    }

    /** @inheritDoc */
    public static function arouse(string $event = 'main'): void
    {
    }

    /** @inheritDoc */
    public static function sleep(float|int $timeout = 0): void
    {
        usleep(max((int)$timeout * 1000 * 1000, 0));
    }
}
