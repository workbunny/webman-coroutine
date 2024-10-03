<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\WaitGroupInterface;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup;

class WaitGroupTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConstruct()
    {
        $mockInterface = Mockery::mock(WaitGroupInterface::class);
        $mockInterface->shouldReceive('add')->andReturn(true);
        $mockInterface->shouldReceive('done')->andReturn(true);
        $mockInterface->shouldReceive('count')->andReturn(0);
        $mockInterface->shouldReceive('wait')->andReturnNull();

        $waitGroup = new WaitGroup();
        $reflection = new \ReflectionClass($waitGroup);
        $property = $reflection->getProperty('_interface');
        $property->setAccessible(true);
        $property->setValue($waitGroup, $mockInterface);

        $this->assertInstanceOf(WaitGroupInterface::class, $property->getValue($waitGroup));
    }

    public function testDestruct()
    {
        $mockInterface = Mockery::mock(WaitGroupInterface::class);
        $mockInterface->shouldReceive('add')->andReturn(true);
        $mockInterface->shouldReceive('done')->andReturn(true);
        $mockInterface->shouldReceive('count')->andReturn(0);
        $mockInterface->shouldReceive('wait')->andReturnNull();

        $waitGroup = new WaitGroup();
        $reflection = new \ReflectionClass($waitGroup);
        $property = $reflection->getProperty('_interface');
        $property->setAccessible(true);
        $property->setValue($waitGroup, $mockInterface);

        $waitGroup->__destruct();
        $this->assertNull($property->getValue($waitGroup));
    }

    public function testRegisterVerify()
    {
        $this->assertEquals(WaitGroupInterface::class, WaitGroup::registerVerify(Mockery::mock(WaitGroupInterface::class)));
        $this->assertFalse(WaitGroup::registerVerify(new \stdClass()));
    }

    public function testUnregisterExecute()
    {
        $this->assertTrue(WaitGroup::unregisterExecute('some_key'));
    }
}
