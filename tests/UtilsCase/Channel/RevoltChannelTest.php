<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Channel;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\RevoltChannel;

class RevoltChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConstruct()
    {
        $channel = new RevoltChannel(10);
        $this->assertInstanceOf(RevoltChannel::class, $channel);
        $this->assertEquals(10, $channel->capacity());
    }

    public function testPushAndPop()
    {
        $channel = new RevoltChannel(5);
        $this->assertEquals(5, $channel->capacity());

        $this->assertTrue($channel->push('test1'));
        $this->assertTrue($channel->push('test2'));
        $this->assertEquals('test1', $channel->pop());
        $this->assertEquals('test2', $channel->pop());
    }

    public function testIsEmpty()
    {
        $channel = new RevoltChannel();
        $this->assertTrue($channel->isEmpty());
        $channel->push('test');
        $this->assertFalse($channel->isEmpty());
    }

    public function testIsFull()
    {
        $channel = new RevoltChannel(1);
        $this->assertFalse($channel->isFull());
        $channel->push('test');
        $this->assertTrue($channel->isFull());
    }

    public function testClose()
    {
        $channel = new RevoltChannel();
        $channel->push('test');
        $channel->close();
        $this->assertTrue($channel->isEmpty());
    }

    public function testPushWithTimeout()
    {
        Mockery::mock('alias:Workbunny\WebmanCoroutine\Handlers\RevoltHandler')
            ->shouldReceive('sleep')->andReturnNull();
        $channel = new RevoltChannel(1);
        $channel->push('test');

        $this->assertFalse($channel->push('test2', 1));
    }

    public function testPopWithTimeout()
    {
        Mockery::mock('alias:Workbunny\WebmanCoroutine\Handlers\RevoltHandler')
            ->shouldReceive('sleep')->andReturnNull();
        $channel = new RevoltChannel(1);

        $this->assertFalse($channel->pop(1));
    }
}
