<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use Workbunny\WebmanCoroutine\Exceptions\KilledException;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\RippleWorkerman5Handler as RippleHandler;

class RippleWorkerman5HandlerTest extends RippleHandlerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../mock/ripple.php';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testIsAvailable()
    {
        RippleHandler::isAvailable();
        $this->assertTrue(true);
    }

    public function testInitEnv()
    {
        RippleHandler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        // success
        $return = false;
        RippleHandler::waitFor(function () use (&$return) {
            return ($return = true);
        });
        $this->assertTrue($return);

        // success with sleep
        $return = false;
        RippleHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);

        // success with event
        $return = false;
        RippleHandler::waitFor(function () use (&$return) {
            sleep(1);

            $return = true;
            RippleHandler::wakeup(__METHOD__);

            return $return;
        }, event: __METHOD__);
        $this->assertTrue($return);

        // timeout in loop
        $this->expectException(TimeoutException::class);
        RippleHandler::waitFor(function () {
            return false;
        }, 1);
        $this->assertTrue(true);

        // timeout not loop
        $this->expectException(TimeoutException::class);
        // 模拟超时
        RippleHandler::waitFor(function () {
            sleep(2);

            return false;
        }, 0.1);
        $this->assertTrue(true);
    }

    public function testSleep()
    {
        RippleHandler::sleep();
        $this->assertTrue(true);

        RippleHandler::sleep(0.01);
        $this->assertTrue(true);

        RippleHandler::sleep(0.009);
        $this->assertTrue(true);

        RippleHandler::sleep(event: __METHOD__);
        $this->assertTrue(true);

        RippleHandler::sleep(-1, event: __METHOD__);
        $this->assertTrue(true);
    }

    public function testWakeup()
    {
        RippleHandler::wakeup(__METHOD__);
        $this->assertTrue(true);

        $suspensionMock = Mockery::mock('alias:\Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $reflection = new \ReflectionClass(RippleHandler::class);
        $property = $reflection->getProperty('_suspensions');
        $property->setAccessible(true);
        $property->setValue(null, [__METHOD__ => $suspensionMock]);

        RippleHandler::wakeup(__METHOD__);
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
        $suspensionMock = Mockery::mock('alias:\Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('throw')->twice()->andReturnUsing(function ($exception) {
            $this->assertInstanceOf(KilledException::class, $exception);
        });
        $this->assertEquals(0, RippleHandler::getSuspensionsWeakMap()->count());
        RippleHandler::setSuspensionsWeakMap($suspensionMock, spl_object_hash($suspensionMock), __METHOD__, microtime(true));
        $this->assertEquals(1, RippleHandler::getSuspensionsWeakMap()->count());

        // object
        foreach (RippleHandler::getSuspensionsWeakMap() as $object => $info) {
            RippleHandler::kill($object, __METHOD__, -1);
        }
        $this->assertTrue(true);

        // string
        foreach (RippleHandler::getSuspensionsWeakMap() as $info) {
            RippleHandler::kill($info['id'], __METHOD__, -1);
        }
        $this->assertTrue(true);
    }
}
