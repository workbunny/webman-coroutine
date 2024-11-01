<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Coroutine;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\RippleCoroutine;

class RippleCoroutineTest extends TestCase
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

            return 'testConstruct';
        };
        $reject = $result = null;

        // 模拟构造
        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_getSuspension')
            ->andReturn($suspension = Mockery::mock('alias:Revolt\EventLoop\Suspension'));
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function ($closure) use ($coroutine, &$result, &$reject) {
                // 模拟发生协程执行
                call_user_func($closure, function ($res) use (&$result) {
                    $result = $res;
                }, function ($rj) use (&$reject) {
                    $reject = $rj;
                });

                // 模拟返回
                return Mockery::mock('alias:Ripple\Promise');
            });

        // 模拟构造函数执行
        $coroutine->__construct($func);

        $this->assertTrue($executed);
        $this->assertEquals('testConstruct', $result);
        $this->assertNull($reject);
        $this->assertNull($coroutine->origin());
        $this->assertNull($coroutine->id());
        $this->assertNotNull($id);
        $this->assertEquals(spl_object_hash($suspension), $id);

        $executed = false;
        $id = null;
        $func = function ($coroutineId) use (&$id, &$executed) {
            $executed = true;
            $id = $coroutineId;
            throw new \Exception('testConstruct');
        };
        $reject = $result = null;

        // 模拟构造函数执行
        $coroutine->__construct($func);

        $this->assertTrue($executed);
        $this->assertInstanceOf(\Exception::class, $reject);
        $this->assertNull($result);
        $this->assertNull($coroutine->origin());
        $this->assertNull($coroutine->id());
        $this->assertNotNull($id);
        $this->assertEquals(spl_object_hash($suspension), $id);
    }

    public function testDestruct()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };

        $reject = $result = null;
        // 模拟构造
        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_getSuspension')
            ->andReturn($suspension = Mockery::mock('alias:Revolt\EventLoop\Suspension'));
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function ($closure) use ($coroutine, &$result, &$reject) {
                // 模拟发生协程执行
                call_user_func($closure, function ($res) use (&$result) {
                    $result = $res;
                }, function ($rj) use (&$reject) {
                    $reject = $rj;
                });

                // 模拟返回
                return Mockery::mock('alias:Ripple\Promise');
            });

        // 模拟构造函数执行
        $coroutine->__construct($func);
        // 模拟析构函数执行
        $method = new \ReflectionMethod(RippleCoroutine::class, '__destruct');
        $method->invoke($coroutine);
        //
        //        $coroutine->__destruct();
        // 正常执行无报错
        $this->assertTrue(true);
    }

    public function testOrigin()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };
        $reject = $result = null;
        // 模拟构造
        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_getSuspension')
            ->andReturn($suspension = Mockery::mock('alias:Revolt\EventLoop\Suspension'));
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function ($closure) use ($coroutine, &$result, &$reject) {
                // 模拟发生协程执行
                call_user_func($closure, function ($res) use (&$result) {
                    $result = $res;
                }, function ($rj) use (&$reject) {
                    $reject = $rj;
                });

                // 模拟返回
                return Mockery::mock('alias:Ripple\Promise');
            });

        // 模拟构造函数执行
        $coroutine->__construct($func);

        $this->assertNull($coroutine->origin());
    }

    public function testId()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };
        $reject = $result = null;
        // 模拟构造
        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_getSuspension')
            ->andReturn($suspension = Mockery::mock('alias:Revolt\EventLoop\Suspension'));
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function ($closure) use ($coroutine, &$result, &$reject) {
                // 模拟发生协程执行
                call_user_func($closure, function ($res) use (&$result) {
                    $result = $res;
                }, function ($rj) use (&$reject) {
                    $reject = $rj;
                });

                // 模拟返回
                return Mockery::mock('alias:Ripple\Promise');
            });

        // 模拟构造函数执行
        $coroutine->__construct($func);

        $this->assertNull($coroutine->id());
    }
}
