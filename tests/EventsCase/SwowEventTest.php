<?php

declare(strict_types=1);

namespace Workbunny\Tests\EventsCase;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Events\SwowEvent;
use Workbunny\WebmanCoroutine\Exceptions\EventLoopException;
use Workerman\Events\EventInterface;

/**
 * @runTestsInSeparateProcesses
 */
class SwowEventTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // 定义缺失的常量
        if (!defined('STREAM_POLLIN')) {
            define('STREAM_POLLIN', 1);
        }
        if (!defined('STREAM_POLLOUT')) {
            define('STREAM_POLLOUT', 2);
        }
        if (!defined('STREAM_POLLHUP')) {
            define('STREAM_POLLHUP', 4);
        }
        if (!defined('STREAM_POLLNONE')) {
            define('STREAM_POLLNONE', 0);
        }
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testConstructWithoutSwowExtension()
    {
        if (extension_loaded('swow')) {
            $this->markTestSkipped('The swow extension is loaded.');
        }
        // normal
        $this->expectException(EventLoopException::class);
        $this->expectExceptionMessage('Not support ext-swow.');
        new SwowEvent();

        // debug
        $swowEvent = new SwowEvent(true);
        $this->assertInstanceOf(SwowEvent::class, $swowEvent);
    }

    public function testAddSignal()
    {
        $swowEvent = new SwowEvent(true);

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnSelf();

        $signalMock = m::mock('alias:Swow\Signal');
        $signalMock->shouldReceive('wait')->andReturn(true);

        $result = $swowEvent->add(SIGTERM, EventInterface::EV_SIGNAL, function () {
            echo 'Signal received';
        });

        $this->assertTrue($result);
    }

    public function testDelSignal()
    {
        $swowEvent = new SwowEvent(true);

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnSelf();
        $coroutineMock->shouldReceive('kill')->andReturn(true);
        $coroutineMock->shouldReceive('isExecuting')->andReturn(true);

        $swowEvent->add(SIGTERM, EventInterface::EV_SIGNAL, function () {
            echo 'Signal received';
        });

        $result = $swowEvent->del(SIGTERM, EventInterface::EV_SIGNAL);

        $this->assertTrue($result);
    }

    public function testAddTimer()
    {
        $swowEvent = new SwowEvent(true);

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnSelf();
        $coroutineMock->shouldReceive('sleep')->andReturn(true);

        $result = $swowEvent->add(1, EventInterface::EV_TIMER, function () {
            echo 'Timer triggered';
        });

        $this->assertEquals(1, $result);
    }

    public function testDelTimer()
    {
        $swowEvent = new SwowEvent(true);

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnSelf();
        $coroutineMock->shouldReceive('kill')->andReturn(true);
        $coroutineMock->shouldReceive('isExecuting')->andReturn(true);

        $timerId = $swowEvent->add(1, EventInterface::EV_TIMER, function () {
            echo 'Timer triggered';
        });

        $result = $swowEvent->del($timerId, EventInterface::EV_TIMER);

        $this->assertTrue($result);
    }

    public function testAddAndDelRead()
    {
        $swowEvent = new SwowEvent(true);
        // mock run
        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnSelf();
        $stream = fopen('php://memory', 'r+');
        // 创建
        $result = $swowEvent->add($stream, EventInterface::EV_READ, function () {
            echo 'Read event';
        });
        $this->assertTrue($result);

        // mock 协程未存活
        $coroutineMock->shouldReceive('isExecuting')->andReturn(false);
        $result = $swowEvent->del($stream, EventInterface::EV_READ);
        $this->assertTrue($result);
        // mock 协程存活
        $swowEvent->add($stream, EventInterface::EV_READ, function () {
            echo 'Read event';
        });
        $coroutineMock->shouldReceive('isExecuting')->andReturn(true);
        $coroutineMock->shouldReceive('kill')->andReturn(true);
        $result = $swowEvent->del($stream, EventInterface::EV_READ);
        $this->assertTrue($result);
        // 重复删除
        $result = $swowEvent->del($stream, EventInterface::EV_READ);
        $this->assertFalse($result);

        fclose($stream);
    }

    public function testAddAndDelWrite()
    {
        $swowEvent = new SwowEvent(true);

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnSelf();
        $stream = fopen('php://memory', 'w+');
        $result = $swowEvent->add($stream, EventInterface::EV_WRITE, function () {
            echo 'Write event';
        });
        $this->assertTrue($result);
        // mock 协程未存活
        $coroutineMock->shouldReceive('isExecuting')->andReturn(false);
        $result = $swowEvent->del($stream, EventInterface::EV_WRITE);
        $this->assertTrue($result);
        // mock 协程存活
        $swowEvent->add($stream, EventInterface::EV_WRITE, function () {
            echo 'Write event';
        });
        $coroutineMock->shouldReceive('isExecuting')->andReturn(true);
        $coroutineMock->shouldReceive('kill')->andReturn(true);
        $result = $swowEvent->del($stream, EventInterface::EV_WRITE);
        $this->assertTrue($result);
        // 重复删除
        $result = $swowEvent->del($stream, EventInterface::EV_WRITE);
        $this->assertFalse($result);

        fclose($stream);
    }

    public function testLoop()
    {
        $this->markTestSkipped('skip');

        $swowEvent = new SwowEvent(true);

        $waitGroupMock = m::mock('alias:Swow\Sync\WaitGroup');
        $waitGroupMock->shouldReceive('add')->andReturn('add');
        $waitGroupMock->shouldReceive('wait')->andReturn(true);

        $this->expectOutputString('');
        $swowEvent->loop();
    }

    public function testDestroy()
    {
        $swowEvent = new SwowEvent(true);

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('killAll')->andReturn(true);

        $waitGroupMock = m::mock('alias:Swow\Sync\WaitGroup');
        $waitGroupMock->shouldReceive('done')->andReturn(true);

        $swowEvent->destroy();

        $this->assertEmpty($swowEvent->getTimerCount());
    }

    public function testClearAllTimer()
    {
        $swowEvent = new SwowEvent(true);

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('kill')->andReturn(true);
        $coroutineMock->shouldReceive('isExecuting')->andReturn(true);
        $coroutineMock->shouldReceive('run')->andReturnSelf();

        $swowEvent->add(1, EventInterface::EV_TIMER, function () {
            echo 'Timer triggered';
        });

        $swowEvent->clearAllTimer();

        $this->assertEquals(0, $swowEvent->getTimerCount());
    }
}
