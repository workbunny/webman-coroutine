<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Channel;

use Workbunny\WebmanCoroutine\Factory;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\ChannelInterface;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\DefaultChannel;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\RippleChannel;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\SwooleChannel;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\SwowChannel;
use Workbunny\WebmanCoroutine\Utils\RegisterMethods;

/**
 * @method mixed pop(int $timeout = -1)
 * @method mixed push(mixed $data, int $timeout = -1)
 * @method bool isEmpty()
 * @method bool isFull()
 * @method int capacity()
 * @method void close()
 */
class Channel
{
    use RegisterMethods;

    /**
     * @var null|ChannelInterface
     */
    protected ?ChannelInterface $_interface;

    /**
     * @var string[]
     */
    protected static array $_handlers = [
        Factory::WORKERMAN_SWOW     => SwowChannel::class,
        Factory::WORKBUNNY_SWOW     => SwowChannel::class,
        Factory::WORKERMAN_SWOOLE   => SwooleChannel::class,
        Factory::WORKBUNNY_SWOOLE   => SwooleChannel::class,
        Factory::REVOLT_FIBER       => RippleChannel::class,
        Factory::RIPPLE_FIBER       => RippleChannel::class,
    ];

    /**
     * 构造方法
     */
    public function __construct(int $capacity = -1)
    {
        $this->_interface = new (self::$_handlers[Factory::getCurrentEventLoop()] ?? DefaultChannel::class)($capacity);
    }

    /**
     * 析构方法
     */
    public function __destruct()
    {
        $this->_interface = null;
    }

    /** @inheritdoc  */
    public static function registerVerify(mixed $value): false|string
    {
        return is_a($value, ChannelInterface::class) ? ChannelInterface::class : false;
    }

    /** @inheritdoc  */
    public static function unregisterExecute(string $key): bool
    {
        return true;
    }

    /**
     * 代理调用ChannelInterface方法
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