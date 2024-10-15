<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\DefaultHandler;

class DefaultHandlerTest extends TestCase
{
    public function testIsAvailable()
    {
        $this->assertTrue(DefaultHandler::isAvailable());
    }

    public function testInitEnv()
    {
        DefaultHandler::initEnv();
        $this->assertTrue(true);
    }

    public function testWaitFor()
    {
        $return = false;
        DefaultHandler::waitFor(function () use (&$return) {
            return ($return = true);
        });
        $this->assertTrue($return);

        $return = false;
        DefaultHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);

        $this->expectException(TimeoutException::class);
        // 模拟超时
        DefaultHandler::waitFor(function () use (&$return) {
            sleep(2);

            return false;
        }, 0.1);

        $this->assertFalse($return);
    }
}
