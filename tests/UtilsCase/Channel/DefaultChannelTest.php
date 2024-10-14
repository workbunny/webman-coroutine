<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Channel;

use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\DefaultChannel;

class DefaultChannelTest extends TestCase
{
    public function testConstructAndCapacity()
    {
        $channel = new DefaultChannel(10);
        $this->assertInstanceOf(DefaultChannel::class, $channel);
        $this->assertEquals(10, $channel->capacity());
    }

    public function testPushAndPop()
    {
        $channel = new DefaultChannel(5);
        $this->assertEquals(5, $channel->capacity());

        $this->assertTrue($channel->push('test1'));
        $this->assertTrue($channel->push('test2'));
        $this->assertEquals('test1', $channel->pop());
        $this->assertEquals('test2', $channel->pop());
    }

    public function testIsEmpty()
    {
        $channel = new DefaultChannel();
        $this->assertTrue($channel->isEmpty());
        $channel->push('test');
        $this->assertFalse($channel->isEmpty());
    }

    public function testIsFull()
    {
        $channel = new DefaultChannel(1);
        $this->assertFalse($channel->isFull());
        $channel->push('test');
        $this->assertTrue($channel->isFull());
    }

    public function testClose()
    {
        $channel = new DefaultChannel();
        $channel->push('test');
        $channel->close();
        $this->assertTrue($channel->isEmpty());
    }
}
