<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\SwooleWaitGroup;

class SwooleWaitGroupTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testAdd()
    {
        $wg = new SwooleWaitGroup();
        $this->assertTrue($wg->add());
        $this->assertEquals(1, $wg->count());
    }

    public function testDone()
    {
        $wg = new SwooleWaitGroup();
        $wg->add();
        $this->assertTrue($wg->done());
        $this->assertEquals(0, $wg->count());
    }

    public function testCount()
    {
        $wg = new SwooleWaitGroup();
        $this->assertEquals(0, $wg->count());
        $wg->add();
        $this->assertEquals(1, $wg->count());
    }

    public function testWait()
    {
        $wg = new SwooleWaitGroup();
        $wg->add();

        // 使用 Mockery 模拟 sleep()
        $coMock = Mockery::mock('alias:sleep');
        $coMock->shouldReceive('sleep')->andReturnNull();

        $wg->done();
        $wg->wait();
        $this->assertEquals(0, $wg->count());
    }
}
