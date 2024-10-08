<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine;

use Closure;
use Workbunny\WebmanCoroutine\Factory;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\CoroutineInterface;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\DefaultCoroutine;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\RippleCoroutine;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\SwooleCoroutine;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\SwowCoroutine;
use Workbunny\WebmanCoroutine\Utils\RegisterMethods;

/**
 * @method mixed origin()
 */
class Coroutine
{
    use RegisterMethods;

    /**
     * @var CoroutineInterface|null
     */
    protected ?CoroutineInterface $_interface;

    /**
     * @var string[]
     */
    protected static array $_handlers = [
        Factory::WORKERMAN_SWOW     => SwowCoroutine::class,
        Factory::WORKBUNNY_SWOW     => SwowCoroutine::class,
        Factory::WORKERMAN_SWOOLE   => SwooleCoroutine::class,
        Factory::WORKBUNNY_SWOOLE   => SwooleCoroutine::class,
        Factory::REVOLT_FIBER       => RippleCoroutine::class,
        Factory::RIPPLE_FIBER       => RippleCoroutine::class,
    ];

    /**
     * 构造方法
     */
    /**
     * @param Closure $func
     */
    public function __construct(Closure $func)
    {
        $this->_interface = new (self::$_handlers[Factory::getCurrentEventLoop()] ?? DefaultCoroutine::class)($func);
    }

    public function __destruct()
    {
        $this->_interface = null;
    }

    /** @inheritdoc  */
    public static function registerVerify(mixed $value): false|string
    {
        return is_a($value, CoroutineInterface::class) ? CoroutineInterface::class : false;
    }

    /** @inheritdoc  */
    public static function unregisterExecute(string $key): bool
    {
        return true;
    }

    /**
     * 代理调用WaitGroupInterface方法
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (!method_exists($this->_interface, $name)) {
            throw new \BadMethodCallException("Method $name not exists. ");
        }
        return $this->_interface->$name(...$arguments);
    }
}