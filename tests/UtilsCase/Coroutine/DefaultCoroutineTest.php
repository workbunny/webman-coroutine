<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Coroutine;

use PHPUnit\Framework\TestCase;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\DefaultCoroutine;

class DefaultCoroutineTest extends TestCase
{
    public function testConstruct()
    {
        $executed = false;
        $func = function() use (&$executed) {
            $executed = true;
        };
        $coroutine = new DefaultCoroutine($func);
        $this->assertTrue($executed);
        $this->assertEquals(spl_object_hash($func), $coroutine->id());
    }

    public function testOrigin()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };
        $coroutine = new DefaultCoroutine($func);
        $this->assertNull($coroutine->origin());
    }

    public function testId()
    {
        $func = function() {
            // 模拟闭包函数的执行
        };
        $coroutine = new DefaultCoroutine($func);
        $this->assertIsString($coroutine->id());
        $this->assertEquals(spl_object_hash($func), $coroutine->id());
    }
}
