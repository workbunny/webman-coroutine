<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use PHPUnit\Framework\TestCase;
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
        $this->markTestSkipped('Skipped. ');
    }

    public function testInitEnv()
    {
        RevoltHandler::initEnv();
        $this->assertTrue(true);
    }

    /**
     *
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

        $return = false;
        RevoltHandler::waitFor(function () use (&$return) {
            return $return = true;
        });
        $this->assertTrue($return);

        $return = false;
        RevoltHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);

        // 模拟超时
        $this->expectException(TimeoutException::class);
        $return = false;
        RevoltHandler::waitFor(function () use (&$return) {
            return false;
        }, 1);
        $this->assertFalse($return);
    }
}
