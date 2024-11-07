<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\SwowHandler;

class SwowHandlerTest extends TestCase
{
    public function testIsAvailable()
    {
        SwowHandler::isAvailable();
        $this->assertTrue(true);
    }

    public function testInitEnv()
    {
        SwowHandler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        $mock = Mockery::mock('alias:\Swow\Coroutine');
        $mock->shouldReceive('getCurrent')->andReturn($mock);
        $mock->shouldReceive('isAvailable')->andReturn(true);
        $mock->shouldReceive('resume')->andReturnNull();
        $mock->shouldReceive('yield')->andReturnNull();
        $mock->shouldReceive('run')->andReturnUsing(function ($closure) {
            $closure();
        });

        // success
        $return = false;
        SwowHandler::waitFor(function () use (&$return) {
            return ($return = true);
        });
        $this->assertTrue($return);

        // success with sleep
        $return = false;
        SwowHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);

        // success with event
        $return = false;
        SwowHandler::waitFor(function () use (&$return) {
            sleep(1);

            $return = true;
            SwowHandler::wakeup(__METHOD__);
            return $return;
        }, event: __METHOD__);
        $this->assertTrue($return);

        // timeout in loop
        $this->expectException(TimeoutException::class);
        SwowHandler::waitFor(function () {

            return false;
        }, 1);

        // timeout not loop
        $this->expectException(TimeoutException::class);
        // 模拟超时
        SwowHandler::waitFor(function () {
            sleep(2);

            return false;
        }, 0.1);
    }

    /**
     * @return void
     */
    public function testSleep()
    {
        $mock = Mockery::mock('alias:\Swow\Coroutine');
        $mock->shouldReceive('getCurrent')->andReturn($mock);
        $mock->shouldReceive('isAvailable')->andReturn(true);
        $mock->shouldReceive('resume')->andReturnNull();
        $mock->shouldReceive('yield')->andReturnNull();
        $mock->shouldReceive('run')->andReturnUsing(function ($closure) {
            $closure();
        });

        SwowHandler::sleep();
        $this->assertTrue(true);

        SwowHandler::sleep(0.001);
        $this->assertTrue(true);

        SwowHandler::sleep(0.0009);
        $this->assertTrue(true);

        SwowHandler::sleep(event: __METHOD__);
        $this->assertTrue(true);

        SwowHandler::sleep(-1, event: __METHOD__);
        $this->assertTrue(true);
    }

    public function testWakeup()
    {
        SwowHandler::wakeup(__METHOD__);
        $this->assertTrue(true);

        $mock = Mockery::mock('alias:\Swow\Coroutine');
        $mock->shouldReceive('isAvailable')->andReturn(true);
        $mock->shouldReceive('resume')->andReturnNull();
        $reflection = new \ReflectionClass(SwowHandler::class);
        $property = $reflection->getProperty('_suspensions');
        $property->setAccessible(true);
        $property->setValue(null, [__METHOD__ => $mock]);

        SwowHandler::wakeup(__METHOD__);
        $this->assertTrue(true);
    }
}
