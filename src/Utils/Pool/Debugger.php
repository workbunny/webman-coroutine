<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Pool;

use Generator;
use WeakMap;
use Workbunny\WebmanCoroutine\Exceptions\PoolDebuggerException;

class Debugger
{
    /** @var int 无错误 */
    public const ERROR_TYPE_NON = 0;
    /** @var int 标量不可clone错误 */
    public const ERROR_TYPE_NORMAL = -1;
    /** @var int 资源不可clone错误 */
    public const ERROR_TYPE_RESOURCE = -2;
    /** @var int 静态数组不可clone错误 */
    public const ERROR_TYPE_STATIC_ARRAY = -101;
    /** @var int 静态对象不可clone错误 */
    public const ERROR_TYPE_STATIC_OBJECT = -102;

    protected static array $_errorMap = [
        self::ERROR_TYPE_STATIC_OBJECT => 'static object',
        self::ERROR_TYPE_STATIC_ARRAY  => 'static array',
        self::ERROR_TYPE_RESOURCE      => 'resource',
        self::ERROR_TYPE_NORMAL        => 'normal',
    ];

    /**
     * 弱引用对象缓存
     *
     * @var WeakMap<object, int>|null <弱引用对象, 错误类型>
     */
    protected static ?WeakMap $_seen = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        if (!static::$_seen) {
            static::$_seen = new WeakMap();
        }
    }

    /**
     * 获取弱引用缓存
     *
     * @return WeakMap|null
     */
    public static function getSeen(): ?WeakMap
    {
        return static::$_seen;
    }

    /**
     * 删除弱引用缓存
     *
     * @return void
     */
    public static function delSeen(): void
    {
        static::$_seen = null;
    }

    /**
     * 检查是否可拷贝
     *
     * @param mixed $value
     * @return bool
     */
    public static function validate(mixed $value): bool
    {
        $debugger = new static();
        $res = $debugger->cloneValidate($value);
        unset($debugger);

        return $res->getReturn();
    }

    /**
     * 检查是否可拷贝
     *
     * @param mixed $value
     * @param int $level 递归层级，无需使用
     * @return Generator
     */
    public function cloneValidate(mixed $value, int $level = 0): Generator
    {
        switch ($type = gettype($value)) {
            // 数组循环检查
            case 'array':
                foreach ($value as $v) {
                    yield from $this->cloneValidate($v, $level - 1);
                }

                return true;
                // 对象递归检查
            case 'object':
                // 是否在调试容器中出现过
                if (!static::$_seen->offsetExists($value)) {
                    // 利用反射检查属性
                    $reflection = new \ReflectionObject($value);
                    // 获取所有属性
                    foreach ($reflection->getProperties() as $property) {
                        $property->setAccessible(true);
                        $v = $property->getValue($value);
                        // 忽略 $this
                        if ($v === $value) {
                            continue;
                        }
                        // 静态属性
                        if ($property->isStatic()) {
                            switch (gettype($v)) {
                                // 静态数组不可控，所以返回异常
                                case 'array':
                                    // weak map 临时保存避免生命周期内的重复检查
                                    static::$_seen->offsetSet($value, static::ERROR_TYPE_STATIC_ARRAY);
                                    throw new PoolDebuggerException(
                                        'Value can not be cloned [static array]. ',
                                        static::ERROR_TYPE_STATIC_ARRAY
                                    );
                                    // 静态对象不可控，所以返回异常
                                case 'object':
                                    // weak map 临时保存避免生命周期内的重复检查
                                    static::$_seen->offsetSet($value, static::ERROR_TYPE_STATIC_OBJECT);
                                    throw new PoolDebuggerException(
                                        'Value can not be cloned [static object]. ',
                                        static::ERROR_TYPE_STATIC_OBJECT
                                    );
                                    // 资源不可拷贝，所以返回异常
                                case 'resource':
                                    // weak map 临时保存避免生命周期内的重复检查
                                    static::$_seen->offsetSet($value, static::ERROR_TYPE_RESOURCE);
                                    throw new PoolDebuggerException(
                                        'Value can not be cloned [resource]. ',
                                        static::ERROR_TYPE_RESOURCE
                                    );
                                    // 其他类型
                                    // 使用生成器递归检查，避免内存溢出
                                    // 使用throw=false忽略标量数据的抛出
                                default:
                                    yield from $this->cloneValidate($v, $level - 1);
                                    break;
                            }
                        }
                        // 非静态属性
                        else {
                            // 资源不可拷贝，所以返回异常
                            if (is_resource($v)) {
                                static::$_seen->offsetSet($value, static::ERROR_TYPE_RESOURCE);
                                throw new PoolDebuggerException(
                                    'Value can not be cloned [resource]. ',
                                    static::ERROR_TYPE_RESOURCE
                                );
                            }
                            // 其他类型，其中非静态数组是安全的安全
                            // 使用生成器递归检查，避免内存溢出
                            // 使用throw=false忽略标量数据的抛出
                            else {
                                yield from $this->cloneValidate($v, $level - 1);
                            }
                        }
                    }
                    // 不存在非法值
                    static::$_seen->offsetSet($value, static::ERROR_TYPE_NON);

                    return true;
                }
                // 如果生命周期内存在检查通过的则返回true
                if (($errorType = static::$_seen->offsetGet($value)) === static::ERROR_TYPE_NON) {
                    return true;
                }
                $info = static::$_errorMap[$errorType];
                throw new PoolDebuggerException("Value can not be cloned [$info]. ", $errorType);
                // 资源不可拷贝，返回异常
            case 'resource':
                throw new PoolDebuggerException(
                    'Value can not be cloned [resource]. ',
                    static::ERROR_TYPE_RESOURCE
                );
                // 其他
            default:
                // 允许内层
                if ($level < 0) {
                    return true;
                }
                // 不允许外层
                throw new PoolDebuggerException(
                    "Value can not be cloned [$type]. ",
                    static::ERROR_TYPE_NORMAL
                );
        }
    }
}
