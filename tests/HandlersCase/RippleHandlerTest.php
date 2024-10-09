<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Handlers\RippleHandler;

class RippleHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testIsAvailable()
    {
        $rippleHandlerMock = Mockery::mock(RippleHandler::class . '[_getWorkerVersion]');
        $rippleHandlerMock->shouldAllowMockingProtectedMethods();
        $rippleHandlerMock->shouldReceive('_getWorkerVersion')->andReturn('5.0.0');
        $this->assertFalse($rippleHandlerMock::isAvailable());
        // todo 可用的情况
    }

    public function testInitEnv()
    {
        RippleHandler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        $rippleHandlerMock = Mockery::mock(RippleHandler::class . '[_sleep]');
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

        $return = false;
        $rippleHandlerMock::waitFor(function () use (&$return) {
            return false;
        }, 1);
        $this->assertFalse($return);
    }
}
