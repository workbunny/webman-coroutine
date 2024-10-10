<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use PHPUnit\Framework\TestCase;
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
        $this->markTestSkipped('Skipped. ');
    }

    public function testInitEnv()
    {
        RevoltHandler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        $rippleHandlerMock = Mockery::mock(RevoltHandler::class . '[sleep]');
        $rippleHandlerMock->shouldReceive('sleep')->andReturnNull();

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
