<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\RevoltWaitGroup;

class RevoltWaitGroupTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testAdd()
    {
        $wg = new RevoltWaitGroup();
        $this->assertTrue($wg->add());
        $this->assertEquals(1, $wg->count());
    }

    public function testDone()
    {
        $wg = new RevoltWaitGroup();
        $wg->add();
        $this->assertTrue($wg->done());
        $this->assertEquals(0, $wg->count());
    }

    public function testCount()
    {
        $wg = new RevoltWaitGroup();
        $this->assertEquals(0, $wg->count());
        $wg->add();
        $this->assertEquals(1, $wg->count());
    }

    public function testWait()
    {
        $suspensionMock = Mockery::mock('alias:\Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:\Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('defer')->andReturnUsing(function ($closure) {
            $closure();
        });
        $eventLoopMock->shouldReceive('delay')->andReturnUsing(function ($timeout, $closure) {
            $closure();
        });
        $wg = new RevoltWaitGroup();
        $wg->add();
        $wg->done();
        $wg->wait();
        $this->assertEquals(0, $wg->count());

        $wg->add();
        $wg->wait(1);
        $this->assertEquals(1, $wg->count());
    }
}
