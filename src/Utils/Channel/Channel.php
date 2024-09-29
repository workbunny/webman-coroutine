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
use Workbunny\WebmanCoroutine\Utils\RegisterMethods;

class Channel
{
    use RegisterMethods;

    protected ChannelInterface $_interface;

    protected static array $_handlers = [

    ];

    /**
     * 构造方法
     */
    public function __construct(int $capacity = -1)
    {
        $this->_interface = new (self::$_handlers[Factory::getCurrentEventLoop()] ?? DefaultChannel::class)($capacity);
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