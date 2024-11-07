<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Workbunny\WebmanCoroutine\Events\SwooleEvent;
use Workbunny\WebmanCoroutine\Events\SwowEvent;
use Workbunny\WebmanCoroutine\Handlers\DefaultHandler;
use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;
use Workbunny\WebmanCoroutine\Handlers\RevoltHandler;
use Workbunny\WebmanCoroutine\Handlers\RippleHandler;
use Workbunny\WebmanCoroutine\Handlers\RippleWorkerman5Handler;
use Workbunny\WebmanCoroutine\Handlers\SwooleHandler;
use Workbunny\WebmanCoroutine\Handlers\SwooleWorkerman5Handler;
use Workbunny\WebmanCoroutine\Handlers\SwowHandler;
use Workbunny\WebmanCoroutine\Handlers\SwowWorkerman5Handler;

/**
 *  工厂化启动器
 */
class Factory
{
    public const WORKERMAN_SWOW = 'Workerman\Events\Swow';
    public const WORKBUNNY_SWOW = SwowEvent::class;
    public const WORKERMAN_SWOOLE = 'Workerman\Events\Swoole';
    public const WORKBUNNY_SWOOLE = SwooleEvent::class;
    public const RIPPLE_FIBER_4 = 'Ripple\Driver\Workerman\Driver4';
    public const RIPPLE_FIBER_5 = 'Ripple\Driver\Workerman\Driver5';
    public const REVOLT_FIBER = 'Workerman\Events\Revolt';
    public const WORKERMAN_DEFAULT = '';

    /*** @Deprecated */
    public const RIPPLE_FIBER = Factory::RIPPLE_FIBER_4;

    /**
     * 默认支持的处理器
     *
     * @var string[]
     */
    protected static array $_handlers = [
        self::WORKERMAN_SWOW    => SwowWorkerman5Handler::class,
        self::WORKBUNNY_SWOW    => SwowHandler::class,
        self::WORKERMAN_SWOOLE  => SwooleWorkerman5Handler::class,
        self::WORKBUNNY_SWOOLE  => SwooleHandler::class,
        self::REVOLT_FIBER      => RevoltHandler::class,
        self::RIPPLE_FIBER      => RippleHandler::class,
        self::RIPPLE_FIBER_5    => RippleWorkerman5Handler::class,
    ];

    /**
     * 当前的事件循环
     *
     * @var string|null
     */
    protected static ?string $_currentEventLoop = null;

    /**
     * 获取当前事件循环
     *
     * @return string|null null:未初始化 空字符串:默认事件
     */
    public static function getCurrentEventLoop(): ?string
    {
        return self::$_currentEventLoop;
    }

    /**
     * 获取当前使用的处理器类名
     *
     * @return string|null null:未初始化
     */
    public static function getCurrentHandler(): ?string
    {
        return self::get(self::$_currentEventLoop) ?: (self::getCurrentEventLoop() === null ? null : DefaultHandler::class);
    }

    /**
     * 注册事件处理器
     *
     * @param string $eventLoopClass 事件循环类名
     * @param string $handlerClass 处理器
     * @return bool|null null:已经存在 bool:是否注册成功
     */
    public static function register(string $eventLoopClass, string $handlerClass): ?bool
    {
        if (self::$_handlers[$eventLoopClass] ?? null) {
            return null;
        }
        if (is_a($handlerClass, HandlerInterface::class, true)) {
            self::$_handlers[$eventLoopClass] = $handlerClass;

            return true;
        }

        return false;
    }

    /**
     * 注销事件处理器
     *
     * @param string $eventLoopClass 事件循环类名
     * @return bool 是否注销成功
     */
    public static function unregister(string $eventLoopClass): bool
    {
        if (isset(self::$_handlers[$eventLoopClass])) {
            unset(self::$_handlers[$eventLoopClass]);

            return true;
        }

        return false;
    }

    /**
     * 获取所有事件处理器
     *
     * @return string[]
     */
    public static function getAll(): array
    {
        return self::$_handlers;
    }

    /**
     * 根据事件循环类获取对应处理器
     *
     * @param null|string $eventLoopClass 指定的事件循环类
     * @return string|null null:未找到
     */
    public static function get(?string $eventLoopClass): ?string
    {
        return self::$_handlers[$eventLoopClass] ?? null;
    }

    /**
     * 初始化
     *
     * @param string|null $eventLoopClass
     * @return void
     */
    public static function init(?string $eventLoopClass): void
    {
        if (!self::$_currentEventLoop) {
            if ($eventLoopClass === null) {
                $handlers = self::getAll();
            } else {
                $handlers = self::get($eventLoopClass) ? [$eventLoopClass => self::get($eventLoopClass)] : [];
            }
            // 默认处理器
            $eventLoopClass = self::WORKERMAN_DEFAULT;
            /**
             * @var string $eventloop
             * @var HandlerInterface $handler
             */
            foreach ($handlers as $eventloop => $handler) {
                if ($handler::isAvailable()) {
                    $eventLoopClass = $eventloop;
                    break;
                }
            }
            // 赋值
            self::$_currentEventLoop = $eventLoopClass;
        }
    }
}
