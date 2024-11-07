<?php

declare(strict_types=1);

namespace Workbunny\Tests;

use Mockery;
use ReflectionClass;
use ReflectionMethod;
use support\Request;
use Workbunny\WebmanCoroutine\CoroutineWebServer;

use function Workbunny\WebmanCoroutine\event_loop;

use Workbunny\WebmanCoroutine\Exceptions\WorkerException;
use Workerman\Connection\ConnectionInterface;
use Workerman\Connection\TcpConnection;
use Workerman\Connection\UdpConnection;
use Workerman\Worker;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CoroutineWebServerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/mock/helpers.php';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testGetConnectionCoroutineCount()
    {
        $connectionId = __METHOD__;
        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_connectionCoroutineCount');
        $property->setAccessible(true);
        $property->setValue($reflectionClass, [$connectionId => 5]);

        $this->assertEquals(5, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
        $this->assertEquals([$connectionId => 5], CoroutineWebServer::getConnectionCoroutineCount());
    }

    public function testUnsetConnectionCoroutineCount()
    {
        $connectionId = __METHOD__;
        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_connectionCoroutineCount');
        $property->setAccessible(true);

        $property->setValue($reflectionClass, [$connectionId => 0]);
        CoroutineWebServer::unsetConnectionCoroutineCount($connectionId);
        $this->assertEquals(0, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
        $this->assertEquals([], CoroutineWebServer::getConnectionCoroutineCount());

        $property->setValue(null, [$connectionId => 1]);
        CoroutineWebServer::unsetConnectionCoroutineCount($connectionId);
        $this->assertEquals(1, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
        $this->assertEquals([$connectionId => 1], CoroutineWebServer::getConnectionCoroutineCount());

        $property->setValue(null, [$connectionId => 1]);
        CoroutineWebServer::unsetConnectionCoroutineCount($connectionId, true);
        $this->assertEquals(0, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
        $this->assertEquals([], CoroutineWebServer::getConnectionCoroutineCount());
    }

    public function testOnWorkerStart()
    {
        $worker = $this->createMock(Worker::class);

        // 模拟测试关闭进程时
        set_config('plugin.workbunny.webman-coroutine.app.enable', false);
        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onWorkerStart');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($server, $worker);
        $this->assertTrue(true); // 确保方法被调用

        $worker = $this->createMock(Worker::class);
        // 模拟测试开启进程时
        set_config('plugin.workbunny.webman-coroutine.app.enable', true);
        $worker::$eventLoopClass = event_loop();
        // mock调用onWorkerStart
        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onWorkerStart');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($server, $worker);
        $this->assertTrue(true);
    }

    public function testOnWorkerStartException()
    {
        $worker = $this->createMock(Worker::class);
        // 模拟测试开启进程时未加载Factory::init()
        set_config('plugin.workbunny.webman-coroutine.app.enable', true);
        $this->expectException(WorkerException::class);
        $class = $worker::class;
        $this->expectExceptionMessage("Please run Factory::init or set $class::\$EventLoopClass = event_loop(). ");
        // mock调用onWorkerStart
        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onWorkerStart');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($server, $worker);
        $this->assertTrue(true);
    }

    public function testOnWorkerStop()
    {
        $worker = Mockery::mock('Workerman\Worker')->makePartial();
        $worker::$eventLoopClass = event_loop();
        $mockClass = new class () {
            public function onWorkerStop(Worker $worker, ...$params)
            {
                TestCase::assertEquals('one', $params[0] ?? null);
                TestCase::assertEquals('two', $params[1] ?? null);
                echo 'parent::onWorkerStop';
            }
        };
        $this->expectOutputString('parent::onWorkerStop');
        // 替换Webman\App类，用于测试
        $res = class_alias(get_class($mockClass), 'Webman\App', false);
        $this->assertTrue($res);
        $logger = Mockery::mock('alias:Monolog\Logger');
        $server = new CoroutineWebServer(Request::class, $logger, '', '');
        $server->onWorkerStop($worker, 'one', 'two');
        $this->assertTrue(true);
    }

    public function testOnWorkerStopWaitForClose()
    {
        $worker = Mockery::mock('Workerman\Worker')->makePartial();
        $worker::$eventLoopClass = event_loop();
        $worker->shouldReceive('safeEcho')->andReturnNull();
        $mockClass = new class () {
            public function onWorkerStop(Worker $worker, ...$params)
            {
                TestCase::assertEquals('one', $params[0] ?? null);
                TestCase::assertEquals('two', $params[1] ?? null);
            }
        };
        // 替换Webman\App类，用于测试
        $res = class_alias(get_class($mockClass), 'Webman\App', false);
        $this->assertTrue($res);
        $logger = Mockery::mock('alias:Monolog\Logger');
        $server = new CoroutineWebServer(Request::class, $logger, '', '');

        set_config('plugin.workbunny.webman-coroutine.app.wait_for_close', -1);
        $server->onWorkerStop($worker, 'one', 'two');
        $this->assertTrue(true);

        set_config('plugin.workbunny.webman-coroutine.app.wait_for_close', 1);
        $server->onWorkerStop($worker, 'one', 'two');
        $this->assertTrue(true);

        set_config('plugin.workbunny.webman-coroutine.app.wait_for_close', 0.1);
        $reflection = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflection->getProperty('_connectionCoroutineCount');
        $property->setAccessible(true);
        $property->setValue($server, ['1' => 1]);
        $server->onWorkerStop($worker, 'one', 'two');
        $this->assertTrue(true);
    }

    public function testOnConnect()
    {
        $connection = Mockery::mock('alias:' . ConnectionInterface::class);
        $connectionId = spl_object_hash($connection);

        $mockClass = new class () {
            public function onConnect(?ConnectionInterface $connection, ...$params)
            {
                TestCase::assertEquals('one', $params[0] ?? null);
                TestCase::assertEquals('two', $params[1] ?? null);
                echo 'parent::onConnect';
            }
        };
        $this->expectOutputString('parent::onConnect');
        // 替换Webman\App类，用于测试
        $res = class_alias(get_class($mockClass), 'Webman\App', false);
        $this->assertTrue($res);
        $logger = Mockery::mock('alias:Monolog\Logger');
        $server = new CoroutineWebServer(Request::class, $logger, '', '');

        // connection object
        $server->onConnect($connection, 'one', 'two');
        $this->assertEquals(0, CoroutineWebServer::getConnectionCoroutineCount($connectionId));

        // 非object
        $server->onConnect('123');
        $this->assertTrue(true);
    }

    public function testOnConnectOnStopSignal()
    {
        $request = $this->createMock(Request::class);
        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();

        // TCP
        $connection = Mockery::mock(TcpConnection::class);
        $connection->shouldReceive('pauseRecv')->once()->andReturnUsing(function () {
            $this->assertTrue(true);
        });
        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_stopSignal');
        $property->setAccessible(true);
        $property->setValue($server, true);
        $reflectionMethod = $reflectionClass->getMethod('onConnect');
        $reflectionMethod->invoke($server, $connection, $request);
        $this->assertTrue(true);

        // TCP
        $connection = Mockery::mock(UdpConnection::class);
        $connection->shouldReceive('close')->once()->andReturnUsing(function () {
            $this->assertTrue(true);
        });
        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_stopSignal');
        $property->setAccessible(true);
        $property->setValue($server, true);
        $reflectionMethod = $reflectionClass->getMethod('onConnect');
        $reflectionMethod->invoke($server, $connection, $request);
        $this->assertTrue(true);
    }

    public function testOnClose()
    {
        $connection = Mockery::mock('alias:' . ConnectionInterface::class);
        $connectionId = spl_object_hash($connection);

        $mockClass = new class () {
            public function onClose(?ConnectionInterface $connection, ...$params)
            {
                TestCase::assertEquals('one', $params[0] ?? null);
                TestCase::assertEquals('two', $params[1] ?? null);
                echo 'parent::onClose';
            }
        };
        $this->expectOutputString('parent::onClose');
        // 替换Webman\App类，用于测试
        $res = class_alias(get_class($mockClass), 'Webman\App', false);
        $this->assertTrue($res);
        $logger = Mockery::mock('alias:Monolog\Logger');
        $server = new CoroutineWebServer(Request::class, $logger, '', '');

        // connection object
        $server->onClose($connection, 'one', 'two');
        $this->assertEquals(0, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
        // 非object
        $server->onClose('123');
        $this->assertTrue(true);
    }

    public function testOnMessage()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(Request::class);
        $connectionId = spl_object_hash($connection);
        set_config('plugin.workbunny.webman-coroutine.app.consumer_count', 1);
        // mock server class
        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_connectionCoroutineCount');
        $property->setAccessible(true);
        // mock server onMessage
        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onMessage');
        $reflectionMethod->setAccessible(true);
        // 测试连接自增1
        $property->setValue($reflectionClass, [$connectionId => 0]);
        $result = $reflectionMethod->invoke($server, $connection, $request);
        $this->assertNull($result);
        $this->assertEquals(0, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
        // 测试连接初始化1
        $property->setValue($reflectionClass, []);
        $reflectionMethod->invoke($server, $connection, $request);
        // 测试非connectionInterface
        $reflectionMethod->invoke($server, 'not connectionInterface', $request);
        $this->assertTrue(true);
    }

    public function testOnMessageOnStopSignal()
    {
        $request = $this->createMock(Request::class);
        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        // mock server class
        $connection = Mockery::mock('alias:' . ConnectionInterface::class);
        $connection->shouldReceive('close')->once()->andReturnUsing(function () {
            $this->assertTrue(true);
        });
        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_stopSignal');
        $property->setAccessible(true);
        $property->setValue($server, true);
        $reflectionMethod = $reflectionClass->getMethod('onMessage');
        $reflectionMethod->invoke($server, $connection, $request);
        $this->assertTrue(true);
    }
}
