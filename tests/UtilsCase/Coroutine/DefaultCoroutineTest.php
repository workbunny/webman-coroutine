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
        $id = null;
        $func = function ($coroutineId) use (&$id, &$executed) {
            $executed = true;
            $id = $coroutineId;
        };
        $coroutine = new DefaultCoroutine($func);
        $this->assertTrue($executed);
        $this->assertNull($coroutine->id());
        $this->assertEquals(spl_object_hash($func), $id);
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
        $this->assertNull($coroutine->id());
        $this->assertNull($coroutine->origin());
    }
}
