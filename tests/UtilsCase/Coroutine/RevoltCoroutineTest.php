<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Coroutine;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\RevoltCoroutine;

class RevoltCoroutineTest extends TestCase
{
    protected function tearDown(): void
    {
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
        $suspensionMock = Mockery::mock('Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->andReturnUsing(function ($closure) use (&$callback) {
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
        $suspensionMock = Mockery::mock('Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->andReturnUsing(function ($closure) use (&$callback) {
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
        $suspensionMock = Mockery::mock('Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->andReturnUsing(function ($closure) use (&$callback) {
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
        $suspensionMock = Mockery::mock('Revolt\EventLoop\Suspension');
        $suspensionMock->shouldReceive('resume')->andReturnNull();
        $suspensionMock->shouldReceive('suspend')->andReturnNull();

        $eventLoopMock = Mockery::mock('alias:Revolt\EventLoop');
        $eventLoopMock->shouldReceive('getSuspension')->andReturn($suspensionMock);
        $eventLoopMock->shouldReceive('queue')->andReturnUsing(function ($closure) use (&$callback) {
            $closure();
            $callback = true;
        });

        $coroutine = new RevoltCoroutine($func);
        $this->assertTrue($callback);
        $this->assertNull($coroutine->id());
    }
}
