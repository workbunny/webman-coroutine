<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Mockery;
use PHPUnit\Framework\TestCase;
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
        $this->markTestSkipped('Skipped. ');
    }

    public function testInitEnv()
    {
        Mockery::mock('alias:Swoole\Runtime')->shouldReceive('enableCoroutine')->andReturnNull();
        SwooleHandler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        $return = false;
        SwooleHandler::waitFor(function () use (&$return) {
            return $return = true;
        });
        $this->assertTrue($return);

        $return = false;
        SwooleHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);
        // 模拟超时
        $this->expectException(TimeoutException::class);
        $return = false;
        SwooleHandler::waitFor(function () use (&$return) {
            sleep(2);
            return false;
        }, 1);
        $this->assertFalse($return);
    }
}
