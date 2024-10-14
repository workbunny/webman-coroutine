<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\RippleWorkerman5Handler;

class RippleWorkerman5HandlerTest extends RippleHandlerTest
{
    public function testIsAvailable()
    {
        $rippleHandlerMock = Mockery::mock(RippleWorkerman5Handler::class . '[_getWorkerVersion]');
        $rippleHandlerMock->shouldAllowMockingProtectedMethods();
        $rippleHandlerMock->shouldReceive('_getWorkerVersion')->andReturn('4.0.0');
        $this->assertFalse($rippleHandlerMock::isAvailable());
        // todo 可用的情况
    }

    public function testInitEnv()
    {
        RippleWorkerman5Handler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        $rippleHandlerMock = Mockery::mock(RippleWorkerman5Handler::class . '[_sleep]');
        $rippleHandlerMock->shouldAllowMockingProtectedMethods();
        $rippleHandlerMock->shouldReceive('_sleep')->andReturnNull();

        $return = false;
        $rippleHandlerMock::waitFor(function () use (&$return) {
            return $return = true;
        });
        $this->assertTrue($return);

        $return = false;
        $rippleHandlerMock::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);
        // 模拟超时
        $this->expectException(TimeoutException::class);
        $rippleHandlerMock::waitFor(function () use (&$return) {
            return false;
        }, 1);
        $this->assertFalse($return);
    }
}
