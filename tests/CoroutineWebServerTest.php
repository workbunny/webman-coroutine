<?php

declare(strict_types=1);

namespace Workbunny\Tests;

use ReflectionClass;
use Mockery;
use ReflectionMethod;
use support\Request;
use Workbunny\WebmanCoroutine\CoroutineWebServer;
use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

class CoroutineWebServerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../vendor/workerman/webman-framework/src/support/helpers.php';
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
        $property->setValue(null, [$connectionId => 5]);

        $this->assertEquals(5, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
        $this->assertEquals([$connectionId => 5], CoroutineWebServer::getConnectionCoroutineCount());
    }

    public function testUnsetConnectionCoroutineCount()
    {
        $connectionId = __METHOD__;
        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_connectionCoroutineCount');
        $property->setAccessible(true);
        $property->setValue(null, [$connectionId => 0]);

        CoroutineWebServer::unsetConnectionCoroutineCount($connectionId);
        $this->assertEquals([], CoroutineWebServer::getConnectionCoroutineCount());
    }

    public function testOnConnect()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connectionId = spl_object_hash($connection);

        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onConnect');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($server, $connection);

        $this->assertEquals(0, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
    }

    public function testOnClose()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connectionId = spl_object_hash($connection);

        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_connectionCoroutineCount');
        $property->setAccessible(true);
        $property->setValue(null, [$connectionId => 0]);

        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onClose');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($server, $connection);

        $this->assertEquals([], CoroutineWebServer::getConnectionCoroutineCount());
    }

    public function testOnMessage()
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(Request::class);
        $connectionId = spl_object_hash($connection);

        $reflectionClass = new ReflectionClass(CoroutineWebServer::class);
        $property = $reflectionClass->getProperty('_connectionCoroutineCount');
        $property->setAccessible(true);
        $property->setValue(null, [$connectionId => 0]);

        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onMessage');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invoke($server, $connection, $request);

        $this->assertNull($result);
        $this->assertEquals(0, CoroutineWebServer::getConnectionCoroutineCount($connectionId));
    }

    public function testOnWorkerStart()
    {
        $worker = $this->createMock(Worker::class);

        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onWorkerStart');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($server, $worker);

        $this->assertTrue(true); // 确保方法被调用
    }

    public function testOnWorkerStop()
    {
        $worker = $this->createMock(Worker::class);

        $server = Mockery::mock(CoroutineWebServer::class)->makePartial();
        $reflectionMethod = new ReflectionMethod(CoroutineWebServer::class, 'onWorkerStop');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($server, $worker);

        $this->assertTrue(true); // 确保方法被调用
    }
}
