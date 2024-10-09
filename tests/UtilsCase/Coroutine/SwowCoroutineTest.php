<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Coroutine;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\SwowCoroutine;

class SwowCoroutineTest extends TestCase
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

        // mock 协程创建
        $callback = null;
        $coroutineMock = Mockery::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($closure) use (&$callback, $coroutineMock) {
            $callback = $closure;

            return $coroutineMock;
        });
        $coroutineMock->shouldReceive('getId')->andReturn(123);
        // 构造
        $coroutine = new SwowCoroutine($func);

        $this->assertFalse($executed);
        $this->assertInstanceOf('Swow\Coroutine', $coroutine->origin());
        $this->assertEquals(123, $coroutine->id());
        $this->assertNull($id);

        // 模拟构造后发生协程调度执行
        call_user_func($callback);

        $this->assertTrue($executed);
        $this->assertNull($coroutine->origin());
        $this->assertNull($coroutine->id());
        $this->assertEquals(123, $id);
    }

    public function testDestruct()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };

        // mock 协程创建
        $callback = null;
        $coroutineMock = Mockery::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($closure) use (&$callback, $coroutineMock) {
            $callback = $closure;

            return $coroutineMock;
        });
        $coroutineMock->shouldReceive('getId')->andReturn(123);
        // 构造
        $coroutine = new SwowCoroutine($func);
        // 模拟构造后发生协程调度执行
        call_user_func($callback);
        // 析构
        $coroutine->__destruct();

        $this->assertNull($coroutine->origin());
        $this->assertNull($coroutine->id());
    }

    public function testOrigin()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };

        // mock 协程创建
        $callback = null;
        $coroutineMock = Mockery::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($closure) use (&$callback, $coroutineMock) {
            $callback = $closure;

            return $coroutineMock;
        });
        $coroutineMock->shouldReceive('getId')->andReturn(123);
        // 构造
        $coroutine = new SwowCoroutine($func);

        $this->assertInstanceOf('Swow\Coroutine', $coroutine->origin());

        // 模拟构造后发生协程调度执行
        call_user_func($callback);

        $this->assertNull($coroutine->origin());
    }

    public function testId()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };

        // mock 协程创建
        $callback = null;
        $coroutineMock = Mockery::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function ($closure) use (&$callback, $coroutineMock) {
            $callback = $closure;

            return $coroutineMock;
        });
        $coroutineMock->shouldReceive('getId')->andReturn(123);
        // 构造
        $coroutine = new SwowCoroutine($func);

        $this->assertEquals(123, $coroutine->id());

        // 模拟构造后发生协程调度执行
        call_user_func($callback);

        $this->assertNull($coroutine->id());
    }
}
