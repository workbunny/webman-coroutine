<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Webman\Http\Request;
use Workbunny\WebmanCoroutine\Events\SwooleEvent;
use Workbunny\WebmanCoroutine\Events\SwowEvent;
use Workbunny\WebmanCoroutine\Handlers\DefaultHandler;
use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;
use Workbunny\WebmanCoroutine\Handlers\RippleHandler;
use Workbunny\WebmanCoroutine\Handlers\SwooleHandler;
use Workbunny\WebmanCoroutine\Handlers\SwooleWorkerman5Handler;
use Workbunny\WebmanCoroutine\Handlers\SwowHandler;
use Workbunny\WebmanCoroutine\Handlers\SwowWorkerman5Handler;
use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

class Factory
{
    public const WORKERMAN_SWOW     = 'Workerman\Events\Swow';
    public const WORKBUNNY_SWOW     = SwowEvent::class;
    public const WORKERMAN_SWOOLE   = 'Workerman\Events\Swoole';
    public const WORKBUNNY_SWOOLE   = SwooleEvent::class;
    public const RIPPLE_FIBER       = 'Psc\Drive\Workerman\PDrive';
    public const WORKERMAN_DEFAULT  = '';

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
        self::RIPPLE_FIBER      => RippleHandler::class,
        self::WORKERMAN_DEFAULT => DefaultHandler::class,
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
     * @param bool $available
     * @param bool $returnEventLoopClass 只在available=true时生效
     * @return string
     */
    public static function get(string $eventLoopClass, bool $available = false, bool $returnEventLoopClass = false): string
    {
        /** @var HandlerInterface $handlerClass */
        $handlerClass = self::$_handlers[$eventLoopClass] ?? DefaultHandler::class;
        if ($available) {
            // 当$returnEventLoopClass=true时，返回的是eventloop classname而不是handler classname
            $handlerClass = $handlerClass::isAvailable()
                ? ($returnEventLoopClass ? $eventLoopClass : $handlerClass)
                : ($returnEventLoopClass ? self::WORKERMAN_DEFAULT : DefaultHandler::class);
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
        /**
         * @var string $eventLoopClass
         * @var HandlerInterface $handlerClass
         */
        foreach (self::getAll() as $eventLoopClass => $handlerClass) {
            // 判断当前环境是否可用，相同可用的取优先
            if ($handlerClass::isAvailable()) {
                return $returnEventLoopClass ? $eventLoopClass : $handlerClass;
            }
        }

        return $returnEventLoopClass ? self::WORKERMAN_DEFAULT : DefaultHandler::class;
    }

    /**
     * 根据当前环境运行处理器
     *
     * @param CoroutineServerInterface $app
     * @param mixed|ConnectionInterface $connection
     * @param mixed|Request $request
     * @param string|null $eventLoopClass
     * @return mixed
     */
    public static function run(CoroutineServerInterface $app, mixed $connection, mixed $request, ?string $eventLoopClass = null): mixed
    {
        // 获取当前处理器
        /** @var HandlerInterface $handlerClass */
        $handlerClass = self::getCurrentHandler() ?:
            // 赋值，避免重复获取
            self::$_currentHandler = (
                // 如果没有就自动获取
                $eventLoopClass ? self::get($eventLoopClass, true) : self::find()
            );

        return $handlerClass::onMessage($app, $connection, $request);
    }

    /**
     * 根据当前环境运行处理器
     *
     * @param CoroutineWorkerInterface $app
     * @param mixed|Worker|null $worker
     * @param string|null $eventLoopClass
     * @return mixed
     */
    public static function start(CoroutineWorkerInterface $app, mixed $worker = null, ?string $eventLoopClass = null): mixed
    {
        // 获取当前处理器
        /** @var HandlerInterface $handlerClass */
        $handlerClass = self::getCurrentHandler() ?:
            // 赋值，避免重复获取
            self::$_currentHandler = (
                // 如果没有就自动获取
                $eventLoopClass ? self::get($eventLoopClass, true) : self::find()
            );

        return $handlerClass::onWorkerStart($app, $worker);
    }
}
