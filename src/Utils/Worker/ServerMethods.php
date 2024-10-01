<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Worker;

use Workbunny\WebmanCoroutine\Utils\Channel\Channel;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup;
use Workerman\Connection\ConnectionInterface;

trait ServerMethods
{
    /**
     * 连接关闭/开启协程化
     *
     * @var bool
     */
    protected bool $_connectionCoroutine = false;

    /**
     * 连接队列
     *
     * @var Channel[]
     */
    protected array $_connectionChannels = [];

    /**
     * 连接队列容量
     *
     * @var int
     */
    protected int $_connectionChannelSize = 1;

    /**
     * 连接队列消费者数量
     *
     * @var int
     */
    protected int $_connectionConsumerCount = 1;

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
     * 获取所有连接队列
     *
     * @return array
     */
    public function getConnectionChannels(): array
    {
        return $this->_connectionChannels;
    }

    /**
     * 获取连接队列容量
     *
     * @return int
     */
    public function getConnectionChannelSize(): int
    {
        return $this->_connectionChannelSize;
    }

    /**
     * 设置连接队列容量
     *
     * @param int $connectionChannelSize
     * @return void
     */
    public function setConnectionChannelSize(int $connectionChannelSize): void
    {
        $this->_connectionChannelSize = $connectionChannelSize;
    }

    /**
     * 获取连接队列消费者数量
     *
     * @return int
     */
    public function getConnectionConsumerCount(): int
    {
        return $this->_connectionConsumerCount;
    }

    /**
     * 设置连接队列消费者数量
     *
     * @param int $connectionConsumerCount
     * @return void
     */
    public function setConnectionConsumerCount(int $connectionConsumerCount): void
    {
        $this->_connectionConsumerCount = $connectionConsumerCount;
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
    protected function __runInit__serverMethods(): void
    {
        // 确保协程化开关只被调用一次
        $connectionCoroutine = $this->_connectionCoroutine;
        // 代理onConnect
        if ($this->onConnect) {
            $this->_parentOnConnect = $this->onConnect;
            $this->onConnect = function (ConnectionInterface $connection) use ($connectionCoroutine) {
                // 协程化连接创建
                if ($connectionCoroutine) {
                    $this->getCoroutine()->create(function () use ($connection) {
                        call_user_func($this->getParentOnConnect(), $connection);
                    });
                } else {
                    call_user_func($this->getParentOnConnect(), $connection);
                }
                // 为每一个连接创建一个通道
                if (!$this->_connectionChannels[$id = spl_object_hash($connection)] ?? null) {
                    $this->_connectionChannels[$id] = new Channel($this->getConnectionChannelSize());
                }
            };
        }
        // 代理onClose
        if ($this->onClose) {
            $this->_parentOnClose = $this->onClose;
            $this->onClose = function (ConnectionInterface $connection) use ($connectionCoroutine) {
                // 协程化连接关闭
                if ($connectionCoroutine) {
                    $this->getCoroutine()->create(function () use ($connection) {
                        call_user_func($this->getParentOnClose(), $connection);
                    });
                } else {
                    call_user_func($this->getParentOnClose(), $connection);
                }
                // 删除通道，析构自动close
                unset($this->_connectionChannels[spl_object_hash($connection)]);
            };
        }
        // 代理onMessage
        if ($this->onMessage) {
            $this->_parentOnMessage = $this->onMessage;
            $this->onMessage = function (ConnectionInterface $connection, mixed $data) {
                // 获取连接通道
                $channel = $this->_connectionChannels[spl_object_hash($connection)];
                // 投递数据
                $channel->push([
                    $connection, $data
                ]);
                $waitGroup = new WaitGroup();
                // 消费者消费
                $count = max(1, $this->getConnectionConsumerCount());
                foreach (range(1, $count) as $ignored) {
                    $waitGroup->add();
                    // 协程创建
                    $this->getCoroutine()->create(function () use ($channel, $waitGroup) {
                        while (true) {
                            // 通道为空或者关闭时退出协程
                            if (
                                $channel->isEmpty() or
                                !$data = $channel->pop()
                            ) {
                                break;
                            }
                            [$connection, $request] = $data;
                            call_user_func($this->getParentOnMessage(), $connection, $request);
                        }
                        $waitGroup->done();
                    });
                }
                // 等待
                $waitGroup->wait(-1);
            };
        }
    }

}
