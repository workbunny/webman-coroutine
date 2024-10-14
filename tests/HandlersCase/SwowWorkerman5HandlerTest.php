<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\SwowWorkerman5Handler as SwowHandler;

class SwowWorkerman5HandlerTest extends SwooleHandlerTest
{
    public function testIsAvailable()
    {
        $this->markTestSkipped('Skipped. ');
    }

    public function testInitEnv()
    {
        SwowHandler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        $return = false;
        SwowHandler::waitFor(function () use (&$return) {
            return $return = true;
        });
        $this->assertTrue($return);

        $return = false;
        SwowHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);
        // 模拟超时
        $this->expectException(TimeoutException::class);
        SwowHandler::waitFor(function () use (&$return) {
            return false;
        }, 1);
        $this->assertFalse($return);
    }
}
