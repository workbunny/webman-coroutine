<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Worker;

use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup;
use Workerman\Connection\ConnectionInterface;

trait ServerMethods
{
    /**
     * 每个连接的协程计数
     *
     * @var int[]
     */
    protected static array $_connectionCoroutineCount = [];

    /**
     * 连接关闭/开启协程化
     *
     * @var bool
     */
    protected bool $_connectionCoroutine = false;

    /**
     * 连接请求响应等待
     *
     * @var bool
     */
    protected bool $_connectionOnMessageWait = true;

    /**
     * 父类的onConnect
     *
     * @var callable|null
     */
    protected $_parentOnConnect = null;

    /**
     * 父类的onClose
     *
     * @var callable|null
     */
    protected $_parentOnClose = null;

    /**
     * 父类的onMessage
     *
     * @var callable|null
     */
    protected $_parentOnMessage = null;

    /**
     * 获取连接的协程计数
     *
     * @return int[]|int
     */
    public static function getConnectionCoroutineCount(?string $connectionId = null): array|int
    {
        return $connectionId === null
            ? static::$_connectionCoroutineCount
            : (static::$_connectionCoroutineCount[$connectionId] ?? 0);
    }

    /**
     * @link parent::$onConnect
     * @return null|callable
     */
    public function getParentOnConnect(): ?callable
    {
        return $this->_parentOnConnect;
    }

    /**
     * @link parent::$onClose
     * @return null|callable
     */
    public function getParentOnClose(): ?callable
    {
        return $this->_parentOnClose;
    }

    /**
     * @link parent::$onMessage
     * @return null|callable
     */
    public function getParentOnMessage(): ?callable
    {
        return $this->_parentOnMessage;
    }

    /**
     * 设置连接创建/关闭协程化
     *
     * @param bool $connectionCoroutine
     */
    public function setConnectionCoroutine(bool $connectionCoroutine): void
    {
        $this->_connectionCoroutine = $connectionCoroutine;
    }

    /**
     * 设置连接on message是否等待
     *
     * @param bool $connectionOnMessageWait
     */
    public function setConnectionOnMessageWait(bool $connectionOnMessageWait): void
    {
        $this->_connectionOnMessageWait = $connectionOnMessageWait;
    }

    /**
     * 初始化服务
     *
     * @return void
     */
    public function __init__serverMethods(): void
    {
        // 确保协程化开关只被调用一次
        $connectionCoroutine = $this->_connectionCoroutine;
        // 代理onConnect
        if ($this->onConnect) {
            $this->_parentOnConnect = $this->onConnect;
            $this->onConnect = function (ConnectionInterface $connection) use ($connectionCoroutine) {
                // 协程化连接创建
                if ($connectionCoroutine) {
                    new Coroutine(function () use ($connection) {
                        call_user_func($this->getParentOnConnect(), $connection);
                    });
                } else {
                    call_user_func($this->getParentOnConnect(), $connection);
                }
            };
        }
        // 代理onClose
        if ($this->onClose) {
            $this->_parentOnClose = $this->onClose;
            $this->onClose = function (ConnectionInterface $connection) use ($connectionCoroutine) {
                // 协程化连接关闭
                if ($connectionCoroutine) {
                    new Coroutine(function () use ($connection) {
                        call_user_func($this->getParentOnClose(), $connection);
                    });
                } else {
                    call_user_func($this->getParentOnClose(), $connection);
                }
            };
        }
        // 代理onMessage
        if ($this->onMessage) {
            $this->_parentOnMessage = $this->onMessage;
            $this->onMessage = function (ConnectionInterface $connection, mixed $data, ...$params) {
                $connectionId = spl_object_hash($connection);
                $res = null;
                $params = func_get_args();
                $waitGroup = new WaitGroup();
                $waitGroup->add();
                // 协程创建
                new Coroutine(function () use (&$res, $waitGroup, $params, $connectionId) {
                    $res = call_user_func($this->getParentOnMessage(), ...$params);
                    self::$_connectionCoroutineCount[$connectionId] --;
                    $waitGroup->done();
                });
                self::$_connectionCoroutineCount[$connectionId] =
                    (isset(self::$_connectionCoroutineCount[$connectionId])
                        ? self::$_connectionCoroutineCount[$connectionId] + 1
                        : 1);
                // 等待
                $waitGroup->wait();
                return $res;
            };
        }
    }

}
