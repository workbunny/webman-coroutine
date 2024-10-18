<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Worker;

use Workbunny\Tests\TestCase;

use function Workbunny\WebmanCoroutine\event_loop;

use Workbunny\WebmanCoroutine\Exceptions\WorkerException;
use Workbunny\WebmanCoroutine\Factory;
use Workbunny\WebmanCoroutine\Utils\Worker\AbstractWorker;
use Workbunny\WebmanCoroutine\Utils\Worker\Worker;

/**
 * @runTestsInSeparateProcesses
 */
class WorkerTest extends TestCase
{
    public function testWorkerUseFuncInit()
    {
        $worker = new Worker();
        $worker::$eventLoopClass = event_loop(Factory::WORKERMAN_DEFAULT);
        $worker->onWorkerStart = $onWorkerStart = function () {
        };
        $worker->onWorkerStop = $onWorkerStop = function () {
        };

        $this->assertNull($worker->getParentOnWorkerStart());
        $this->assertNull($worker->getParentOnWorkerStop());

        // init
        $reflection = new \ReflectionClass(AbstractWorker::class);
        $init = $reflection->getMethod('initWorkers');
        $init->invoke(null);
        // onWorkerStart
        $start = $reflection->getProperty('onWorkerStart');
        call_user_func($start->getValue($worker), $worker);

        $this->assertEquals($onWorkerStart, $worker->getParentOnWorkerStart());
        $this->assertEquals($onWorkerStop, $worker->getParentOnWorkerStop());
    }

    public function testWorkerUseFactoryInit()
    {
        Factory::init(Factory::WORKERMAN_DEFAULT);
        $worker = new Worker();
        $worker->onWorkerStart = $onWorkerStart = function ($worker) {
            echo "testWorkerUseFactoryInit->onWorkerStart\n";
        };
        $worker->onWorkerStop = $onWorkerStop = function ($worker) {
            echo "testWorkerUseFactoryInit->onWorkerStop\n";
        };

        $this->assertNull($worker->getParentOnWorkerStart());
        $this->assertNull($worker->getParentOnWorkerStop());

        // init
        $reflection = new \ReflectionClass(AbstractWorker::class);
        $init = $reflection->getMethod('initWorkers');
        $init->invoke(null);

        $this->assertEquals($onWorkerStart, $worker->getParentOnWorkerStart());
        $this->assertEquals($onWorkerStop, $worker->getParentOnWorkerStop());

        $this->expectOutputString(
            "testWorkerUseFactoryInit->onWorkerStart\n"
            . "testWorkerUseFactoryInit->onWorkerStop\n"
        );
        call_user_func($worker->onWorkerStart, $worker);
        call_user_func($worker->onWorkerStop, $worker);
    }

    public function testWorkerException()
    {
        $worker = new Worker();
        //        $worker::$eventLoopClass = event_loop();
        $worker->onWorkerStart = function () {
        };
        $worker->onWorkerStop = function () {
        };

        $this->assertNull($worker->getParentOnWorkerStart());
        $this->assertNull($worker->getParentOnWorkerStop());

        // init
        $reflection = new \ReflectionClass(AbstractWorker::class);
        $init = $reflection->getMethod('initWorkers');
        $init->invoke(null);

        $className = $worker::class;
        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage("Please run Factory::init or set $className::\$EventLoopClass = event_loop().");
        // onWorkerStart
        $start = $reflection->getProperty('onWorkerStart');
        call_user_func($start->getValue($worker), $worker);
    }
}
