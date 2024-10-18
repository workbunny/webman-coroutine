<?php

declare(strict_types=1);

namespace Workbunny\Tests\EventsCase;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Events\SwooleEvent;
use Workbunny\WebmanCoroutine\Exceptions\EventLoopException;
use Workerman\Events\EventInterface;

class SwooleEventTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // 定义缺失的常量
        if (!defined('SWOOLE_EVENT_READ')) {
            define('SWOOLE_EVENT_READ', 1);
        }
        if (!defined('SWOOLE_EVENT_WRITE')) {
            define('SWOOLE_EVENT_WRITE', 2);
        }
        SwooleEvent::$debug = true;
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testConstructWithoutSwooleExtension()
    {
        if (extension_loaded('swoole')) {
            $this->markTestSkipped('Swoole extension is loaded.');
        }
        // normal
        $this->expectException(EventLoopException::class);
        $this->expectExceptionMessage('Not support ext-swoole.');
        SwooleEvent::$debug = false;
        new SwooleEvent();

        SwooleEvent::$debug = true;
        // debug
        $swooleEvent = new SwooleEvent();
        $this->assertInstanceOf(SwooleEvent::class, $swooleEvent);
    }

    public function testAddSignal()
    {
        $swooleEvent = new SwooleEvent();

        $processMock = m::mock('alias:Swoole\Process');
        $processMock->shouldReceive('signal')->andReturn(true);

        $result = $swooleEvent->add(SIGTERM, EventInterface::EV_SIGNAL, function () {
            echo 'Signal received';
        });
        $this->assertTrue($result);

        $result = $swooleEvent->add(SIGTERM, EventInterface::EV_SIGNAL, function () {
            echo 'Signal received';
        });
        $this->assertFalse($result);
    }

    public function testDelSignal()
    {
        $swooleEvent = new SwooleEvent();

        $processMock = m::mock('alias:Swoole\Process');
        $processMock->shouldReceive('signal')->andReturn(true);

        $swooleEvent->add(SIGTERM, EventInterface::EV_SIGNAL, function () {
            echo 'Signal received';
        });

        $result = $swooleEvent->del(SIGTERM, EventInterface::EV_SIGNAL);

        $this->assertTrue($result);

        $result = $swooleEvent->del(SIGTERM, EventInterface::EV_SIGNAL);

        $this->assertFalse($result);
    }

    public function testAddTimer()
    {
        $swooleEvent = new SwooleEvent();
        $timerMock = m::mock('alias:Swoole\Timer');
        $timerMock->shouldReceive('tick')->andReturnUsing(function ($interval, $callback) {
            $callback();

            return 1;
        });
        $this->expectOutputString('Timer triggered');

        $result = $swooleEvent->add(1, EventInterface::EV_TIMER, function () {
            echo 'Timer triggered';
        });
        $this->assertEquals(1, $result);


    }

    public function testAddOnceTimer()
    {
        $swooleEvent = new SwooleEvent();
        $timerMock = m::mock('alias:Swoole\Timer');
        $timerMock->shouldReceive('tick')->andReturnUsing(function ($interval, $callback) {
            $callback();

            return 1;
        });
        $this->expectOutputString('Timer once triggered');
        $result = $swooleEvent->add(1, EventInterface::EV_TIMER_ONCE, function () {
            echo 'Timer once triggered';
        });
        $this->assertEquals(1, $result);
    }

    public function testAddTimerMs()
    {
        $swooleEvent = new SwooleEvent();
        SwooleEvent::$debug = true;
        $timerMock = m::mock('alias:Swoole\Coroutine');
        $timerMock->shouldReceive('create')->andReturnUsing(function ($callback) {
            $callback();

            return 1;
        });
        $this->expectOutputString('Timer triggered ms');

        $result = $swooleEvent->add(0.0009, EventInterface::EV_TIMER, function () {
            echo 'Timer triggered ms';
        });
        $this->assertEquals(1, $result);
    }

    public function testAddOnceTimerMs()
    {
        $swooleEvent = new SwooleEvent();
        SwooleEvent::$debug = true;
        $timerMock = m::mock('alias:Swoole\Coroutine');
        $timerMock->shouldReceive('create')->andReturnUsing(function ($callback) {
            $callback();

            return 1;
        });
        $this->expectOutputString('Timer once triggered ms');
        $result = $swooleEvent->add(0.0009, EventInterface::EV_TIMER_ONCE, function () {
            echo 'Timer once triggered ms';
        });
        $this->assertEquals(1, $result);
        m::close();

        $swooleEvent = new SwooleEvent();
        SwooleEvent::$debug = true;
        $timerMock = m::mock('alias:Swoole\Coroutine');
        $timerMock->shouldReceive('create')->andReturn(false);
        $result = $swooleEvent->add(0.0009, EventInterface::EV_TIMER, function () {
            echo 'Timer triggered false';
        });
        $this->assertFalse($result);
    }

    public function testDelTimer()
    {
        $swooleEvent = new SwooleEvent();

        $timerMock = m::mock('alias:Swoole\Timer');
        $timerMock->shouldReceive('tick')->andReturn(1);
        $timerMock->shouldReceive('clear')->andReturn(true);
        $timerMock = m::mock('alias:Swoole\Coroutine');
        $timerMock->shouldReceive('create')->andReturn(2);
        $timerMock->shouldReceive('cancel')->andReturn(true);

        $timerId = $swooleEvent->add(1, EventInterface::EV_TIMER, $fuc = function () {
            echo 'Timer triggered';
        });
        if (!$timerId) {
            while (true) {
                $r = $swooleEvent->add(1, EventInterface::EV_TIMER, $fuc);
                if ($r) {
                    break;
                }
            }
        }

        $result = $swooleEvent->del($timerId, EventInterface::EV_TIMER);

        $this->assertTrue($result);

        $timerId = $swooleEvent->add(0, EventInterface::EV_TIMER, $fuc = function () {
            echo 'Timer once triggered';
        });
        if (!$timerId) {
            while (true) {
                $r = $swooleEvent->add(0, EventInterface::EV_TIMER, $fuc);
                if ($r) {
                    break;
                }
            }
        }
        $result = $swooleEvent->del($timerId, EventInterface::EV_TIMER);

        $this->assertTrue($result);
    }

    public function testAddAndDelRead()
    {
        $swooleEvent = new SwooleEvent();

        $eventMock = m::mock('alias:Swoole\Event');
        $eventMock->shouldReceive('add')->andReturnUsing(function ($stream, $readCallback, $writeCallback, $event) {
            $this->assertTrue(is_resource($stream));
            $this->assertTrue(is_callable($readCallback));
            $this->assertTrue(is_int($event));
            $this->assertNull($writeCallback);

            return true;
        });
        $eventMock->shouldReceive('set')->andReturnUsing(function ($stream, $readCallback, $writeCallback, $event) {
            $this->assertTrue(is_resource($stream));
            $this->assertTrue(is_callable($readCallback));
            $this->assertTrue(is_int($event));
            $this->assertNull($writeCallback);

            return true;
        });
        $eventMock->shouldReceive('isset')->andReturn(false);
        // false
        $result = $swooleEvent->add('123', EventInterface::EV_READ, function () {
            echo 'Read event error';
        });
        $this->assertFalse($result);
        // Event::add
        $stream = fopen('php://memory', 'r+');
        $result = $swooleEvent->add($stream, EventInterface::EV_READ, function () {
            echo 'Read event1';
        });
        $this->assertTrue($result);
        // Event::set
        $eventMock->shouldReceive('isset')->andReturn(true);
        $stream2 = fopen('php://memory', 'r+');
        $result = $swooleEvent->add($stream2, EventInterface::EV_READ, function () {
            echo 'Read event2';
        });
        $this->assertTrue($result);

        // ----

        $eventMock->shouldReceive('del')->andReturn(true);
        $eventMock->shouldReceive('set')->andReturnUsing(function ($stream, $readCallback, $writeCallback, $event) {
            $this->assertTrue(is_resource($stream));
            $this->assertTrue(is_int($event));
            $this->assertNull($writeCallback);
            $this->assertNull($readCallback);

            return true;
        });
        // Event::del
        $eventMock->shouldReceive('isset')->andReturn(false);
        $result = $swooleEvent->del($stream, EventInterface::EV_READ);
        $this->assertTrue($result);
        // Event::set
        $eventMock->shouldReceive('isset')->andReturn(false);


        $result = $swooleEvent->del($stream2, EventInterface::EV_READ);
        $this->assertTrue($result);
        // false
        $result = $swooleEvent->del('123', EventInterface::EV_READ);
        $this->assertFalse($result);
        fclose($stream);
    }

    public function testAddAndDelWrite()
    {
        $swooleEvent = new SwooleEvent();

        $eventMock = m::mock('alias:Swoole\Event');
        $eventMock->shouldReceive('add')->andReturnUsing(function ($stream, $readCallback, $writeCallback, $event) {
            $this->assertTrue(is_resource($stream));
            $this->assertTrue(is_callable($writeCallback));
            $this->assertTrue(is_int($event));
            $this->assertNull($readCallback);

            return true;
        });
        $eventMock->shouldReceive('set')->andReturnUsing(function ($stream, $readCallback, $writeCallback, $event) {
            $this->assertTrue(is_resource($stream));
            $this->assertTrue(is_callable($writeCallback));
            $this->assertTrue(is_int($event));
            $this->assertNull($readCallback);

            return true;
        });

        $eventMock->shouldReceive('isset')->andReturn(false);
        // false
        $result = $swooleEvent->add('321', EventInterface::EV_WRITE, function () {
            echo 'Write event error';
        });
        $this->assertFalse($result);
        // Event::add
        $eventMock->shouldReceive('isset')->andReturn(false);
        $stream = fopen('php://memory', 'w+');
        $result = $swooleEvent->add($stream, EventInterface::EV_WRITE, function () {
            echo 'Write event1';
        });
        $this->assertTrue($result);
        // Event::set
        $eventMock->shouldReceive('isset')->andReturn(true);
        $stream2 = fopen('php://memory', 'w+');
        $result = $swooleEvent->add($stream2, EventInterface::EV_WRITE, function () {
            echo 'Write event2';
        });
        $this->assertTrue($result);

        // -----

        $eventMock->shouldReceive('del')->andReturn(true);
        $eventMock->shouldReceive('set')->andReturnUsing(function ($stream, $readCallback, $writeCallback, $event) {
            $this->assertTrue(is_resource($stream));
            $this->assertTrue(is_int($event));
            $this->assertNull($writeCallback);
            $this->assertNull($readCallback);

            return true;
        });
        // Event::del
        $eventMock->shouldReceive('isset')->andReturn(false);
        $result = $swooleEvent->del($stream, EventInterface::EV_WRITE);
        $this->assertTrue($result);
        // Event::set
        $eventMock->shouldReceive('isset')->andReturn(true);
        $result = $swooleEvent->del($stream, EventInterface::EV_WRITE);
        $this->assertTrue($result);
        // false
        $result = $swooleEvent->del('321', EventInterface::EV_WRITE);
        $this->assertFalse($result);
        fclose($stream);
    }

    public function testLoop()
    {
        $this->markTestSkipped('loop will exit()');

        $this->expectException(\RuntimeException::class);

        $swooleEvent = new SwooleEvent(true);
        $eventMock = m::mock('alias:Swoole\Event');
        $eventMock->shouldReceive('wait')->andReturn(true);

        $this->expectOutputString('');
        $swooleEvent->loop();
    }

    public function testDestroy()
    {
        $swooleEvent = new SwooleEvent();

        $eventMock = m::mock('alias:Swoole\Event');
        $eventMock->shouldReceive('exit')->andReturn(true);

        $eventMock = m::mock('alias:Swoole\Coroutine');
        $eventMock->shouldReceive('cancel')->andReturnUsing(function ($id) {
            $this->assertTrue(is_int($id));
        });
        $eventMock->shouldReceive('listCoroutines')->andReturn([
            1, 2
        ]);

        $swooleEvent->destroy();

        $this->assertEmpty($swooleEvent->getTimerCount());
    }

    public function testClearAllTimer()
    {
        $swooleEvent = new SwooleEvent();

        $timerMock = m::mock('alias:Swoole\Timer');
        $timerMock->shouldReceive('tick')->andReturn(1);
        $timerMock->shouldReceive('clear')->andReturn(true);
        $timerMock = m::mock('alias:Swoole\Coroutine');
        $timerMock->shouldReceive('create')->andReturn(2);
        $timerMock->shouldReceive('cancel')->andReturn(true);

        $swooleEvent->add(1, EventInterface::EV_TIMER, function () {
            echo 'Timer triggered';
        });

        $this->assertEquals(1, $swooleEvent->getTimerCount());

        $swooleEvent->add(0, EventInterface::EV_TIMER, function () {
            echo 'Timer once triggered';
        });

        $this->assertEquals(2, $swooleEvent->getTimerCount());

        $swooleEvent->clearAllTimer();

        $this->assertEquals(0, $swooleEvent->getTimerCount());
    }

    public function testUnknownEvent()
    {
        $swooleEvent = new SwooleEvent();
        $this->assertNull($swooleEvent->add(1, 0xFFFFFFFF, function () {
        }));

        $this->assertNull($swooleEvent->del(1, 0xFFFFFFFF));
    }
}
