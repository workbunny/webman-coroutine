<?php

declare(strict_types=1);

namespace Workbunny\Tests;

use PHPUnit\Framework\TestCase;
use Workbunny\Tests\mock\TestHandler;
use Workbunny\WebmanCoroutine\Factory;
use function Workbunny\WebmanCoroutine\event_loop;
use function Workbunny\WebmanCoroutine\package_installed;
class HelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../src/helpers.php';
    }

    public function testEventLoopWithExpectedClass()
    {
        Factory::register('SomeEventLoopClass', TestHandler::class);
        $expectedClass = 'SomeEventLoopClass';
        $result = event_loop($expectedClass);
        $this->assertEquals($expectedClass, $result);
        Factory::unregister('SomeEventLoopClass');
    }

    public function testEventLoopWithDefaultClass()
    {
        // env auto return
        $result = event_loop();
        $this->assertEquals(Factory::WORKERMAN_DEFAULT, $result);
        // not found class
        $result = event_loop('SomeEventLoopClass');
        $this->assertEquals(Factory::WORKERMAN_DEFAULT, $result);
    }

    public function testPackageInstalled()
    {
        $packageName = 'webman/console';
        $this->assertTrue(package_installed($packageName));
        $packageName = 'nonexistent/package';
        $this->assertFalse(package_installed($packageName));
    }
}