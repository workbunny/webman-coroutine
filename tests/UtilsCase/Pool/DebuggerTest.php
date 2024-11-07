<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Pool;

use stdClass;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Exceptions\PoolDebuggerException;
use Workbunny\WebmanCoroutine\Utils\Pool\Debugger;

class DebuggerTest extends TestCase
{
    protected static ?\WeakMap $seen = null;

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
                    4,
                ],
            ],
        ];
        $result = Debugger::validate($array);
        $this->assertTrue($result);

        // array has object
        $array = [
            1,
            new stdClass(),
            [
                3.1,
                [
                    4,
                ],
            ],
        ];
        $result = Debugger::validate($array);
        $this->assertTrue($result);
        // double check
        $result = Debugger::validate($array);
        $this->assertTrue($result);

        // map
        $array = [
            'a' => 1,
            'b' => '2',
            'c' => [
                3.1,
                [
                    'd' => 4,
                ],
            ],
        ];
        $result = Debugger::validate($array);
        $this->assertTrue($result);

        // map has object
        $array = [
            'a' => 1,
            'b' => new stdClass(),
            'c' => [
                3.1,
                [
                    'd' => 4,
                ],
            ],
        ];
        $result = Debugger::validate($array);
        $this->assertTrue($result);
        // double check
        $result = Debugger::validate($array);
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
                    4,
                ],
            ],
        ];
        try {
            Debugger::validate($array);
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
                    4,
                ],
            ],
        ];
        try {
            Debugger::validate($array);
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
                    4,
                ],
            ],
        ];
        try {
            Debugger::validate($array);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::validate($array);
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
                    4,
                ],
            ],
        ];
        try {
            Debugger::validate($array);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::validate($array);
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
        $result = Debugger::validate($object);
        $this->assertTrue($result);

        // object->array
        $object = new stdClass();
        $object->prop = [1, 2, 3];
        $result = Debugger::validate($object);
        $this->assertTrue($result);

        // object->self
        $object = new stdClass();
        $object->self = $object;
        $result = Debugger::validate($object);
        $this->assertTrue($result);

        // object->object
        $object = new stdClass();
        $object2 = new stdClass();
        $object->self = $object2;
        $result = Debugger::validate($object);
        $this->assertTrue($result);
    }

    public function testCloneValidateWithObjectResourceException()
    {
        // object->array has resource
        $object = new stdClass();
        $object->prop = [1, fopen('php://memory', 'w+'), 3];
        try {
            Debugger::validate($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::validate($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }

        // object->resource
        $object = new stdClass();
        $object->prop = fopen('php://memory', 'w+');
        try {
            Debugger::validate($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::validate($object);
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
            Debugger::validate($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
        try {
            // 在$object生命周期二次调用走缓存
            Debugger::validate($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
    }

    public function testCloneValidateWithObjectStaticProperty()
    {
        // string
        $object = new class () {
            public static $string = '1';
        };
        $res = Debugger::validate($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::validate($object);
        $this->assertTrue($res);

        // int
        $object = new class () {
            public static $int = 1;
        };
        $res = Debugger::validate($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::validate($object);
        $this->assertTrue($res);

        // float
        $object = new class () {
            public static $float = 1.1;
        };
        $res = Debugger::validate($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::validate($object);
        $this->assertTrue($res);

        // bool
        $object = new class () {
            public static $bool = true;
        };
        $res = Debugger::validate($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::validate($object);
        $this->assertTrue($res);

        // null
        $object = new class () {
            public static $null = null;
        };
        $res = Debugger::validate($object);
        $this->assertTrue($res);
        // $object二次调用走缓存
        $res = Debugger::validate($object);
        $this->assertTrue($res);
    }

    public function testCloneValidateWithObjectStaticPropertyException()
    {
        // array
        $object = new class () {
            public static $arr = [1, 2, 3];
        };
        try {
            Debugger::validate($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_STATIC_ARRAY, $e->getCode());
            $this->assertEquals('Value can not be cloned [static array]. ', $e->getMessage());
        }

        // object
        $object = new class () {
            public static $object = null;

            public function __construct()
            {
                self::$object = new stdClass();
            }
        };
        try {
            Debugger::validate($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_STATIC_OBJECT, $e->getCode());
            $this->assertEquals('Value can not be cloned [static object]. ', $e->getMessage());
        }

        // resource
        $object = new class () {
            public static $resource = null;

            public function __construct()
            {
                self::$resource = fopen('php://memory', 'w+');
            }
        };
        try {
            Debugger::validate($object);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_RESOURCE, $e->getCode());
            $this->assertEquals('Value can not be cloned [resource]. ', $e->getMessage());
        }
    }

    public function testCloneValidateException()
    {
        // string
        try {
            Debugger::validate('123');
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [string]. ', $e->getMessage());
        }

        // int
        try {
            Debugger::validate(1);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [integer]. ', $e->getMessage());
        }

        // float
        try {
            Debugger::validate(1.1);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [double]. ', $e->getMessage());
        }

        // boolean
        try {
            Debugger::validate(true);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [boolean]. ', $e->getMessage());
        }

        try {
            Debugger::validate(null);
        } catch (PoolDebuggerException $e) {
            $this->assertEquals(Debugger::ERROR_TYPE_NORMAL, $e->getCode());
            $this->assertEquals('Value can not be cloned [NULL]. ', $e->getMessage());
        }
    }

    public function testSeenMechanism()
    {
        $this->expectOutputString(
            "construct\n"
            . "1\n"
            . "destruct\n"
            . "0\n"
            . "over\n"
        );
        $object = new class () {
            protected $prop = 'value';

            public function __construct()
            {
                echo "construct\n";
            }

            public function __destruct()
            {
                echo "destruct\n";
            }
        };
        Debugger::validate($object);
        echo Debugger::getSeen()->count() . "\n";
        unset($object);
        echo Debugger::getSeen()->count() . "\n";
        echo "over\n";
    }
}
