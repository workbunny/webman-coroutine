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
        $func = function() use (&$executed) {
            $executed = true;
        };
        $promiseMock = Mockery::mock('Psc\Core\Coroutine\Promise');

        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function($closure) use ($promiseMock) {
                $closure();
                return $promiseMock;
            });

        // 手动调用构造函数
        $constructor = new \ReflectionMethod(RippleCoroutine::class, '__construct');
        $constructor->invoke($coroutine, $func);

        $this->assertTrue($executed);
        $this->assertInstanceOf('Psc\Core\Coroutine\Promise', $coroutine->origin());
        $this->assertIsString($coroutine->id());
    }

    public function testDestruct()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };
        $promiseMock = Mockery::mock('Psc\Core\Coroutine\Promise');

        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function($closure) use ($promiseMock) {
                $closure();
                return $promiseMock;
            });

        // 手动调用构造函数
        $constructor = new \ReflectionMethod(RippleCoroutine::class, '__construct');
        $constructor->invoke($coroutine, $func);

        $coroutine->__destruct();
        $this->assertTrue(true);
    }

    public function testOrigin()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };
        $promiseMock = Mockery::mock('Psc\Core\Coroutine\Promise');

        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function($closure) use ($promiseMock) {
                $closure();
                return $promiseMock;
            });

        // 手动调用构造函数
        $constructor = new \ReflectionMethod(RippleCoroutine::class, '__construct');
        $constructor->invoke($coroutine, $func);

        $this->assertInstanceOf('Psc\Core\Coroutine\Promise', $coroutine->origin());
    }

    public function testId()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };
        $promiseMock = Mockery::mock('Psc\Core\Coroutine\Promise');

        $coroutine = Mockery::mock(RippleCoroutine::class)->makePartial();
        $coroutine->shouldAllowMockingProtectedMethods()->shouldReceive('_async')
            ->andReturnUsing(function($closure) use ($promiseMock) {
                $closure();
                return $promiseMock;
            });

        // 手动调用构造函数
        $constructor = new \ReflectionMethod(RippleCoroutine::class, '__construct');
        $constructor->invoke($coroutine, $func);

        $this->assertIsString($coroutine->id());
    }
}
