<?php

declare(strict_types=1);

namespace Workbunny\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Workbunny\Tests\mock\TestHandler;
use Workbunny\WebmanCoroutine\Factory;

/**
 * @runTestsInSeparateProcesses
 */
class FactoryTest extends TestCase
{
    public function testRegister()
    {
        $result = Factory::register(__METHOD__, TestHandler::class);
        $this->assertTrue($result);

        $reflection = new ReflectionClass(Factory::class);
        $property = $reflection->getProperty('_handlers');
        $property->setAccessible(true);
        $handlers = $property->getValue();
        $this->assertEquals(TestHandler::class, $handlers[__METHOD__] ?? null);

        Factory::unregister(__METHOD__);
    }

    public function testRegisterExistingHandler()
    {
        Factory::register(__METHOD__, TestHandler::class);
        $result = Factory::register(__METHOD__, TestHandler::class);
        $this->assertNull($result);

        Factory::unregister(__METHOD__);
    }

    public function testUnregister()
    {
        Factory::register(__METHOD__, TestHandler::class);
        $result = Factory::unregister(__METHOD__);
        $this->assertTrue($result);

        $reflection = new ReflectionClass(Factory::class);
        $property = $reflection->getProperty('_handlers');
        $property->setAccessible(true);
        $handlers = $property->getValue();
        $this->assertArrayNotHasKey(__METHOD__, $handlers);

        Factory::unregister(__METHOD__);
    }
}
