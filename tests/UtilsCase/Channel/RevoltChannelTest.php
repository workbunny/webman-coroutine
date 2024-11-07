<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Channel;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\RevoltHandler;
use Workbunny\WebmanCoroutine\Utils\Channel\Handlers\RevoltChannel;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class RevoltChannelTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testPushWithTimeout()
    {
        $eventLoopMock = Mockery::mock('alias:' . RevoltHandler::class);
        $eventLoopMock->shouldReceive('waitFor')->once()->andReturnUsing(function ($closure, $timeout, $event) {
            return true;
        });
        $eventLoopMock->shouldReceive('wakeup')->once()->andReturnUsing(function ($event) {
            return true;
        });
        $channel = new RevoltChannel(1);
        $channel->push('test');

        $eventLoopMock->shouldReceive('waitFor')->once()->andReturnUsing(function ($closure, $timeout, $event) {
            $this->assertTrue(str_starts_with($event, 'channel.push.'));
            $this->assertEquals(1, $timeout);
            $this->assertFalse(call_user_func($closure));
            throw new TimeoutException('Timeout after 1 seconds.');
        });
        $this->assertFalse($channel->push('test2', 1));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testPopWithTimeout()
    {
        $channel = new RevoltChannel(1);

        $eventLoopMock = Mockery::mock('alias:' . RevoltHandler::class);
        $eventLoopMock->shouldReceive('waitFor')->andReturnUsing(function ($closure, $timeout, $event) {
            $this->assertTrue(str_starts_with($event, 'channel.pop.'));
            $this->assertEquals(1, $timeout);
            $this->assertFalse(call_user_func($closure));
            throw new TimeoutException('Timeout after 1 seconds.');
        });

        $this->assertFalse($channel->pop(1));
    }
}
