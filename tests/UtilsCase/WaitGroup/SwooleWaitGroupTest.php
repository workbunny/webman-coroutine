<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\SwooleWaitGroup;

class SwooleWaitGroupTest extends TestCase
{
    protected int $_count = 0;

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
        $this->_count = 0;
    }

    public function testAdd()
    {
        $swooleMock = Mockery::mock('alias:Swoole\Coroutine\WaitGroup');
        $swooleMock->shouldReceive('add')->with(1)->andReturnUsing(function ($delta) {
            // 模拟增加计数
            $this->_count += $delta;
        });
        $swooleMock->shouldReceive('count')->andReturnUsing(function () {
            // 模拟增加计数
            return $this->_count;
        });

        $wg = new SwooleWaitGroup();
        $reflection = new \ReflectionClass($wg);
        $property = $reflection->getProperty('_waitGroup');
        $property->setAccessible(true);
        $property->setValue($wg, $swooleMock);

        $this->assertTrue($wg->add());
        $this->assertEquals(1, $wg->count());
    }

    public function testDone()
    {
        $swooleMock = Mockery::mock('alias:Swoole\Coroutine\WaitGroup');
        $swooleMock->shouldReceive('add')->with(1)->andReturnUsing(function ($delta) {
            // 模拟增加计数
            $this->_count += $delta;
        });
        $swooleMock->shouldReceive('done')->andReturnUsing(function () {
            // 模拟减少计数
            $this->_count--;
        });
        $swooleMock->shouldReceive('count')->andReturnUsing(function () {
            // 模拟增加计数
            return $this->_count;
        });

        $wg = new SwooleWaitGroup();
        $reflection = new \ReflectionClass($wg);
        $property = $reflection->getProperty('_waitGroup');
        $property->setAccessible(true);
        $property->setValue($wg, $swooleMock);

        $wg->add();
        $this->assertTrue($wg->done());
        $this->assertEquals(0, $wg->count());
    }

    public function testCount()
    {
        $swooleMock = Mockery::mock('alias:Swoole\Coroutine\WaitGroup');
        $swooleMock->shouldReceive('add')->with(1)->andReturnUsing(function ($delta) {
            // 模拟增加计数
            $this->_count += $delta;
        });
        $swooleMock->shouldReceive('count')->andReturnUsing(function () {
            // 模拟增加计数
            return $this->_count;
        });
        $wg = new SwooleWaitGroup();
        $reflection = new \ReflectionClass($wg);
        $property = $reflection->getProperty('_waitGroup');
        $property->setAccessible(true);
        $property->setValue($wg, $swooleMock);

        $this->assertEquals(0, $wg->count());
        $wg->add();
        $this->assertEquals(1, $wg->count());
    }

    public function testWait()
    {
        $swooleMock = Mockery::mock('alias:Swoole\Coroutine\WaitGroup');
        $swooleMock->shouldReceive('add')->with(1)->andReturnUsing(function ($delta) {
            // 模拟增加计数
            $this->_count += $delta;
        });
        $swooleMock->shouldReceive('done')->andReturnUsing(function () {
            // 模拟减少计数
            $this->_count--;
        });
        $swooleMock->shouldReceive('count')->andReturnUsing(function () {
            // 模拟增加计数
            return $this->_count;
        });
        $swooleMock->shouldReceive('wait')->with(-1)->andReturnNull();

        $wg = new SwooleWaitGroup();
        $reflection = new \ReflectionClass($wg);
        $property = $reflection->getProperty('_waitGroup');
        $property->setAccessible(true);
        $property->setValue($wg, $swooleMock);

        $wg->add();
        $wg->done();
        $wg->wait();
        $this->assertEquals(0, $wg->count());
    }
}
