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
        // success
        $return = false;
        DefaultHandler::waitFor(function () use (&$return) {
            return ($return = true);
        });
        $this->assertTrue($return);

        // success with sleep
        $return = false;
        DefaultHandler::waitFor(function () use (&$return) {
            sleep(1);

            return $return = true;
        });
        $this->assertTrue($return);

        // success with event
        $return = false;
        DefaultHandler::waitFor(function () use (&$return) {
            sleep(1);

            $return = true;
            DefaultHandler::wakeup(__METHOD__);

            return $return;
        }, event: __METHOD__);
        $this->assertTrue($return);

        // timeout in loop
        $this->expectException(TimeoutException::class);
        DefaultHandler::waitFor(function () {
            return false;
        }, 1);

        // timeout not loop
        $this->expectException(TimeoutException::class);
        // 模拟超时
        DefaultHandler::waitFor(function () {
            sleep(2);

            return false;
        }, 0.1);
    }

    public function testSleep()
    {
        DefaultHandler::sleep();
        $this->assertTrue(true);

        DefaultHandler::sleep(0.001);
        $this->assertTrue(true);

        DefaultHandler::sleep(0.0009);
        $this->assertTrue(true);

        DefaultHandler::sleep();
        $this->assertTrue(true);

        DefaultHandler::sleep(event: __METHOD__);
        $this->assertTrue(true);

        DefaultHandler::sleep(-1, event: __METHOD__);
        $this->assertTrue(true);
    }

    public function testWakeup()
    {
        DefaultHandler::wakeup(__METHOD__);
        $this->assertTrue(true);
    }
}
