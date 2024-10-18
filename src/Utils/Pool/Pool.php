<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Pool;

use WeakReference;
use Workbunny\WebmanCoroutine\Exceptions\PoolException;

use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use function Workbunny\WebmanCoroutine\wait_for;

use Workerman\Worker;

class Pool
{
    /**
     * @var Pool[][]
     */
    protected static array $pools = [];

    /**
     * @var bool[]
     */
    protected static array $poolIsClone = [];

    /**
     * 名称
     *
     * @var string
     */
    protected string $_name;

    /**
     * 索引
     *
     * @var int
     */
    protected int $_index;

    /**
     * 元素
     *
     * @var WeakReference|object|array|null|mixed
     */
    protected mixed $_element;

    /**
     * 是否空闲
     *
     * @var bool
     */
    protected bool $_idle;

    /**
     * 是否是深拷贝
     *
     * @var bool
     */
    protected bool $_clone;

    /**
     * 是否强制回收
     *
     * @var bool
     */
    protected bool $_force;

    /**
     * 初始化
     *
     * @param string $name 区域
     * @param int $count 区域索引
     * @param mixed $element 元素
     * @param bool $clone 是否开启深拷贝
     * @return Pool[]
     */
    public static function create(string $name, int $count, mixed $element, bool $clone = true): array
    {
        // 占位
        static::init($name, $clone);
        // 追加
        foreach (range(1, $count) as $i) {
            static::append($name, $i, $element);
        }
        return self::$pools[$name];
    }

    /**
     * 区域/区域对象是否存在
     *
     * @param string $name
     * @param int|null $index
     * @return bool
     */
    public static function exists(string $name, ?int $index): bool
    {
        return $index === null ? isset(self::$pools[$name]) : isset(self::$pools[$name][$index]);
    }

    /**
     * 初始化占位
     *
     * @param string $name
     * @param bool $clone
     * @return void
     */
    public static function init(string $name, bool $clone = true): void
    {
        if (static::exists($name, null)) {
            throw new PoolException("Pools $name already exists. ", -1);
        }
        self::$pools[$name] = [];
        self::$poolIsClone[$name] = $clone;
    }

    /**
     * 追加
     *
     * @param string $name
     * @param int $index
     * @param mixed $element
     * @return void
     */
    public static function append(string $name, int $index, mixed $element): void
    {
        $clone = self::$poolIsClone[$name] ?? false;
        self::$pools[$name][$index] = new Pool($name, $index, $element, $clone);
    }

    /**
     * 回收
     *
     * @param string $name
     * @param int|null $index
     * @param bool $force
     * @return void
     */
    public static function destroy(string $name, ?int $index, bool $force = false): void
    {
        $pools = static::get($name, $index);
        if ($pools instanceof Pool) {
            $pools->setForce($force);
            unset(self::$pools[$name][$index]);

            return;
        }
        if (is_array($pools)) {
            foreach ($pools as $i => $p) {
                $p->setForce($force);
                unset(self::$pools[$name][$i]);

                return;
            }
        }
    }

    /**
     * 获取
     *
     * @param string $name
     * @param int|null $index
     * @return Pool|Pool[]|null
     */
    public static function get(string $name, ?int $index): Pool|array|null
    {
        $pools = self::$pools[$name] ?? [];

        return $index !== null ? ($pools[$index] ?? null) : $pools;
    }

    /**
     * 获取空闲池
     *
     * @param string $name
     * @return Pool|null
     */
    public static function idle(string $name): Pool|null
    {
        $pools = self::get($name, null);
        // 总是按顺序优先获取空闲
        foreach ($pools as $pool) {
            if ($pool->isIdle() and $pool->getElement()) {
                return $pool;
            }
        }

        return null;
    }

    /**
     * 获取空闲池
     *
     * @param string $name
     * @param int $timeout
     * @return Pool
     * @throws TimeoutException
     */
    public static function getIdle(string $name, int $timeout = -1): Pool
    {
        $pool = null;
        wait_for(function () use (&$pool, $name) {
            $pool = self::idle($name);

            return $pool !== null;
        }, $timeout);
        $pool->setIdle(false);
        return $pool;
    }

