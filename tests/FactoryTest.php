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
use Workbunny\WebmanCoroutine\Handlers\DefaultHandler;
use Workbunny\WebmanCoroutine\Handlers\RippleHandler;
use Workbunny\WebmanCoroutine\Handlers\SwooleHandler;
use Workbunny\WebmanCoroutine\Handlers\SwooleWorkerman5Handler;
use Workbunny\WebmanCoroutine\Handlers\SwowHandler;
use Workbunny\WebmanCoroutine\Handlers\SwowWorkerman5Handler;
use Workerman\Connection\ConnectionInterface;
use Workerman\Worker;

class FactoryTest extends TestCase
{
    protected function setUp(): void
    {
        // 重置静态属性
        $reflection = new ReflectionClass(Factory::class);
        $property = $reflection->getProperty('_handlers');
        $property->setAccessible(true);
        $property->setValue($reflection, [
            Factory::WORKERMAN_SWOW    => SwowWorkerman5Handler::class,
            Factory::WORKBUNNY_SWOW    => SwowHandler::class,
            Factory::WORKERMAN_SWOOLE  => SwooleWorkerman5Handler::class,
            Factory::WORKBUNNY_SWOOLE  => SwooleHandler::class,
            Factory::RIPPLE_FIBER      => RippleHandler::class,
            Factory::WORKERMAN_DEFAULT => DefaultHandler::class,
        ]);
    }

    public function testRegister()
    {
        $result = Factory::register('NewEventLoop', TestHandler::class);
        $this->assertTrue($result);

        $reflection = new ReflectionClass(Factory::class);
        $property = $reflection->getProperty('_handlers');
        $property->setAccessible(true);
        $handlers = $property->getValue();
        $this->assertEquals(TestHandler::class, $handlers['NewEventLoop']);
    }

    public function testRegisterExistingHandler()
    {
        Factory::register('ExistingEventLoop', TestHandler::class);
        $result = Factory::register('ExistingEventLoop', TestHandler::class);
        $this->assertNull($result);
    }

    public function testUnregister()
    {
        Factory::register('EventLoopToRemove', TestHandler::class);
        $result = Factory::unregister('EventLoopToRemove');
        $this->assertTrue($result);

        $reflection = new ReflectionClass(Factory::class);
        $property = $reflection->getProperty('_handlers');
        $property->setAccessible(true);
        $handlers = $property->getValue();
        $this->assertArrayNotHasKey('EventLoopToRemove', $handlers);
    }

    public function testRun()
    {
        $app = $this->createMock(CoroutineServerInterface::class);
        $connection = $this->createMock(ConnectionInterface::class);
        $request = $this->createMock(Request::class);

        Factory::register('NewEventLoop', TestHandler::class);
        Factory::run($app, $connection, $request, 'NewEventLoop');

        $result = Factory::run($app, $connection, $request);
        $this->assertEquals('response', $result);
    }

    public function testStart()
    {
        $app = $this->createMock(CoroutineWorkerInterface::class);
        $worker = $this->createMock(Worker::class);

        Factory::register('NewEventLoop', TestHandler::class);
        Factory::start($app, $worker, 'NewEventLoop');

        $result = Factory::start($app, $worker);
        $this->assertEquals('response', $result);
    }
}
