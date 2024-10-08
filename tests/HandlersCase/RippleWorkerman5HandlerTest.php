<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use Workbunny\WebmanCoroutine\Handlers\RippleHandler;

class RippleWorkerman5HandlerTest extends RippleHandlerTest
{
    public function testIsAvailable()
    {
        $rippleHandlerMock = Mockery::mock(RippleHandler::class . '[_getWorkerVersion]');
        $rippleHandlerMock->shouldAllowMockingProtectedMethods();
        $rippleHandlerMock->shouldReceive('_getWorkerVersion')->andReturn('4.0.0');
        $this->assertFalse($rippleHandlerMock::isAvailable());
        // todo 可用的情况
    }
}
