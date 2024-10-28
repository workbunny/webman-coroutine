<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Worker;

use Workbunny\WebmanCoroutine\Exceptions\WorkerException;
use Workbunny\WebmanCoroutine\Factory;
use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup;

use function Workbunny\WebmanCoroutine\wait_for;

use Workerman\Connection\ConnectionInterface;

trait ServerMethods
{
    /**
     * 请求消费者协程数量，0为无限
     *
     * @var int
     */
    public static int $consumerCount = 0;

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
     * 回收连接的协程计数
     *
     * @param string $connectionId
     * @param bool $force
     * @return void
     */
    public static function unsetConnectionCoroutineCount(string $connectionId, bool $force = false): void
    {
        if (!$force and self::getConnectionCoroutineCount($connectionId) > 0) {
            return;
        }
        unset(static::$_connectionCoroutineCount[$connectionId]);
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
     * 初始化服务
     *
     * @return void
     */
    public function __init__serverMethods(): void
    {
        // 确保协程化开关只被调用一次
        $connectionCoroutine = $this->_connectionCoroutine;
        $parentOnWorkerStart = $this->onWorkerStart;
        $this->onWorkerStart = function (...$params) use ($parentOnWorkerStart) {
            if ($parentOnWorkerStart) {
                call_user_func($parentOnWorkerStart, ...$params);
            }
            // 加载环境
            /** @var HandlerInterface $handler */
            $handler = Factory::getCurrentHandler();
            if (!$handler) {
                $className = $this::class;
                throw new WorkerException("Please run Factory::init or set $className::\$EventLoopClass = event_loop(). ");
            }
            $handler::initEnv();
        };
        // 代理onConnect
        if ($this->onConnect) {
            $this->_parentOnConnect = $this->onConnect;
            $this->onConnect = function (...$params) use ($connectionCoroutine) {
                // 协程化连接创建
                if ($connectionCoroutine) {
                    $wattGroup = new WaitGroup();
                    $wattGroup->add();
                    new Coroutine(function () use ($wattGroup, $params) {
                        try {
                            call_user_func($this->getParentOnConnect(), ...$params);
                        } finally {
                            $wattGroup->done();
                        }
                    });
                    $wattGroup->wait();
                } else {
                    call_user_func($this->getParentOnConnect(), ...$params);
                }
                if (is_object($connection = $params[0] ?? null)) {
                    static::$_connectionCoroutineCount[spl_object_hash($connection)] = 0;
                }
            };
        }
        // 代理onClose
        if ($this->onClose) {
            $this->_parentOnClose = $this->onClose;
            $this->onClose = function (...$params) use ($connectionCoroutine) {
                // 协程化连接关闭
                if ($connectionCoroutine) {
                    $wattGroup = new WaitGroup();
                    $wattGroup->add();
                    new Coroutine(function () use ($wattGroup, $params) {
                        try {
                            call_user_func($this->getParentOnClose(), ...$params);
                        } finally {
                            $wattGroup->done();
                        }
                    });
                    $wattGroup->wait();
                } else {
                    call_user_func($this->getParentOnClose(), ...$params);
                }
                if (is_object($connection = $params[0] ?? null)) {
                    static::unsetConnectionCoroutineCount(spl_object_hash($connection), true);
                }
            };
        }
        // 保证只调用一次
        $consumerCount = static::$consumerCount;
        // 代理onMessage
        if ($this->onMessage) {
            $this->_parentOnMessage = $this->onMessage;
            $this->onMessage = function (ConnectionInterface $connection, mixed $data, ...$params) use ($consumerCount) {
                $connectionId = spl_object_hash($connection);
                $res = null;
                $params = func_get_args();
                if ($consumerCount > 0) {
                    // 等待协程消费者回收
                    wait_for(function () use ($connectionId, $consumerCount) {
                        return self::getConnectionCoroutineCount($connectionId) <= $consumerCount;
                    });
                }
                $waitGroup = new WaitGroup();
                // 协程创建
                $waitGroup->add();
                // 计数 ++
                self::$_connectionCoroutineCount[$connectionId] =
                    isset(self::$_connectionCoroutineCount[$connectionId])
                        ? (self::$_connectionCoroutineCount[$connectionId] + 1)
                        : 1;
                new Coroutine(function () use (&$res, $waitGroup, $params, $connectionId) {
                    try {
                        $res = call_user_func($this->getParentOnMessage(), ...$params);
                    } finally {
                        static::$_connectionCoroutineCount[$connectionId]--;
                        static::unsetConnectionCoroutineCount($connectionId);
                        $waitGroup->done();
                    }
                });
                // 等待
                $waitGroup->wait();

                return $res;
            };
        }
    }
}
