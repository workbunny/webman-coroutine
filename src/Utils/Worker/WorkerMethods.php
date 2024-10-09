<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Worker;

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
            $waitGroup = new WaitGroup();
            $waitGroup->add(1);
            new Coroutine(function () use ($worker, $waitGroup) {
                call_user_func($this->getParentOnWorkerStart(), $worker);
                $waitGroup->done();
            });
            $waitGroup->wait(-1);
        };
        // stop
        $this->_parentOnWorkerStop = $this->onWorkerStop;
        $this->onWorkerStop = function (\Workerman\Worker $worker) {
            $waitGroup = new WaitGroup();
            $waitGroup->add(1);
            new Coroutine(function () use ($worker, $waitGroup) {
                call_user_func($this->getParentOnWorkerStop(), $worker);
                $waitGroup->done();
            });
            $waitGroup->wait(-1);
        };
    }
}
