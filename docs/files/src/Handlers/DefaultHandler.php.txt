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
    public static function waitFor(?\Closure $action = null, float|int $timeout = -1, ?string $event = null): void
    {
        $time = hrtime(true);
        try {
            while (true) {
                if ($action and call_user_func($action) === true) {
                    return;
                }
                if ($timeout > 0 and ((hrtime(true) - $time) / 1e9 >= $timeout)) {
                    throw new TimeoutException("Timeout after $timeout seconds.");
                }
                // 测试用，为保证覆盖生成时不会无限等待
                // @codeCoverageIgnoreStart
                if (static::$debug and ((hrtime(true) - $time) / 1e9 >= 20)) {
                    return;
                }
                // @codeCoverageIgnoreEnd
                static::sleep();
            }
        } finally {
            if ($event) {
                static::wakeup($event);
            }
        }
    }

    /** @inheritDoc */
    public static function wakeup(string $event): void
    {
    }

    /** @inheritDoc
     * @param float|int $timeout
     * @param string|null $event
     */
    public static function sleep(float|int $timeout = 0, ?string $event = null): void
    {
        usleep(max((int)$timeout * 1000 * 1000, 0));
    }
}
