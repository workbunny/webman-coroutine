<?php

declare(strict_types=1);

namespace UtilsCase\Coroutine;

use Mockery;
use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\SwooleCoroutine;

class SwooleCoroutineTest extends TestCase
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

        $coroutineMock = Mockery::mock('alias:Swoole\Coroutine');
        $coroutineMock->shouldReceive('create')->andReturnUsing(function($closure) use (&$executed) {
            $closure();
            return 123;
        });

        $coroutine = new SwooleCoroutine($func);

        $this->assertTrue($executed);
        $this->assertEquals(123, $coroutine->origin());
        $this->assertEquals(123, $coroutine->id());
    }

    public function testDestruct()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };

        $coroutineMock = Mockery::mock('alias:Swoole\Coroutine');
        $coroutineMock->shouldReceive('create')->andReturnUsing(function($closure) {
            $closure();
            return 123;
        });

        $coroutine = new SwooleCoroutine($func);
        $coroutine->__destruct();

        $this->assertNull($coroutine->origin());
    }

    public function testOrigin()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };

        $coroutineMock = Mockery::mock('alias:Swoole\Coroutine');
        $coroutineMock->shouldReceive('create')->andReturnUsing(function($closure) {
            $closure();
            return 123;
        });

        $coroutine = new SwooleCoroutine($func);

        $this->assertEquals(123, $coroutine->origin());
    }

    public function testId()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };

        $coroutineMock = Mockery::mock('alias:Swoole\Coroutine');
        $coroutineMock->shouldReceive('create')->andReturnUsing(function($closure) {
            $closure();
            return 123;
        });

        $coroutine = new SwooleCoroutine($func);

        $this->assertEquals(123, $coroutine->id());
    }
}
