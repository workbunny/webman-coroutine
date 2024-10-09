<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use PHPUnit\Framework\TestCase;
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

        $return = false;
        DefaultHandler::waitFor(function () use (&$return) {
            return false;
        }, 1);
        $this->assertFalse($return);
    }
}
