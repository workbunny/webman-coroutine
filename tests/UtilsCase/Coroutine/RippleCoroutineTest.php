<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Coroutine;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\RippleCoroutine;

class RippleCoroutineTest extends TestCase
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

        // mock async
        $callback = null;
        $promiseMock = Mockery::mock('Psc\Core\Coroutine\Promise');
        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function($closure) use (&$callback, $promiseMock) {
                $callback = $closure;
                return $promiseMock;
            });
        // 模拟构造函数执行
        $constructor = new \ReflectionMethod(RippleCoroutine::class, '__construct');
        $constructor->invoke($coroutine, $func);
        // 模拟构造后协程执行callback
        call_user_func($callback);

        $this->assertTrue($executed);
        $this->assertInstanceOf('Psc\Core\Coroutine\Promise', $coroutine->origin());
        $this->assertIsString($coroutine->id());
        $this->assertEquals(spl_object_hash($promiseMock), $coroutine->id());
        $this->assertEquals($coroutine->id(), $id);
    }

    public function testDestruct()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };

        // mock async
        $callback = null;
        $promiseMock = Mockery::mock('Psc\Core\Coroutine\Promise');
        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function($closure) use (&$callback, $promiseMock) {
                $callback = $closure;
                return $promiseMock;
            });
        // 模拟构造函数执行
        $constructor = new \ReflectionMethod(RippleCoroutine::class, '__construct');
        $constructor->invoke($coroutine, $func);
        // 模拟构造后协程执行callback
        call_user_func($callback);
        // 模拟析构
        $coroutine->__destruct();
        // 正常执行无报错
        $this->assertTrue(true);
    }

    public function testOrigin()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };
        // mock async
        $callback = null;
        $promiseMock = Mockery::mock('Psc\Core\Coroutine\Promise');
        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function($closure) use (&$callback, $promiseMock) {
                $callback = $closure;
                return $promiseMock;
            });
        // 模拟构造函数执行
        $constructor = new \ReflectionMethod(RippleCoroutine::class, '__construct');
        $constructor->invoke($coroutine, $func);
        // 模拟构造后协程执行callback
        call_user_func($callback);

        $this->assertInstanceOf('Psc\Core\Coroutine\Promise', $coroutine->origin());
    }

    public function testId()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };
        // mock async
        $callback = null;
        $promiseMock = Mockery::mock('Psc\Core\Coroutine\Promise');
        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function($closure) use (&$callback, $promiseMock) {
                $callback = $closure;
                return $promiseMock;
            });
        // 模拟构造函数执行
        $constructor = new \ReflectionMethod(RippleCoroutine::class, '__construct');
        $constructor->invoke($coroutine, $func);
        // 模拟构造后协程执行callback
        call_user_func($callback);

        $this->assertIsString($coroutine->id());
    }
}
