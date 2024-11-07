<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\RevoltHandler;

class RevoltHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testIsAvailable()
    {
        RevoltHandler::isAvailable();
        $this->assertTrue(true);
    }

    public function testInitEnv()
    {
        RevoltHandler::initEnv();
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testWaitFor()
    {
        $suspensionMock = Mockery::mock('alias:\Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:\Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('defer')->andReturnUsing(function ($closure) {
            $closure();
        });
        $eventLoopMock->shouldReceive('delay')->andReturnUsing(function ($timeout, $closure) {
            $closure();
        });

        // success
        $return = false;
        RevoltHandler::waitFor(function () use (&$return) {
            return ($return = true);
        });
        $this->assertTrue($return);

        // success with sleep
        $return = false;
        RevoltHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);

        // success with event
        $return = false;
        RevoltHandler::waitFor(function () use (&$return) {
            sleep(1);

            $return = true;
            RevoltHandler::wakeup(__METHOD__);

            return $return;
        }, event: __METHOD__);
        $this->assertTrue($return);

        // timeout in loop
        $this->expectException(TimeoutException::class);
        RevoltHandler::waitFor(function () {
            return false;
        }, 1);

        // timeout not loop
        $this->expectException(TimeoutException::class);
        // 模拟超时
        RevoltHandler::waitFor(function () {
            sleep(2);

            return false;
        }, 0.1);
    }

    /**
     * @return void
     */
    public function testSleep()
    {
        $suspensionMock = Mockery::mock('alias:\Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:\Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('defer')->andReturnUsing(function ($closure) {
            $closure();
        });
        $eventLoopMock->shouldReceive('delay')->andReturnUsing(function ($timeout, $closure) {
            $closure();
        });

        RevoltHandler::sleep();
        $this->assertTrue(true);

        RevoltHandler::sleep(0.001);
        $this->assertTrue(true);

        RevoltHandler::sleep(0.0009);
        $this->assertTrue(true);

        RevoltHandler::sleep(event: __METHOD__);
        $this->assertTrue(true);

        RevoltHandler::sleep(-1, event: __METHOD__);
        $this->assertTrue(true);
    }

    public function testWakeup()
    {
        RevoltHandler::wakeup(__METHOD__);
        $this->assertTrue(true);

        $suspensionMock = Mockery::mock('alias:\Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $reflection = new \ReflectionClass(RevoltHandler::class);
        $property = $reflection->getProperty('_suspensions');
        $property->setAccessible(true);
        $property->setValue(null, [__METHOD__ => $suspensionMock]);

        RevoltHandler::wakeup(__METHOD__);
        $this->assertTrue(true);
    }
}
