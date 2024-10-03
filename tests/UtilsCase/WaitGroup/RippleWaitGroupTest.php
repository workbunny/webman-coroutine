<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\RippleWaitGroup;

class RippleWaitGroupTest extends TestCase
{
    protected function tearDown(): void
    {
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
        $partialMock = Mockery::mock(RippleWaitGroup::class, [1])->makePartial();
        $partialMock->add();
        $partialMock->shouldAllowMockingProtectedMethods()->shouldReceive('_sleep')->andReturnNull();

        $partialMock->done();
        $partialMock->wait();
        $this->assertEquals(0, $partialMock->count());
    }
}
