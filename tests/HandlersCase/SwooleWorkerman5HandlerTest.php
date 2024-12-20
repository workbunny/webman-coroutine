<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\SwooleWorkerman5Handler as SwooleHandler;

class SwooleWorkerman5HandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testIsAvailable()
    {
        SwooleHandler::isAvailable();
        $this->assertTrue(true);
    }

    public function testInitEnv()
    {
        Mockery::mock('alias:Swoole\Runtime')->shouldReceive('enableCoroutine')->andReturnNull();
        SwooleHandler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        $timerMock = Mockery::mock('alias:Swoole\Timer');
        $timerMock->shouldReceive('after')->andReturnUsing(function ($interval, $callback) {
            $callback();
        });
        $timerMock = Mockery::mock('alias:Swoole\Event');
        $timerMock->shouldReceive('defer')->andReturnUsing(function ($callback) {
            $callback();
        });
        $mock = Mockery::mock('alias:\Swoole\Coroutine');
        $mock->shouldReceive('exists')->andReturn(true);
        $mock->shouldReceive('suspend')->andReturnNull();
        $mock->shouldReceive('resume')->andReturnNull();
        $mock->shouldReceive('getCid')->andReturn(1);

        // success
        $return = false;
        SwooleHandler::waitFor(function () use (&$return) {
            return ($return = true);
        });
        $this->assertTrue($return);

        // success with sleep
        $return = false;
        SwooleHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);

        // success with event
        $return = false;
        SwooleHandler::waitFor(function () use (&$return) {
            sleep(1);

            $return = true;
            SwooleHandler::wakeup(__METHOD__);

            return $return;
        }, event: __METHOD__);
        $this->assertTrue($return);

        // timeout in loop
        $this->expectException(TimeoutException::class);
        SwooleHandler::waitFor(function () {
            return false;
        }, 1);

        // timeout not loop
        $this->expectException(TimeoutException::class);
        // 模拟超时
        SwooleHandler::waitFor(function () {
            sleep(2);

            return false;
        }, 0.1);
    }

    /**
     * @return void
     */
    public function testSleep()
    {
        $timerMock = Mockery::mock('alias:Swoole\Timer');
        $timerMock->shouldReceive('after')->andReturnUsing(function ($interval, $callback) {
            $callback();
        });
        $timerMock = Mockery::mock('alias:Swoole\Event');
        $timerMock->shouldReceive('defer')->andReturnUsing(function ($callback) {
            $callback();
        });
        $mock = Mockery::mock('alias:\Swoole\Coroutine');
        $mock->shouldReceive('exists')->andReturn(true);
        $mock->shouldReceive('suspend')->andReturnNull();
        $mock->shouldReceive('resume')->andReturnNull();
        $mock->shouldReceive('getCid')->andReturn(1);

        SwooleHandler::sleep();
        $this->assertTrue(true);

        SwooleHandler::sleep(0.001);
        $this->assertTrue(true);

        SwooleHandler::sleep(0.0009);
        $this->assertTrue(true);

        SwooleHandler::sleep(event: __METHOD__);
        $this->assertTrue(true);

        SwooleHandler::sleep(-1, event: __METHOD__);
        $this->assertTrue(true);
    }

    public function testWakeup()
    {
        SwooleHandler::wakeup(__METHOD__);
        $this->assertTrue(true);

        $mock = Mockery::mock('alias:\Swoole\Coroutine');
        $mock->shouldReceive('exists')->andReturn(true);
        $mock->shouldReceive('resume')->andReturnNull();
        $reflection = new \ReflectionClass(SwooleHandler::class);
        $property = $reflection->getProperty('_suspensions');
        $property->setAccessible(true);
        $property->setValue(null, [__METHOD__ => 1]);

        SwooleHandler::wakeup(__METHOD__);
        $this->assertTrue(true);
    }
}
