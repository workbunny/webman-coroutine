<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Coroutine;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\KilledException;
use Workbunny\WebmanCoroutine\Handlers\SwooleHandler;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\SwooleCoroutine;

class SwooleCoroutineTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
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
        $coroutineMock = Mockery::mock('alias:Swoole\Coroutine');
        $coroutineMock->shouldReceive('create')->once()->andReturnUsing(function ($closure) use (&$callback, &$executed) {
            $callback = $closure;

            return 123;
        });
        $coroutineMock->shouldReceive('getCid')->andReturn(123);
        // 构造
        $coroutine = new SwooleCoroutine($func);

        $this->assertFalse($executed);
        $this->assertEquals(123, $coroutine->origin());
        $this->assertEquals(123, $coroutine->id());
        $this->assertNull($id);

        // 模拟构造后发生协程执行
        call_user_func($callback);

        $this->assertTrue($executed);
        $this->assertNull($coroutine->origin());
        $this->assertNull($coroutine->id());
        $this->assertEquals(123, $id);

        // 模拟创建失败，触发sleep协程切换
        $eventLoopMock = Mockery::mock('alias:' . SwooleHandler::class);
        $eventLoopMock->shouldReceive('sleep')->andReturnNull();
        $i = 0;
        $coroutineMock->shouldReceive('create')->andReturnUsing(function ($closure) use (&$i) {
            $i++;
            if ($i > 1) {
                return 123;
            }

            return false;
        });
        // 构造
        new SwooleCoroutine($func);
        $this->assertTrue(true);
    }

    public function testDestruct()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };

        // mock 协程创建
        $callback = null;
        $coroutineMock = Mockery::mock('alias:Swoole\Coroutine');
        $coroutineMock->shouldReceive('create')->andReturnUsing(function ($closure) use (&$callback, &$executed) {
            $callback = $closure;

            return 123;
        });
        $coroutineMock->shouldReceive('getCid')->andReturn(123);
        // 构造
        $coroutine = new SwooleCoroutine($func);
        // 模拟构造后发生协程执行
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
        $coroutineMock = Mockery::mock('alias:Swoole\Coroutine');
        $coroutineMock->shouldReceive('create')->andReturnUsing(function ($closure) use (&$callback, &$executed) {
            $callback = $closure;

            return 123;
        });
        $coroutineMock->shouldReceive('getCid')->andReturn(123);
        // 构造
        $coroutine = new SwooleCoroutine($func);

        $this->assertEquals(123, $coroutine->origin());
        // 模拟构造后发生协程执行
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
        $coroutineMock = Mockery::mock('alias:Swoole\Coroutine');
        $coroutineMock->shouldReceive('create')->andReturnUsing(function ($closure) use (&$callback, &$executed) {
            $callback = $closure;

            return 123;
        });
        $coroutineMock->shouldReceive('getCid')->andReturn(123);
        // 构造
        $coroutine = new SwooleCoroutine($func);

        $this->assertEquals(123, $coroutine->id());
        // 模拟构造后发生协程执行
        call_user_func($callback);

        $this->assertNull($coroutine->id());
    }

    public function testKill()
    {
        $suspensionMock = Mockery::mock('alias:Swoole\Coroutine');
        $suspensionMock->shouldReceive('create')->once()->andReturn(123);
        $suspensionMock->shouldReceive('cancel')->once()->andReturnUsing(function ($id) {
            $this->assertEquals(123, $id);
        });
        $func = function () {
            // 模拟闭包函数的执行
        };
        (new SwooleCoroutine($func))->kill(new KilledException());
        $this->assertTrue(true);
    }
}
