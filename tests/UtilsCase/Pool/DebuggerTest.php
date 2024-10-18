<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Pool;

use stdClass;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\PoolDebuggerException;
use Workbunny\WebmanCoroutine\Utils\Pool\Debugger;

class DebuggerTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Debugger::delSeen();
    }

    protected function _generateNestedArray($depth): array
    {
        if ($depth <= 0) {
            return [];
        }
        return ['level' => $depth, 'next' => $this->_generateNestedArray($depth - 1)];
    }

    public function testCloneValidateWithArray()
    {
        // array
        $array = [
            1,
            '2',
            [
                3.1,
                [
                    4
                ]
            ]
        ];
        $result = Debugger::run($array);
        $this->assertTrue($result);

        // array has object
        $array = [
            1,
            new stdClass(),
            [
                3.1,
                [
                    4
                ]
            ]
        ];
        $result = Debugger::run($array);
        $this->assertTrue($result);
        // double check
        $result = Debugger::run($array);
        $this->assertTrue($result);

        // map
        $array = [
            'a' => 1,
            'b' => '2',
            'c' => [
                3.1,
                [
                    'd' => 4
                ]
            ]
        ];
        $result = Debugger::run($array);
        $this->assertTrue($result);

        // map has object
        $array = [
            'a' => 1,
            'b' => new stdClass(),
            'c' => [
                3.1,
                [
                    'd' => 4
                ]
            ]
        ];
        $result = Debugger::run($array);
        $this->assertTrue($result);
        // double check
        $result = Debugger::run($array);
        $this->assertTrue($result);
    }

    public function testCloneValidateWithArrayException()
    {
        // array has resource
        $array = [
            1,
            fopen('php://memory', 'w+'),
            [
                3.1,
                [
                    4
                ]
            ]
        ];
        try {
            Debugger::run($array);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }

        // array->array has resource
        $array = [
            1,
            '3',
            [
                fopen('php://memory', 'w+'),
                [
                    4
                ]
            ]
        ];
        try {
            Debugger::run($array);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }

        // array->object has resource
        $object = new stdClass();
        $object->resource = fopen('php://memory', 'w+');
        $array = [
            1,
            '3',
            [
                $object,
                [
                    4
                ]
            ]
        ];
        try {
            Debugger::run($array);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::run($array);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }

        // array->object->array has resource
        $object = new stdClass();
        $object->array = [fopen('php://memory', 'w+')];
        $array = [
            1,
            '3',
            [
                $object,
                [
                    4
                ]
            ]
        ];
        try {
            Debugger::run($array);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::run($array);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
    }

    public function testCloneValidateWithObject()
    {
        // object
        $object = new stdClass();
        $object->prop = 'value';
        $result = Debugger::run($object);
        $this->assertTrue($result);

        // object->array
        $object = new stdClass();
        $object->prop = [1, 2, 3];
        $result = Debugger::run($object);
        $this->assertTrue($result);

        // object->self
        $object = new stdClass();
        $object->self = $object;
        $result = Debugger::run($object);
        $this->assertTrue($result);

        // object->object
        $object = new stdClass();
        $object2 = new stdClass();
        $object->self = $object2;
        $result = Debugger::run($object);
        $this->assertTrue($result);
    }

    public function testCloneValidateWithObjectResourceException()
    {
        // object->array has resource
        $object = new stdClass();
        $object->prop = [1, fopen('php://memory', 'w+'), 3];
        try {
            Debugger::run($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::run($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }

        // object->resource
        $object = new stdClass();
        $object->prop = fopen('php://memory', 'w+');
        try {
            Debugger::run($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::run($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }

        // object->object->resource
        $object = new stdClass();
        $object2 = new stdClass();
        $object2->resouce = fopen('php://memory', 'w+');
        $object->prop = $object2;
        try {
            Debugger::run($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::run($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
    }

    public function testCloneValidateWithObjectStaticProperty()
    {
        // string
        $object = new class {
            public static $string = '1';
        };
        $res = Debugger::run($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::run($object);
        $this->assertTrue($res);

        // int
        $object = new class {
            public static $int = 1;
        };
        $res = Debugger::run($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::run($object);
        $this->assertTrue($res);

        // float
        $object = new class {
            public static $float = 1.1;
        };
        $res = Debugger::run($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::run($object);
        $this->assertTrue($res);

        // bool
        $object = new class {
            public static $bool = true;
        };
        $res = Debugger::run($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::run($object);
        $this->assertTrue($res);

        // null
        $object = new class {
            public static $null = null;
        };
        $res = Debugger::run($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::run($object);
        $this->assertTrue($res);
    }

    public function testCloneValidateWithObjectStaticPropertyException()
    {
        // array
        $object = new class {
            public static $arr = [1, 2, 3];
        };
        try {
            Debugger::run($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_STATIC_ARRAY, $e->getCode());
            $this->assertEquals('Value can not be cloned [static array]. ', $e->getMessage());
        }

        // resource
        $object = new class {
            public static $resource = null;

            public function __construct()
            {
                self::$resource = fopen('php://memory', 'w+');
            }
        };
        try {
            Debugger::run($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
    }

    public function testCloneValidateException()
    {
        // string
        try {
            Debugger::run('123');
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [string]. ', $e->getMessage());
        }

        // int
        try {
            Debugger::run(1);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [integer]. ', $e->getMessage());
        }

        // float
        try {
            Debugger::run(1.1);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [double]. ', $e->getMessage());
        }

        // boolean
        try {
            Debugger::run(true);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [boolean]. ', $e->getMessage());
        }

        try {
            Debugger::run(null);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [NULL]. ', $e->getMessage());
        }
    }

    public function testSeenMechanism()
    {
        $object = new stdClass();
        $object->prop = 'value';

        Debugger::run($object);
        $seen = Debugger::getSeen();
        $this->assertNotEquals(0, $seen->count());

        unset($object);

        $this->assertEquals(0, $seen->count());
    }
}
