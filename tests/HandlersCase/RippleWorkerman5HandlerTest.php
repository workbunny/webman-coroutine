<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use Workbunny\WebmanCoroutine\Handlers\RippleWorkerman5Handler;

class RippleWorkerman5HandlerTest extends RippleHandlerTest
{
    public function testIsAvailable()
    {
        $rippleHandlerMock = Mockery::mock(RippleWorkerman5Handler::class . '[_getWorkerVersion]');
        $rippleHandlerMock->shouldAllowMockingProtectedMethods();
        $rippleHandlerMock->shouldReceive('_getWorkerVersion')->andReturn('4.0.0');
        $this->assertFalse($rippleHandlerMock::isAvailable());
    }

    public function testInitEnv()
    {
        RippleWorkerman5Handler::initEnv();
        $this->assertTrue(true);
    }
}
