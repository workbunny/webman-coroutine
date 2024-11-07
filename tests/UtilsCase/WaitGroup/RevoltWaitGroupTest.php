<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\RevoltHandler;
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testDone()
    {
        $eventLoopMock = Mockery::mock('alias:' . RevoltHandler::class);
        $eventLoopMock->shouldReceive('wakeup')->once()->andReturnUsing(function ($event) {
            $this->assertTrue(str_starts_with($event, 'waitGroup.wait.'));
        });
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testWait()
    {
        $eventLoopMock = Mockery::mock('alias:' . RevoltHandler::class);
        $eventLoopMock->shouldReceive('sleep')->once()->andReturnUsing(function ($timeout, $event) {
            $this->assertTrue(str_starts_with($event, 'waitGroup.wait.'));
        });
        $eventLoopMock->shouldReceive('wakeup')->andReturnUsing(function ($event) {
            $this->assertTrue(str_starts_with($event, 'waitGroup.wait.'));
        });
        $wg = new RevoltWaitGroup();
        $wg->add();
        $wg->done();
        $wg->wait();
        $this->assertEquals(0, $wg->count());

        $eventLoopMock->shouldReceive('sleep')->once()->andReturnUsing(function ($timeout, $event) {
            $this->assertTrue(str_starts_with($event, 'waitGroup.wait.'));
        });
        $this->expectException(TimeoutException::class);
        $wg->add();
        $wg->wait(1);
        $this->assertEquals(1, $wg->count());
    }
}
