<?php

declare(strict_types=1);

namespace Workbunny\Tests\EventsCase;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Events\SwowEvent;
use Workbunny\WebmanCoroutine\Exceptions\EventLoopException;
use Workerman\Events\EventInterface;

///**
// * @runTestsInSeparateProcesses
// */
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
        SwowEvent::$debug = true;
        require_once __DIR__ . '/../mock/helpers.php';
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
        SwowEvent::$debug = false;
        // normal
        $this->expectException(EventLoopException::class);
        $this->expectExceptionMessage('Not support ext-swow.');
        new SwowEvent();

        SwowEvent::$debug = true;
        // debug
        $swowEvent = new SwowEvent();
        $this->assertInstanceOf(SwowEvent::class, $swowEvent);
    }

    public function testAddSignal()
    {
        $swowEvent = new SwowEvent();

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($callback) use ($coroutineMock) {
            $callback();
            return $coroutineMock;
        });

        $signalMock = m::mock('alias:Swow\Signal');
        $signalMock->shouldReceive('wait')->andReturn(true);

        // 通过mock测试，回调会在signal::wait后立即执行，所以echo
        $this->expectOutputString('Signal received');
        $result = $swowEvent->add(SIGTERM, EventInterface::EV_SIGNAL, function () {
            echo 'Signal received';
        });

        $this->assertTrue($result);

        $result = $swowEvent->add(SIGTERM, EventInterface::EV_SIGNAL, function () {
            echo 'Signal received2';
        });

        $this->assertFalse($result);
    }

    public function testDelSignal()
    {
        $swowEvent = new SwowEvent();

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnSelf();
        $coroutineMock->shouldReceive('kill')->andReturn(true);
        $coroutineMock->shouldReceive('isExecuting')->andReturn(true);

        $swowEvent->add(SIGTERM, EventInterface::EV_SIGNAL, function () {
            echo 'Signal received';
        });

        $result = $swowEvent->del(SIGTERM, EventInterface::EV_SIGNAL);

        $this->assertTrue($result);

        $result = $swowEvent->del(SIGTERM, EventInterface::EV_SIGNAL);

        $this->assertFalse($result);
    }

    public function testAddTimer()
    {
        $swowEvent = new SwowEvent();

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($callback) use ($coroutineMock) {
            $callback();
            return $coroutineMock;
        });

        $this->expectOutputString('Timer once triggered');
        $result = $swowEvent->add(1, EventInterface::EV_TIMER_ONCE, function () {
            echo 'Timer once triggered';
        });
        $this->assertEquals(1, $result);
    }

    public function testDelTimer()
    {
        $swowEvent = new SwowEvent();

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

    public function testAddReadException()
    {
        $swowEvent = new SwowEvent();
        // mock run
        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($callback) use ($coroutineMock) {
            $callback();
            return $coroutineMock;
        });
        // exception
        set_stream_poll_one_return(-1);
        $stream = fopen('php://memory', 'r+');
        $swowEvent->add($stream, EventInterface::EV_READ, function () {
            echo 'Read event exception';
        });
        $this->assertTrue(true);
    }

    public function testAddAndDelRead()
    {
        $swowEvent = new SwowEvent();
        // mock run
        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($callback) use ($coroutineMock) {
            $callback();
            return $coroutineMock;
        });
        // 非可读流
        $result = $swowEvent->add('string', EventInterface::EV_READ, function () {
            echo 'Read event';
        });
        $this->assertFalse($result);
        // callback 和 非del
        set_stream_poll_one_return(STREAM_POLLIN);
        $stream = fopen('php://memory', 'r+');
        // 创建
        $this->expectOutputString('Read event1');
        $result = $swowEvent->add($stream, EventInterface::EV_READ, function () {
            echo 'Read event1';
        });
        $this->assertTrue($result);
        // 非callback 和 del
        set_stream_poll_one_return(STREAM_POLLNONE);
        $stream = fopen('php://memory', 'r+');
        // 创建
        $result = $swowEvent->add($stream, EventInterface::EV_READ, function () {
            echo 'Read event2';
        });
        $this->assertTrue($result);
        // mock 协程未存活
        $coroutineMock->shouldReceive('isExecuting')->andReturn(false);
        $result = $swowEvent->del($stream, EventInterface::EV_READ);
        $this->assertTrue($result);
        // mock 协程存活
        $stream = fopen('php://memory', 'r+');
        $swowEvent->add($stream, EventInterface::EV_READ, function () {});
        $coroutineMock->shouldReceive('isExecuting')->andReturn(true);
        $coroutineMock->shouldReceive('kill')->andReturn(true);
        $result = $swowEvent->del($stream, EventInterface::EV_READ);
        $this->assertTrue($result);
        // 重复删除
        $result = $swowEvent->del($stream, EventInterface::EV_READ);
        $this->assertFalse($result);

        fclose($stream);
    }

    public function testAddWriteException()
    {
        $swowEvent = new SwowEvent();
        // mock run
        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($callback) use ($coroutineMock) {
            $callback();
            return $coroutineMock;
        });
        // exception
        set_stream_poll_one_return(-1);
        $stream = fopen('php://memory', 'w+');
        $swowEvent->add($stream, EventInterface::EV_WRITE, function () {
            echo 'Write event exception';
        });
        $this->assertTrue(true);
    }

    public function testAddAndDelWrite()
    {
        $swowEvent = new SwowEvent();
        // mock run
        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($callback) use ($coroutineMock) {
            $callback();
            return $coroutineMock;
        });
        // 非可写流
        $result = $swowEvent->add('string', EventInterface::EV_WRITE, function () {
            echo 'Write event';
        });
        $this->assertFalse($result);
        // callback 和 非del
        set_stream_poll_one_return(STREAM_POLLOUT);
        $stream = fopen('php://memory', 'w+');
        // 创建
        $this->expectOutputString('Write event1');
        $result = $swowEvent->add($stream, EventInterface::EV_WRITE, function () {
            echo 'Write event1';
        });
        $this->assertTrue($result);
        // 非callback 和 del
        set_stream_poll_one_return(STREAM_POLLNONE);
        $stream = fopen('php://memory', 'w+');
        // 创建
        $result = $swowEvent->add($stream, EventInterface::EV_WRITE, function () {
            echo 'Write event2';
        });
        $this->assertTrue($result);
        // mock 协程未存活
        $coroutineMock->shouldReceive('isExecuting')->andReturn(false);
        $result = $swowEvent->del($stream, EventInterface::EV_WRITE);
        $this->assertTrue($result);
        // mock 协程存活
        $stream = fopen('php://memory', 'w+');
        $swowEvent->add($stream, EventInterface::EV_WRITE, function () {});
        $coroutineMock->shouldReceive('isExecuting')->andReturn(true);
        $coroutineMock->shouldReceive('kill')->andReturn(true);
        $result = $swowEvent->del($stream, EventInterface::EV_WRITE);
        $this->assertTrue($result);
        // 重复删除
        $result = $swowEvent->del($stream, EventInterface::EV_WRITE);
        $this->assertFalse($result);

        fclose($stream);
    }

    public function testUnknownEvent()
    {
        $swowEvent = new SwowEvent();
        $this->assertNull($swowEvent->add(1, 0xFFFFFFFF, function () {}));

        $this->assertNull($swowEvent->del(1, 0xFFFFFFFF, function () {}));
    }

    public function testLoop()
    {
        $this->markTestSkipped('skip');

        $swowEvent = new SwowEvent();

        $waitGroupMock = m::mock('alias:Swow\Sync\WaitGroup');
        $waitGroupMock->shouldReceive('add')->andReturn('add');
        $waitGroupMock->shouldReceive('wait')->andReturn(true);

        $this->expectOutputString('');
        $swowEvent->loop();
    }

    public function testDestroy()
    {
        $swowEvent = new SwowEvent();

        $coroutineMock = m::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('killAll')->andReturn(true);

        $waitGroupMock = m::mock('alias:Swow\Sync\WaitGroup');
        $waitGroupMock->shouldReceive('done')->andReturn(true);

        $swowEvent->destroy();

        $this->assertEmpty($swowEvent->getTimerCount());
    }

    public function testClearAllTimer()
    {
        $swowEvent = new SwowEvent();

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
