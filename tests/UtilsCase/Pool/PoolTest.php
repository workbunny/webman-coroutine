<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Pool;

use Mockery;
use PHPUnit\Framework\TestCase;
use \stdClass;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Utils\Pool\Pool;
use Workbunny\WebmanCoroutine\Exceptions\PoolException;

class PoolTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * 递归比较数组
     *
     * @param array $array1
     * @param array $array2
     * @return bool
     */
    protected function _arraysAreDifferent(array $array1, array $array2): bool
    {
        if (count($array1) !== count($array2)) {
            return true;
        }
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!is_array($array2[$key]) || $this->_arraysAreDifferent($value, $array2[$key])) {
                    return true;
                }
            } elseif (is_object($value)) {
                if (!is_object($array2[$key]) || spl_object_id($value) === spl_object_id($array2[$key])) {
                    return true;
                }
            } else {
                if ($value !== $array2[$key]) {
                    return true;
                }
            }
        }
        return false;
    }

    public function testCreatePoolWithStdClass()
    {
        $element = new stdClass();
        $element->property = 'value';
        $pools = Pool::create(__METHOD__, 2, $element);

        $this->assertCount(2, $pools);
        $this->assertInstanceOf(Pool::class, $pools[1]);
        $this->assertInstanceOf(Pool::class, $pools[2]);
        // 验证克隆是否生效
        $this->assertNotSame($element, $pools[1]->getElement());
        $this->assertEquals('value', $pools[1]->getElement()->property);
        $this->assertTrue($pools[1]->isClone());

        // already
        $this->expectException(PoolException::class);
        Pool::create(__METHOD__, 2, $element);

        // 引用
        $reference = new stdClass();
        $reference->property = 'reference';
        $pools = Pool::create(__METHOD__ . '->reference', 1, $reference, false);
        $this->assertCount(1, $pools);
        $this->assertInstanceOf(Pool::class, $pools[1]);
        // 验证引用
        $this->assertEquals(spl_object_id($reference), $pools[1]->getElement());
        $this->assertEquals('reference', $pools[1]->getElement()->property);
        $this->assertFalse($pools[1]->isClone());

    }

    public function testCreatePoolWithArray()
    {
        $element = ['key' => 'value'];
        $pools = Pool::create(__METHOD__, 2, $element);

        $this->assertCount(2, $pools);
        $this->assertInstanceOf(Pool::class, $pools[1]);
        $this->assertInstanceOf(Pool::class, $pools[2]);

        $element['key'] = 1;
        // 验证数组是否正确克隆
        $this->assertTrue($this->_arraysAreDifferent($element, $pools[1]->getElement()));
        $this->assertEquals('value', $pools[1]->getElement()['key']);
    }

    public function testCreatePoolWithResource()
    {
        $element = fopen('php://memory', 'r');
        $pools = Pool::create(__METHOD__, 2, $element);

        $this->assertCount(2, $pools);
        $this->assertInstanceOf(Pool::class, $pools[1]);
        $this->assertInstanceOf(Pool::class, $pools[2]);

        // 验证资源类型
        $this->assertIsResource($pools[1]->getElement());
        fclose($element);
    }

    public function testCreatePoolThrowsException()
    {
        $this->expectException(PoolException::class);
        $name = __METHOD__;
        $this->expectExceptionMessage("Pools $name already exists.");

        $element = new stdClass();
        Pool::create(__METHOD__, 2, $element);
        Pool::create(__METHOD__, 2, $element);
    }

    public function testGetPool()
    {
        $element = new stdClass();
        Pool::create(__METHOD__, 2, $element);

        $pool = Pool::get(__METHOD__, 1);
        $this->assertInstanceOf(Pool::class, $pool);
        $this->assertEquals(__METHOD__, $pool->getName());
        $this->assertEquals(1, $pool->getIndex());
    }

    public function testIdlePool()
    {
        $element = new stdClass();
        Pool::create(__METHOD__, 2, $element);

        $pool = Pool::idle(__METHOD__);
        $this->assertInstanceOf(Pool::class, $pool);
        $this->assertTrue($pool->isIdle());
    }

    public function testWaitForIdle()
    {
        $element = new stdClass();
        Pool::create(__METHOD__, 2, $element);

        $result = Pool::waitForIdle(__METHOD__, function ($pool) {
            return $pool->getName();
        });

        $this->assertEquals(__METHOD__, $result);
    }

    public function testDestroyPool()
    {
        $element = new stdClass();
        Pool::create(__METHOD__, 2, $element);

        Pool::destroy(__METHOD__, 1);
        $this->assertNull(Pool::get(__METHOD__, 1));
    }

    public function testWaitForIdleTimeout()
    {
        $element = new stdClass();
        Pool::create(__METHOD__, 1, $element);

        // 模拟上锁
        Pool::get(__METHOD__, 1)->setIdle(false);

        $this->assertNull($element->value ?? null);
        $this->expectException(TimeoutException::class);
        Pool::waitForIdle(__METHOD__, function (Pool $pool) {
            sleep(2);
            $pool->getElement()->value = 'test';
        }, 1);
        $this->assertNull($element->value ?? null);
    }

    public function testDeepCopyArrayWithNestedObjects()
    {
        $reflection = new \ReflectionClass(Pool::class);
        $method = $reflection->getMethod('_deepCopyArray');
        $method->setAccessible(true);

        $array = ['foo' => ['bar' => new stdClass()]];
        $array['foo']['bar']->value = 'test';

        $copy = $method->invokeArgs(null, [$array]);

        $this->assertEquals('test', $copy['foo']['bar']->value);
        $this->assertNotSame($array['foo']['bar'], $copy['foo']['bar']);
    }

    public function testPoolDestruct()
    {
        $element = new stdClass();
        Pool::create(__METHOD__, 1, $element);
        $pool = Pool::get(__METHOD__, 1);
        // 销毁池
        Pool::destroy(__METHOD__, null);
        // 销毁引用
        unset($pool);

        $this->assertNull(Pool::get(__METHOD__, 1));
    }

    public function testWait()
    {
        $element = new stdClass();
        Pool::create(__METHOD__, 1, $element);
        $pool = Pool::get(__METHOD__, 1);

        $pool->wait(function ($p) {
            $this->assertFalse($p->isIdle());
        });

        $this->assertTrue($pool->isIdle());
    }
}

