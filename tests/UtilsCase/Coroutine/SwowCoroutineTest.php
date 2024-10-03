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
        $func = function() use (&$executed) {
            $executed = true;
        };

        $coroutineMock = Mockery::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function($closure) use ($coroutineMock) {
            $closure();
            return $coroutineMock;
        });
        $coroutineMock->shouldReceive('getId')->andReturn(123);

        $coroutine = new SwowCoroutine($func);

        $this->assertTrue($executed);
        $this->assertInstanceOf('Swow\Coroutine', $coroutine->origin());
        $this->assertEquals(123, $coroutine->id());
    }

    public function testDestruct()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };

        $coroutineMock = Mockery::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function($closure) use ($coroutineMock) {
            $closure();
            return $coroutineMock;
        });
        $coroutineMock->shouldReceive('getId')->andReturn(123);

        $coroutine = new SwowCoroutine($func);
        $coroutine->__destruct();

        $this->assertNull($coroutine->origin());
    }

    public function testOrigin()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };

        $coroutineMock = Mockery::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function($closure) use ($coroutineMock) {
            $closure();
            return $coroutineMock;
        });
        $coroutineMock->shouldReceive('getId')->andReturn(123);

        $coroutine = new SwowCoroutine($func);

        $this->assertInstanceOf('Swow\Coroutine', $coroutine->origin());
    }

    public function testId()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };

        $coroutineMock = Mockery::mock('alias:Swow\Coroutine');
        $coroutineMock->shouldReceive('run')->andReturnUsing(function($closure) use ($coroutineMock) {
            $closure();
            return $coroutineMock;
        });
        $coroutineMock->shouldReceive('getId')->andReturn(123);

        $coroutine = new SwowCoroutine($func);

        $this->assertEquals(123, $coroutine->id());
    }
}
