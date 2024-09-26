<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Webman\App;
use Webman\Http\Request;
use Workbunny\WebmanCoroutine\Events\SwooleEvent;
use Workbunny\WebmanCoroutine\Events\SwowEvent;
use Workbunny\WebmanCoroutine\Handlers\DefaultHandler;
use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;
use Workbunny\WebmanCoroutine\Handlers\SwooleHandler;
use Workbunny\WebmanCoroutine\Handlers\SwooleWorkerman5Handler;
use Workbunny\WebmanCoroutine\Handlers\SwowHandler;
use Workbunny\WebmanCoroutine\Handlers\SwowWorkerman5Handler;
use Workerman\Connection\ConnectionInterface;

class Factory
{
    public const WORKERMAN_SWOW = 'Workerman\Events\Swow';
    public const WORKBUNNY_SWOW = SwowEvent::class;
    public const WORKERMAN_SWOOLE = 'Workerman\Events\Swoole';
    public const WORKBUNNY_SWOOLE = SwooleEvent::class;

    /**
     * 默认支持的处理器
     *
     * @var string[]
     */
    protected static array $_handlers = [
        self::WORKERMAN_SWOW   => SwowWorkerman5Handler::class,
        self::WORKBUNNY_SWOW   => SwowHandler::class,
        self::WORKERMAN_SWOOLE => SwooleWorkerman5Handler::class,
        self::WORKBUNNY_SWOOLE => SwooleHandler::class,
    ];

    /**
     * 当前的处理器
     *
     * @var string|null
     */
    protected static ?string $_currentHandler = null;

    /**
     * @return string|null
     */
    public static function getCurrentHandler(): ?string
    {
        return self::$_currentHandler;
    }

    /**
     * 注册事件处理器
     *
     * @param string $eventLoopClass 事件循环类名
     * @param string $handlerClass 处理器
     * @return bool|null
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
     * @param string $eventLoopClass
     * @return bool
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
     * @param string $eventLoopClass
     * @param bool $check
     * @return string
     */
    public static function get(string $eventLoopClass, bool $check = false): string
    {
        $handlerClass = self::$_handlers[$eventLoopClass] ?? DefaultHandler::class;
        if ($check) {
            if (!method_exists($handlerClass, 'available')) {
                throw new \RuntimeException("handlerClass $handlerClass error [available]! ");
            }
            $handlerClass = $handlerClass::available() ? $handlerClass : DefaultHandler::class;
        }

        return $handlerClass;
    }

    /**
     * 根据当前环境获取可用的处理器
     *
     * @param bool $returnEventLoopClass 是否返回事件循环类名
     * @return string 事件循环类名|处理器类名|空字符串
     */
    public static function find(bool $returnEventLoopClass = false): string
    {
        foreach (self::getAll() as $eventLoopClass => $handlerClass) {
            // 通常不会执行到此逻辑，只是为了ide友好
            if (!method_exists($handlerClass, 'available')) {
                throw new \RuntimeException("handlerClass $handlerClass error [available]! ");
            }
            if ($handlerClass::available()) {
                return $returnEventLoopClass ? $eventLoopClass : $handlerClass;
            }
        }

        return $returnEventLoopClass ? '' : DefaultHandler::class;
    }

    /**
     * 根据当前环境运行处理器
     *
     * @param App $app
     * @param mixed|ConnectionInterface $connection
     * @param mixed|Request $request
     * @param string|null $eventLoopClass
     * @return mixed
     */
    public static function run(App $app, mixed $connection, mixed $request, ?string $eventLoopClass = null): mixed
    {
        // 获取当前处理器
        $handlerClass = self::getCurrentHandler() ?:
            // 赋值，避免重复获取
            self::$_currentHandler = (
                // 如果没有就自动获取
                $eventLoopClass ? self::get($eventLoopClass, true) : self::find()
            );
        // 通常不会执行到此逻辑，只是为了ide友好
        if (!method_exists($handlerClass, 'run')) {
            throw new \RuntimeException('handlerClass error [run]! ');
        }

        return $handlerClass::run($app, $connection, $request);
    }
}