    /**
     * 等待空闲并执行
     *
     * @param string $name 池区域
     * @param \Closure $closure 被执行函数 = function (Pool $pool) {}
     * @param int $timeout 超时时间
     * @return mixed
     * @throws TimeoutException
     */
    public static function waitForIdle(string $name, \Closure $closure, int $timeout = -1): mixed
    {
        try {
            $pool = static::getIdle($name, $timeout);
            return call_user_func($closure, $pool);
        } finally {
            if (isset($pool)) {
                $pool->setIdle(true);
            }
        }
    }

    /**
     * 数组的深拷贝
     *
     * @param array $array
     * @return array
     */
    protected static function _deepCopyArray(array $array): array
    {
        $copy = [];
        foreach ($array as $key => $value) {
            if (is_callable($value)) {
                // 系统接管，忽略覆盖
                // @codeCoverageIgnoreStart
                Worker::log("Pool::deepCopyArray $key value is callable. ");
                // @codeCoverageIgnoreEnd
            }
            if (is_array($value)) {
                $copy[$key] = self::_deepCopyArray($value);
            } elseif (is_object($value)) {
                $copy[$key] = clone $value;
            } else {
                $copy[$key] = $value;
            }
        }

        return $copy;
    }

    /**
     * 构造
     *
     * @param string $name 区域名称
     * @param int $index 区域索引
     * @param object|array|resource|mixed $element 元素
     * @param bool $clone 是否执行深拷贝
     */
    public function __construct(string $name, int $index, mixed $element, bool $clone = true)
    {
        if (static::get($name, $index)) {
            throw new PoolException("Pool $name#$index already exists. ", -2);
        }
        $this->_name = $name;
        $this->_index = $index;
        $this->_clone = $clone;
        $this->setForce(false);
        $this->setIdle(true);
        /*
         * 由于callable类型数据无法做到完美深拷贝，涉及到参数引用上下文问题，谨慎使用
         */
        if (is_callable($element)) {
            // 系统接管，忽略覆盖
            // @codeCoverageIgnoreStart
            Worker::log("Pool $name#$index element is callable. ");
            // @codeCoverageIgnoreEnd
        }
        $this->_element = !$clone ? $element : (match (true) {
            is_object($element)                     => clone $element,
            is_array($element)                      => self::_deepCopyArray($element),
            default                                 => $element,
        });
    }

    /**
     * 析构等待销毁
     */
    public function __destruct()
    {
        if (!$this->isForce()) {
            $this->wait();
        }
    }

    /**
     * 获取所在区域名称
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * 所在区域的索引
     *
     * @return int
     */
    public function getIndex(): int
    {
        return $this->_index;
    }

    /**
     * 获取元素
     *
     * @return resource|object|array|mixed|null
     */
    public function getElement(): mixed
    {
        return ($this->_element instanceof WeakReference) ? $this->_element->get() : $this->_element;
    }

    /**
     * 是否是深拷贝
     *
     * @return bool
     */
    public function isClone(): bool
    {
        return $this->_clone;
    }

    /**
     * 是否闲置
     *
     * @return bool
     */
    public function isIdle(): bool
    {
        return $this->_idle;
    }

    /**
     * 设置状态
     *
     * @param bool $idle
     * @return void
     */
    public function setIdle(bool $idle): void
    {
        $this->_idle = $idle;
    }

    /**
     * 是否强制回收
     *
     * @return bool
     */
    public function isForce(): bool
    {
        return $this->_force;
    }

    /**
     * 设置强制回收
     *
     * @param bool $force
     * @return void
     */
    public function setForce(bool $force): void
    {
        $this->_force = $force;
    }

    /**
     * 等待至闲置
     *
     * @param \Closure|null $closure 需要执行的逻辑 = function ($this) {}
     * @return void
     */
    public function wait(?\Closure $closure = null): void
    {
        wait_for(function () {
            return $this->isIdle();
        });
        if ($closure) {
            $this->setIdle(false);
            try {
                call_user_func($closure, $this);
            } finally {
                $this->setIdle(true);
            }
        }
    }
}
