<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Coroutine;

use Closure;
use WeakMap;
use Workbunny\WebmanCoroutine\Exceptions\KilledException;
use Workbunny\WebmanCoroutine\Factory;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\CoroutineInterface;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\DefaultCoroutine;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\RevoltCoroutine;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\RippleCoroutine;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\SwooleCoroutine;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\SwowCoroutine;
use Workbunny\WebmanCoroutine\Utils\RegisterMethods;

/**
 * @method mixed origin()
 * @method string|int id()
 */
class Coroutine
{
    use RegisterMethods;

    /**
     * @var WeakMap<CoroutineInterface, float|int>|null
     */
    protected static ?WeakMap $_coroutinesWeakMap = null;

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
        Factory::REVOLT_FIBER       => RevoltCoroutine::class,
        Factory::RIPPLE_FIBER       => RippleCoroutine::class,
        Factory::RIPPLE_FIBER_5     => RippleCoroutine::class,
    ];

    /**
     * 构造方法
     *
     * @param Closure $func
     * @link CoroutineInterface::__construct
     */
    public function __construct(Closure $func)
    {
        // 创建协程
        $this->_interface = new (self::$_handlers[Factory::getCurrentEventLoop()] ?? DefaultCoroutine::class)($func);
        // 注册协程
        static::$_coroutinesWeakMap = static::$_coroutinesWeakMap ?: new WeakMap();
        static::$_coroutinesWeakMap->offsetSet($this->_interface, [
            'id'        => $this->_interface->id(),
            'startTime' => microtime(true),
        ]);
    }

    /**
     * 析构
     */
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
     * 返回所有协程
     *
     * @return WeakMap<CoroutineInterface, float|int> [CoroutineInterface, 开始时间戳]
     */
    public static function listCoroutinesWeakMap(): WeakMap
    {
        return static::$_coroutinesWeakMap ?: new WeakMap();
    }

    /**
     * 杀死协程
     *
     * @param object|int|string $coroutineOrCoroutineId
     * @param string $message
     * @param int $exitCode
     * @return void
     */
    public static function kill(object|int|string $coroutineOrCoroutineId, string $message = 'kill', int $exitCode = 0): void
    {
        if ($coroutineOrCoroutineId instanceof CoroutineInterface) {
            $coroutineOrCoroutineId->kill(new KilledException($message, $exitCode));
        } else {
            /**
             * @var CoroutineInterface $object
             * @var array $info
             */
            foreach (static::listCoroutinesWeakMap() as $object => $info) {
                if ($info['id'] === $coroutineOrCoroutineId) {
                    $object->kill(new KilledException($message, $exitCode));
                }
            }
        }
    }

    /**
     * 代理调用WaitGroupInterface方法
     *
     * @codeCoverageIgnore 系统魔术调用，不需要覆盖
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
