<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Channel;

use PHPUnit\Framework\TestCase;
use Mockery;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\RippleChannel;

class RippleChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConstruct()
    {
        $channel = new RippleChannel(10);
        $this->assertInstanceOf(RippleChannel::class, $channel);
        $this->assertEquals(10, $channel->capacity());
    }

    public function testPushAndPop()
    {
        $channel = new RippleChannel(5);
        $this->assertEquals(5, $channel->capacity());

        $this->assertTrue($channel->push('test1'));
        $this->assertTrue($channel->push('test2'));
        $this->assertEquals('test1', $channel->pop());
        $this->assertEquals('test2', $channel->pop());
    }

    public function testIsEmpty()
    {
        $channel = new RippleChannel();
        $this->assertTrue($channel->isEmpty());
        $channel->push('test');
        $this->assertFalse($channel->isEmpty());
    }

    public function testIsFull()
    {
        $channel = new RippleChannel(1);
        $this->assertFalse($channel->isFull());
        $channel->push('test');
        $this->assertTrue($channel->isFull());
    }

    public function testClose()
    {
        $channel = new RippleChannel();
        $channel->push('test');
        $channel->close();
        $this->assertTrue($channel->isEmpty());
    }

    public function testPushWithTimeout()
    {
        $partialMock = Mockery::mock('Workbunny\WebmanCoroutine\Utils\Channel\Handlers\RippleChannel', [1])->makePartial();
        $partialMock->push('test');
        $partialMock->shouldAllowMockingProtectedMethods()->shouldReceive('_sleep')->andReturnNull();

        $this->assertFalse($partialMock->push('test2', 1));
    }

    public function testPopWithTimeout()
    {
        $partialMock = Mockery::mock('Workbunny\WebmanCoroutine\Utils\Channel\Handlers\RippleChannel', [])->makePartial();
        $partialMock->shouldAllowMockingProtectedMethods()->shouldReceive('_sleep')->andReturnNull();

        $this->assertFalse($partialMock->pop( 1));
    }
}
