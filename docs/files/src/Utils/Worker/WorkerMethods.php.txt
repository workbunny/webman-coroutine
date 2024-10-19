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

trait WorkerMethods
{
    /**
     * @var callable|null
     */
    protected $_parentOnWorkerStart = null;

    /**
     * @var callable|null
     */
    protected $_parentOnWorkerStop = null;

    /**
     * @return callable|null
     */
    public function getParentOnWorkerStart(): ?callable
    {
        return $this->_parentOnWorkerStart;
    }

    /**
     * @return callable|null
     */
    public function getParentOnWorkerStop(): ?callable
    {
        return $this->_parentOnWorkerStop;
    }

    /**
     * @return void
     */
    public function __init__workerMethods(): void
    {
        // start
        $this->_parentOnWorkerStart = $this->onWorkerStart;
        $this->onWorkerStart = function (\Workerman\Worker $worker) {
            // 加载环境
            /** @var HandlerInterface $handler */
            $handler = Factory::getCurrentHandler();
            if (!$handler) {
                $className = $this::class;
                throw new WorkerException("Please run Factory::init or set $className::\$EventLoopClass = event_loop(). ");
            }
            $handler::initEnv();
            // 执行
            $waitGroup = new WaitGroup();
            $waitGroup->add();
            new Coroutine(function () use ($worker, $waitGroup) {
                try {
                    call_user_func($this->getParentOnWorkerStart(), $worker);
                } finally {
                    $waitGroup->done();
                }
            });
            $waitGroup->wait();
        };
        // stop
        $this->_parentOnWorkerStop = $this->onWorkerStop;
        $this->onWorkerStop = function (\Workerman\Worker $worker) {
            $waitGroup = new WaitGroup();
            $waitGroup->add();
            new Coroutine(function () use ($worker, $waitGroup) {
                try {
                    call_user_func($this->getParentOnWorkerStop(), $worker);
                } finally {
                    $waitGroup->done();
                }
            });
            $waitGroup->wait();
        };
    }
}
