<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Worker;

use Mockery;
use Workbunny\Tests\TestCase;
use Workerman\Connection\ConnectionInterface;
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
        $worker->onConnect = $onConnect = function () {
            echo "testServerUseFuncInit->onConnect\n";
        };
        $worker->onClose = $onClose = function () {
            echo "testServerUseFuncInit->onClose\n";

        };
        $worker->onMessage = $onMessage = function ($connection, $data) {
            echo "testServerUseFuncInit->onMessage\n";
        };

        $worker::$consumerCount = 1;

        $this->assertNull($worker->getParentOnConnect());
        $this->assertNull($worker->getParentOnClose());
        $this->assertNull($worker->getParentOnMessage());

        $reflectionMethod = new \ReflectionMethod(AbstractWorker::class, 'initWorkers');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke(null);

        $this->assertEquals($onMessage, $worker->getParentOnMessage());
        $this->assertEquals($onConnect, $worker->getParentOnConnect());
        $this->assertEquals($onClose, $worker->getParentOnClose());

        $connection = Mockery::mock(ConnectionInterface::class);

        $this->expectOutputString(
            "testServerUseFuncInit->onConnect\n"
            . "testServerUseFuncInit->onMessage\n"
            . "testServerUseFuncInit->onClose\n"
        );
        call_user_func($worker->onConnect, $connection);
        $this->assertEquals(0, $worker::getConnectionCoroutineCount($id = spl_object_hash($connection)));
        $this->assertTrue(isset($worker::getConnectionCoroutineCount()[$id]));

        call_user_func($worker->onMessage, $connection, 'aaa');
        call_user_func($worker->onClose, $connection);
    }

    public function testServerUseFactoryInit()
    {
        Factory::init(Factory::WORKERMAN_DEFAULT);
        $worker = new Server();
        $worker->onConnect = $onConnect = function () {
            echo "testServerUseFactoryInit->onConnect\n";
        };
        $worker->onClose = $onClose = function () {
            echo "testServerUseFactoryInit->onClose\n";

        };
        $worker->onMessage = $onMessage = function ($connection, $data) {
            echo "testServerUseFactoryInit->onMessage\n";
        };

        $this->assertNull($worker->getParentOnConnect());
        $this->assertNull($worker->getParentOnClose());
        $this->assertNull($worker->getParentOnMessage());

        $reflectionMethod = new \ReflectionMethod(AbstractWorker::class, 'initWorkers');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke(null);

        $this->assertEquals($onMessage, $worker->getParentOnMessage());
        $this->assertEquals($onConnect, $worker->getParentOnConnect());
        $this->assertEquals($onClose, $worker->getParentOnClose());

        $connection = Mockery::mock(ConnectionInterface::class);

        $this->expectOutputString(
            "testServerUseFactoryInit->onConnect\n"
            . "testServerUseFactoryInit->onMessage\n"
            . "testServerUseFactoryInit->onClose\n"
        );
        call_user_func($worker->onConnect, $connection);
        $this->assertEquals(0, $worker::getConnectionCoroutineCount($id = spl_object_hash($connection)));
        $this->assertTrue(isset($worker::getConnectionCoroutineCount()[$id]));

        call_user_func($worker->onMessage, $connection, 'aaa');
        call_user_func($worker->onClose, $connection);
    }

    public function testServerSetConnectionCoroutine()
    {
        Factory::init(Factory::WORKERMAN_DEFAULT);
        $worker = new Server();
        $worker->setConnectionCoroutine(true);
        $worker->onConnect = $onConnect = function () {
            echo "testServerSetConnectionCoroutine->onConnect\n";
        };
        $worker->onClose = $onClose = function () {
            echo "testServerSetConnectionCoroutine->onClose\n";

        };
        $worker->onMessage = $onMessage = function ($connection, $data) {
            echo "testServerSetConnectionCoroutine->onMessage\n";
        };

        $this->assertNull($worker->getParentOnConnect());
        $this->assertNull($worker->getParentOnClose());
        $this->assertNull($worker->getParentOnMessage());

        $reflectionMethod = new \ReflectionMethod(AbstractWorker::class, 'initWorkers');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke(null);

        $this->assertEquals($onMessage, $worker->getParentOnMessage());
        $this->assertEquals($onConnect, $worker->getParentOnConnect());
        $this->assertEquals($onClose, $worker->getParentOnClose());

        $connection = Mockery::mock(ConnectionInterface::class);

        $this->expectOutputString(
            "testServerSetConnectionCoroutine->onConnect\n"
            . "testServerSetConnectionCoroutine->onMessage\n"
            . "testServerSetConnectionCoroutine->onClose\n"
        );
        call_user_func($worker->onConnect, $connection);
        $this->assertEquals(0, $worker::getConnectionCoroutineCount($id = spl_object_hash($connection)));
        $this->assertTrue(isset($worker::getConnectionCoroutineCount()[$id]));

        call_user_func($worker->onMessage, $connection, 'aaa');
        call_user_func($worker->onClose, $connection);
    }

    public function testServerUnsetConnectionCoroutineByForce()
    {
        Factory::init(Factory::WORKERMAN_DEFAULT);
        $worker = new Server();
        $worker->setConnectionCoroutine(true);
        $connection = Mockery::mock(ConnectionInterface::class);
        $id = spl_object_hash($connection);

        $reflectionClass = new \ReflectionClass($worker);
        $property = $reflectionClass->getProperty('_connectionCoroutineCount');
        $property->setAccessible(true);
        // mock 当前存在一个消费者
        $property->setValue(null, [$id => 1]);

        $this->assertEquals(1, $worker::getConnectionCoroutineCount($id));
        $this->assertTrue(isset($worker::getConnectionCoroutineCount()[$id]));

        $worker::unsetConnectionCoroutineCount($id);

        $this->assertTrue(true);
    }

    public function testServerException()
    {
        $worker = new Server();
        //        $worker::$eventLoopClass = event_loop();
        $worker->onConnect = function () {
        };
        $worker->onClose = function () {
        };
        $worker->onMessage = function () {
        };

        $this->expectException(WorkerException::class);
        $this->assertNull($worker->getParentOnConnect());
        $this->assertNull($worker->getParentOnClose());
        $this->assertNull($worker->getParentOnMessage());

        $reflectionMethod = new \ReflectionMethod(AbstractWorker::class, 'initWorkers');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke(null);
    }
}
