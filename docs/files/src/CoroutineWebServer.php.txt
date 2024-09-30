<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

use Webman\App;
use Webman\Http\Request;
use Workbunny\WebmanCoroutine\Utils\Channel\Channel;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup;
use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

/**
 *  协程化web服务进程
 */
class CoroutineWebServer extends App
{

    /**
     * @var Channel[]
     */
    protected array $_connectionChannels = [];

    /**
     * @var Coroutine|null
     */
    protected ?Coroutine $_coroutine = null;

    /** @inheritdoc  */
    public function onWorkerStart($worker)
    {
        if (!\config('plugin.workbunny.webman-coroutine.app.enable', false)) {
            return;
        }
        $this->_coroutine = new Coroutine();
        parent::onWorkerStart($worker);
    }

    /**
     * 停止服务
     *
     *  - 不用返回值和参数标定是为了兼容
     *
     * @param Worker|mixed $worker
     * @return void
     */
    public function onWorkerStop($worker, ...$params)
    {
        if (is_callable($call = [parent::class, 'onWorkerStop'])) {
            call_user_func($call, $worker, ...$params);
        }
    }

    /**
     * 连接建立
     *
     *  - 不用返回值和参数标定是为了兼容
     *
     * @param ConnectionInterface $connection
     * @param mixed ...$params
     * @return void
     */
    public function onConnect($connection, ...$params): void
    {
        if (!is_object($connection)) {
            return;
        }
        if (is_callable($call = [parent::class, 'onConnect'])) {
            call_user_func($call, $connection, ...$params);
        }
        if (!$this->_connectionChannels[$id = spl_object_hash($connection)] ?? null) {
            $this->_connectionChannels[$id] = new Channel(\config('plugin.workbunny.webman-coroutine.app.channel_size', 1));
        }
    }

    /**
     * 连接关闭
     *
     *  - 不用返回值和参数标定是为了兼容
     *
     * @param ConnectionInterface|mixed $connection
     * @param ...$params
     * @return void
     */
    public function onClose($connection, ...$params)
    {
        if (!is_object($connection)) {
            return;
        }
        if (is_callable($call = [parent::class, 'onClose'])) {
            call_user_func($call, $connection, ...$params);
        }
        unset($this->_connectionChannels[spl_object_hash($connection)]);
    }

    /**
     * @link parent::onMessage()
     * @param ConnectionInterface|mixed $connection
     * @param Request|mixed $request
     * @param ...$params
     * @return null
     * @link parent::onMessage()
     */
    public function onMessage($connection, $request, ...$params)
    {
        if (!is_object($connection)) {
            return null;
        }
        // 为每一个连接创建一个通道
        $channel = $this->_connectionChannels[spl_object_hash($connection)];
        // 请求生产
        $channel->push(func_get_args());
        $waitGroup = new WaitGroup();
        // 根据request consumer数量创建协程
        $consumerCount = config('plugin.workbunny.webman-coroutine.app.consumer_count', 1);
        foreach (range(1, $consumerCount) as $ignored) {
            $waitGroup->add();
            // 请求消费协程
            $this->_coroutine->create(function () use ($channel, $waitGroup) {
                while (true) {
                    // 通道为空或者关闭时退出协程
                    if (
                        $channel->isEmpty() or
                        !$data = $channel->pop()
                    ) {
                        break;
                    }
                    parent::onMessage(...$data);
                }
                $waitGroup->done();
            });
        }
        $waitGroup->wait();
        return null;
    }
}
