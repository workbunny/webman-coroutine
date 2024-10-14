<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Channel;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\SwowChannel;

class SwowChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testConstruct()
    {
        $mockChannel = Mockery::mock('\Swow\Channel');
        $mockChannel->shouldReceive('close')
            ->once();
        $channel = new SwowChannel(10);
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_channel');
        $property->setAccessible(true);
        $property->setValue($channel, $mockChannel);
        $this->assertInstanceOf(SwowChannel::class, $channel);
    }

    public function testPushAndPop()
    {
        $mockChannel = Mockery::mock('\Swow\Channel');
        $mockChannel->shouldReceive('close')
            ->once();
        $mockChannel->shouldReceive('push')
            ->once()
            ->with(__METHOD__, -1)
            ->andReturn(true);

        $mockChannel->shouldReceive('pop')
            ->once()
            ->andReturn(__METHOD__);

        $channel = new SwowChannel(10);
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_channel');
        $property->setAccessible(true);
        $property->setValue($channel, $mockChannel);

        $channel->push(__METHOD__);
        $this->assertEquals(__METHOD__, $channel->pop());
    }

    public function testIsEmpty()
    {
        $mockChannel = Mockery::mock('\Swow\Channel');
        $mockChannel->shouldReceive('close')
            ->once();
        $mockChannel->shouldReceive('isEmpty')
            ->once()
            ->andReturn(true);

        $channel = new SwowChannel(10);
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_channel');
        $property->setAccessible(true);
        $property->setValue($channel, $mockChannel);

        $this->assertTrue($channel->isEmpty());
    }

    public function testIsFull()
    {
        $mockChannel = Mockery::mock('\Swow\Channel');
        $mockChannel->shouldReceive('close')
            ->once();
        $mockChannel->shouldReceive('isFull')
            ->once()
            ->andReturn(true);

        $channel = new SwowChannel(10);
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_channel');
        $property->setAccessible(true);
        $property->setValue($channel, $mockChannel);

        $this->assertTrue($channel->isFull());
    }

    public function testClose()
    {
        $mockChannel = Mockery::mock('\Swow\Channel');
        $mockChannel->shouldReceive('close')
            ->twice();

        $channel = new SwowChannel(10);
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_channel');
        $property->setAccessible(true);
        $property->setValue($channel, $mockChannel);

        $channel->close();
        unset($channel);
        $this->assertTrue(true);
        //        $mockChannel = Mockery::mock('\Swow\Channel');
        //        $mockChannel->shouldReceive('close')
        //            ->once();
        //
        //        $channel = Mockery::mock(SwowChannel::class, [10])
        //            ->makePartial()
        //            ->shouldAllowMockingProtectedMethods();
        //        $channel->shouldReceive('__destruct')
        //            ->andReturnNull();
        //
        //        $reflection = new \ReflectionClass($channel);
        //        $property = $reflection->getProperty('_channel');
        //        $property->setAccessible(true);
        //        $property->setValue($channel, $mockChannel);
        //
        //        $channel->close();
    }

    public function testCapacity()
    {
        $mockChannel = Mockery::mock('\Swow\Channel');
        $mockChannel->shouldReceive('close')
            ->once();
        $mockChannel->shouldReceive('getCapacity')
            ->once()
            ->andReturn(10);

        $channel = new SwowChannel(10);
        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_channel');
        $property->setAccessible(true);
        $property->setValue($channel, $mockChannel);

        $this->assertEquals(10, $channel->capacity());
    }
}
