<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\KilledException;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\SwooleHandler;

class SwooleHandlerTest extends TestCase
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testKill()
    {
        // mock
        $object = new \stdClass();
        $object->throw = null;
        $object->suspension = 1;

        $suspensionMock = Mockery::mock('alias:\Swoole\Coroutine');
        $suspensionMock->shouldReceive('resume')->twice()->andReturnUsing(function ($id) use ($object) {
            $this->assertEquals($object->suspension, $id);
        });

        $this->assertEquals(0, SwooleHandler::getSuspensionsWeakMap()->count());
        SwooleHandler::setSuspensionsWeakMap($object, $object->suspension, __METHOD__, microtime(true));
        $this->assertEquals(1, SwooleHandler::getSuspensionsWeakMap()->count());

        // object
        foreach (SwooleHandler::getSuspensionsWeakMap() as $object => $info) {
            SwooleHandler::kill($object, __METHOD__, -1);
        }
        $this->assertInstanceOf(KilledException::class, $object->throw);

        // string
        $object->throw = null;

        foreach (SwooleHandler::getSuspensionsWeakMap() as $info) {
            SwooleHandler::kill($info['id'], __METHOD__, -1);
        }
        $this->assertTrue(true);
    }
}
