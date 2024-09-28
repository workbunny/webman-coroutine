<?php

declare(strict_types=1);

namespace Workbunny\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Webman\Http\Request;
use Workbunny\Tests\mock\TestHandler;
use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workbunny\WebmanCoroutine\Factory;
use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

class FactoryTest extends TestCase
{
    public function testRegister()
    {
        $result = Factory::register(__METHOD__, TestHandler::class);
        $this->assertTrue($result);

        $reflection = new ReflectionClass(Factory::class);
        $property = $reflection->getProperty('_handlers');
        $property->setAccessible(true);
        $handlers = $property->getValue();
        $this->assertEquals(TestHandler::class, $handlers[__METHOD__] ?? null);

        Factory::unregister(__METHOD__);
    }

    public function testRegisterExistingHandler()
    {
        Factory::register(__METHOD__, TestHandler::class);
        $result = Factory::register(__METHOD__, TestHandler::class);
        $this->assertNull($result);

        Factory::unregister(__METHOD__);
    }

    public function testUnregister()
    {
        Factory::register(__METHOD__, TestHandler::class);
        $result = Factory::unregister(__METHOD__);
        $this->assertTrue($result);

        $reflection = new ReflectionClass(Factory::class);
        $property = $reflection->getProperty('_handlers');
        $property->setAccessible(true);
        $handlers = $property->getValue();
        $this->assertArrayNotHasKey(__METHOD__, $handlers);

        Factory::unregister(__METHOD__);
    }

    public function testRun()
    {
        $app = $this->createMock(CoroutineServerInterface::class);
        $connection = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(Request::class);

        Factory::register(__METHOD__, TestHandler::class);
        Factory::run($app, $connection, $request, __METHOD__);

        $result = Factory::run($app, $connection, $request);
        $this->assertEquals('response', $result);

        Factory::unregister(__METHOD__);
    }

    public function testStart()
    {
        $app = $this->createMock(CoroutineWorkerInterface::class);
        $worker = $this->createMock(Worker::class);

        Factory::register(__METHOD__, TestHandler::class);
        Factory::start($app, $worker, __METHOD__);

        $result = Factory::start($app, $worker);
        $this->assertEquals('response', $result);

        Factory::unregister(__METHOD__);
    }
}
