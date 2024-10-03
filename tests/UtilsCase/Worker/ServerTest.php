<?php

declare(strict_types=1);

namespace UtilsCase\Worker;

use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\WorkerException;
use Workbunny\WebmanCoroutine\Factory;
use Workbunny\WebmanCoroutine\Utils\Worker\AbstractWorker;
use Workbunny\WebmanCoroutine\Utils\Worker\Server;
use function Workbunny\WebmanCoroutine\event_loop;

/**
 * @runTestsInSeparateProcesses
 */
class ServerTest extends TestCase
{
    public function testServerUseFuncInit()
    {
        $worker = new Server();
        $worker::$eventLoopClass = event_loop(Factory::WORKERMAN_DEFAULT);
        $worker->onConnect = $onConnect = function () {};
        $worker->onClose = $onClose = function () {};
        $worker->onMessage = $onMessage = function () {};

        $this->assertNull($worker->getParentOnConnect());
        $this->assertNull($worker->getParentOnClose());
        $this->assertNull($worker->getParentOnMessage());

        $reflectionMethod = new \ReflectionMethod(AbstractWorker::class, 'initWorkers');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke(null);

        $this->assertEquals($onMessage, $worker->getParentOnMessage());
        $this->assertEquals($onConnect, $worker->getParentOnConnect());
        $this->assertEquals($onClose, $worker->getParentOnClose());
    }

    public function testServerUseFactoryInit()
    {
        Factory::init(Factory::WORKERMAN_DEFAULT);
        $worker = new Server();
        $worker->onConnect = $onConnect = function () {};
        $worker->onClose = $onClose = function () {};
        $worker->onMessage = $onMessage = function () {};

        $this->assertNull($worker->getParentOnConnect());
        $this->assertNull($worker->getParentOnClose());
        $this->assertNull($worker->getParentOnMessage());

        $reflectionMethod = new \ReflectionMethod(AbstractWorker::class, 'initWorkers');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke(null);

        $this->assertEquals($onMessage, $worker->getParentOnMessage());
        $this->assertEquals($onConnect, $worker->getParentOnConnect());
        $this->assertEquals($onClose, $worker->getParentOnClose());
    }

    public function testServerException()
    {
        $worker = new Server();
//        $worker::$eventLoopClass = event_loop();
        $worker->onConnect = function () {};
        $worker->onClose  = function () {};
        $worker->onMessage = function () {};

        $this->expectException(WorkerException::class);
        $this->assertNull($worker->getParentOnConnect());
        $this->assertNull($worker->getParentOnClose());
        $this->assertNull($worker->getParentOnMessage());

        $reflectionMethod = new \ReflectionMethod(AbstractWorker::class, 'initWorkers');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke(null);
    }


}
