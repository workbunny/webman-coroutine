<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Channel;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\Channel\Channel;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\ChannelInterface;

class ChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConstruct()
    {
        $mockInterface = Mockery::mock(ChannelInterface::class);
        $mockInterface->shouldReceive('__construct')
            ->with(-1);

        $channel = Mockery::mock(Channel::class, [-1])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $channel->shouldReceive('__destruct')
            ->andReturnNull();

        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_interface');
        $property->setAccessible(true);
        $property->setValue($channel, $mockInterface);

        $this->assertInstanceOf(Channel::class, $channel);
    }

    public function testDestruct()
    {
        $mockInterface = Mockery::mock(ChannelInterface::class);
        $mockInterface->shouldReceive('__destruct')
            ->andReturnNull();

        $channel = Mockery::mock(Channel::class, [-1])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $channel->shouldReceive('__destruct')
            ->andReturnNull();

        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_interface');
        $property->setAccessible(true);
        $property->setValue($channel, $mockInterface);

        // 验证析构前的状态
        $this->assertInstanceOf(ChannelInterface::class, $property->getValue($channel));

        // 调用析构函数
        $channel->__destruct();

        // 验证析构后的状态
        $this->assertNull($property->getValue($channel));
    }

    public function testRegisterVerify()
    {
        $this->assertEquals(ChannelInterface::class, Channel::registerVerify(Mockery::mock(ChannelInterface::class)));
        $this->assertFalse(Channel::registerVerify(new \stdClass()));
    }

    public function testUnregisterExecute()
    {
        $this->assertTrue(Channel::unregisterExecute('some_key'));
    }

    public function testInterfaceLoaded()
    {
        $mockInterface = Mockery::mock(ChannelInterface::class);
        $mockInterface->shouldReceive('__construct')
            ->with(-1);

        $channel = Mockery::mock(Channel::class, [-1])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $channel->shouldReceive('__destruct')
            ->andReturnNull();

        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_interface');
        $property->setAccessible(true);
        $property->setValue($channel, $mockInterface);

        $this->assertInstanceOf(ChannelInterface::class, $property->getValue($channel));
    }
}
