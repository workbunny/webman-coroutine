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

/**
 *  工厂化启动器
 */
class Factory
{
    public const WORKERMAN_SWOW = 'Workerman\Events\Swow';
    public const WORKBUNNY_SWOW = SwowEvent::class;
    public const WORKERMAN_SWOOLE = 'Workerman\Events\Swoole';
    public const WORKBUNNY_SWOOLE = SwooleEvent::class;
    public const RIPPLE_FIBER = 'Psc\Drive\Workerman\PDrive';
    public const WORKERMAN_DEFAULT = '';

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
    ];

    /**
     * 当前的事件循环
     *
     * @var string|null
     */
    protected static ?string $_currentEventLoop = null;

    /**
     * @return string|null
     */
    public static function getCurrentEventLoop(): ?string
    {
        return self::$_currentEventLoop;
    }

    /**
     * 获取当前使用的处理器类名
     *
     * @return string|null
     */
    public static function getCurrentHandler(): ?string
    {
        return self::$_handlers[self::getCurrentEventLoop()] ??
            (self::getCurrentEventLoop() === null ? DefaultHandler::class : null);
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
     * @param string $eventLoopClass 指定的事件循环类
     * @param bool $available 是否校验当前环境可用性
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
                ? ($returnEventLoopClass
                    ? (isset(self::$_handlers[$eventLoopClass])) ? $eventLoopClass : self::WORKERMAN_DEFAULT
                    : $handlerClass)
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
     * 初始化
     *
     * @param string|null $eventLoopClass
     * @return void
     */
    public static function init(?string $eventLoopClass): void
    {
        if (!self::$_currentEventLoop) {
            // 赋值，避免重复获取
            self::$_currentEventLoop = (
                // 如果没有就自动获取
                $eventLoopClass ? self::get($eventLoopClass, true, true) : self::find(true)
            );
        }
    }

    /**
     * 根据当前环境运行处理器
     *
     * @param CoroutineServerInterface $app 实现CoroutineServerInterface
     * @param mixed|ConnectionInterface $connection 连接资源
     * @param mixed|Request $request 请求体
     * @param string|null $eventLoopClass null:根据环境获取事件循环类
     * @return mixed
     */
    public static function run(CoroutineServerInterface $app, mixed $connection, mixed $request, ?string $eventLoopClass = null): mixed
    {
        self::init($eventLoopClass);
        // 获取当前处理器
        /** @var HandlerInterface $handlerClass */
        $handlerClass = self::getCurrentHandler();

        return $handlerClass::onMessage($app, $connection, $request);
    }

    /**
     * 根据当前环境运行处理器
     *
     * @param CoroutineWorkerInterface $app 实现CoroutineWorkerInterface
     * @param mixed|Worker|null $worker worker对象
     * @param string|null $eventLoopClass null:根据环境获取事件循环类
     * @return mixed
     */
    public static function start(CoroutineWorkerInterface $app, mixed $worker = null, ?string $eventLoopClass = null): mixed
    {
        self::init($eventLoopClass);
        // 获取当前处理器
        /** @var HandlerInterface $handlerClass */
        $handlerClass = self::getCurrentHandler();

        return $handlerClass::onWorkerStart($app, $worker);
    }
}
