<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\SwowWaitGroup;

class SwowWaitGroupTest extends TestCase
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
        $swowMock = Mockery::mock('alias:Swow\Sync\WaitGroup');
        $swowMock->shouldReceive('add')->with(1)->andReturnUsing(function ($delta) {
            // 模拟增加计数
            $this->_count += $delta;
        });

        $wg = new SwowWaitGroup();
        $reflection = new \ReflectionClass($wg);
        $property = $reflection->getProperty('_waitGroup');
        $property->setAccessible(true);
        $property->setValue($wg, $swowMock);

        $this->assertTrue($wg->add());
        $this->assertEquals(1, $wg->count());
    }

    public function testDone()
    {
        $swowMock = Mockery::mock('alias:Swow\Sync\WaitGroup');
        $swowMock->shouldReceive('add')->with(1)->andReturnUsing(function ($delta) {
            // 模拟增加计数
            $this->_count += $delta;
        });
        $swowMock->shouldReceive('done')->andReturnUsing(function () {
            // 模拟减少计数
            $this->_count--;
        });

        $wg = new SwowWaitGroup();
        $reflection = new \ReflectionClass($wg);
        $property = $reflection->getProperty('_waitGroup');
        $property->setAccessible(true);
        $property->setValue($wg, $swowMock);

        $wg->add();
        $this->assertTrue($wg->done());
        $this->assertEquals(0, $wg->count());
    }

    public function testCount()
    {
        $swowMock = Mockery::mock('alias:Swow\Sync\WaitGroup');
        $swowMock->shouldReceive('add')->with(1)->andReturnUsing(function ($delta) {
            // 模拟增加计数
            $this->_count += $delta;
        });
        $wg = new SwowWaitGroup();
        $reflection = new \ReflectionClass($wg);
        $property = $reflection->getProperty('_waitGroup');
        $property->setAccessible(true);
        $property->setValue($wg, $swowMock);

        $this->assertEquals(0, $wg->count());
        $wg->add();
        $this->assertEquals(1, $wg->count());
    }

    public function testWait()
    {
        $swowMock = Mockery::mock('alias:Swow\Sync\WaitGroup');
        $swowMock->shouldReceive('add')->with(1)->andReturnUsing(function ($delta) {
            // 模拟增加计数
            $this->_count += $delta;
        });
        $swowMock->shouldReceive('done')->andReturnUsing(function () {
            // 模拟减少计数
            $this->_count--;
        });
        $swowMock->shouldReceive('wait')->with(-1)->andReturnNull();

        $wg = new SwowWaitGroup();
        $reflection = new \ReflectionClass($wg);
        $property = $reflection->getProperty('_waitGroup');
        $property->setAccessible(true);
        $property->setValue($wg, $swowMock);

        $wg->add();
        $wg->done();
        $wg->wait();
        $this->assertEquals(0, $wg->count());
    }
}
