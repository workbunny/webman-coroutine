<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Coroutine;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\KilledException;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\RevoltCoroutine;

class RevoltCoroutineTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testConstruct()
    {
        $executed = false;
        $id = null;
        $func = function ($coroutineId) use (&$id, &$executed) {
            $executed = true;
            $id = $coroutineId;
        };

        // mock
        $callback = null;
        $suspensionMock = Mockery::mock('alias:Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->once()->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->once()->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->once()->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->once()->andReturnUsing(function ($closure) use (&$callback) {
            $closure();
            $callback = true;
        });

        $coroutine = new RevoltCoroutine($func);

        $this->assertTrue($callback);
        $this->assertTrue($executed);
        $this->assertNull($coroutine->origin());
        $this->assertNull($coroutine->id());
        $this->assertNotNull($id);
        $this->assertEquals(spl_object_hash($suspensionMock), $id);
    }

    public function testDestruct()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };

        // mock
        $callback = null;
        $suspensionMock = Mockery::mock('alias:Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->once()->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->once()->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->once()->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->once()->andReturnUsing(function ($closure) use (&$callback) {
            $closure();
            $callback = true;
        });

        $coroutine = new RevoltCoroutine($func);
        $coroutine->__destruct();

        $this->assertTrue($callback);
        // 正常执行无报错
        $this->assertTrue(true);
    }

    public function testOrigin()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };
        // mock
        $callback = null;
        $suspensionMock = Mockery::mock('alias:Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->once()->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->once()->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->once()->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->once()->andReturnUsing(function ($closure) use (&$callback) {
            $closure();
            $callback = true;
        });

        $coroutine = new RevoltCoroutine($func);
        $this->assertTrue($callback);
        $this->assertNull($coroutine->origin());
    }

    public function testId()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };
        // mock
        $callback = null;
        $suspensionMock = Mockery::mock('alias:Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->once()->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->once()->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->once()->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->once()->andReturnUsing(function ($closure) use (&$callback) {
            $closure();
            $callback = true;
        });

        $coroutine = new RevoltCoroutine($func);
        $this->assertTrue($callback);
        $this->assertNull($coroutine->id());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testKill()
    {
        $suspensionMock = Mockery::mock('alias:Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('suspend')->once()->andReturnNull();
        $suspensionMock->shouldReceive('throw')->once()->andReturnUsing(function ($throwable) {
            $this->assertInstanceOf(KilledException::class, $throwable);
        });
        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->once()->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->once()->andReturnNull();
        $func = function () {
            // 模拟闭包函数的执行
        };
        $coroutine = new RevoltCoroutine($func);
        $reflection = new \ReflectionClass(RevoltCoroutine::class);
        $property = $reflection->getProperty('_suspension');
        $property->setAccessible(true);
        $property->setValue($coroutine, $suspensionMock);

        $coroutine->kill(new KilledException());
        $this->assertTrue(true);

    }
}
