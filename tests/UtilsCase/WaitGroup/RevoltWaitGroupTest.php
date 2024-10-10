<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\WaitGroup;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\RevoltWaitGroup;

/**
 * @runTestsInSeparateProcesses
 */
class RevoltWaitGroupTest extends TestCase
{
    protected function tearDown(): void
    {
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
        Mockery::mock('Workbunny\WebmanCoroutine\Handlers\RevoltHandler')
            ->shouldReceive('sleep')->andReturnNull();
        $wg = new RevoltWaitGroup();
        $wg->add();
        $wg->done();
        $wg->wait();
        $this->assertEquals(0, $wg->count());
    }
}
