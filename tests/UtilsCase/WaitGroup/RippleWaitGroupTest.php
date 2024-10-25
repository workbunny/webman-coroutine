<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\RippleWaitGroup;

class RippleWaitGroupTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testAdd()
    {
        $wg = new RippleWaitGroup();
        $this->assertTrue($wg->add());
        $this->assertEquals(1, $wg->count());
    }

    public function testDone()
    {
        $wg = new RippleWaitGroup();
        $wg->add();
        $this->assertTrue($wg->done());
        $this->assertEquals(0, $wg->count());
    }

    public function testCount()
    {
        $wg = new RippleWaitGroup();
        $this->assertEquals(0, $wg->count());
        $wg->add();
        $this->assertEquals(1, $wg->count());
    }

    public function testWait()
    {
        $suspensionMock = Mockery::mock('alias:\Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->andReturnNull();
        $partialMock = Mockery::mock(RippleWaitGroup::class, [1])->makePartial();
        $partialMock->add();
        $partialMock->shouldAllowMockingProtectedMethods()->shouldReceive('_sleep')->andReturnNull();
        $partialMock->shouldAllowMockingProtectedMethods()->shouldReceive('_getSuspension')->andReturn($suspensionMock);


        $partialMock->done();
        $partialMock->wait();
        $this->assertEquals(0, $partialMock->count());

        $partialMock->shouldAllowMockingProtectedMethods()->shouldReceive('_delay')->andReturnUsing(function ($callback, $timeout) {
            $this->assertEquals(1, $timeout);
            $callback();
            return 'delayEventId';
        });
        $partialMock->add();
        $partialMock->wait(1);
        $this->assertEquals(1, $partialMock->count());
    }
}
