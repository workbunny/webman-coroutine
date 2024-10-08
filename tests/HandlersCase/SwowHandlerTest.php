<?php

declare(strict_types=1);

namespace Workbunny\Tests\HandlersCase;

use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Handlers\SwowHandler;

class SwowHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
    }

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

        $return = false;
        SwowHandler::waitFor(function () use (&$return) {
            return false;
        }, 1);
        $this->assertFalse($return);
    }
}
