<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Pool;

use Workbunny\WebmanCoroutine\Exceptions\PoolException;
use function Workbunny\WebmanCoroutine\wait_for;

class Pool
{

    /**
     * @var Pool[][]
     */
    protected static array $pools = [];

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
     * @var object|array|null|mixed
     */
    protected mixed $_element;

    /**
     * 是否空闲
     *
     * @var bool
     */
    protected bool $_idle;

    /**
     * 是否强制回收
     *
     * @var bool
     */
    protected bool $_force;

    /**
     * 创建
     *
     * @param string $name
     * @param int $count
     * @param mixed $element
     * @return Pool[]
     */
    public static function create(string $name, int $count, mixed $element): array
    {
        if (static::get($name, null)) {
            throw new PoolException("Pools $name already exists. ", -1);
        }
        foreach (range(1, $count) as $i) {
            self::$pools[$name][$i] = new Pool($name, $i, $element);
        }
        return self::$pools[$name];
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
            if ($pool->isIdle()) {
                return $pool;
            }
        }
        return null;
    }

    /**
     * 等待空闲并执行
     *
     * @param string $name 池区域
     * @param \Closure $closure 被执行函数 = function (Pool $pool) {}
     * @param int $timeout 超时时间
     * @return mixed
     */
    public static function waitForIdle(string $name, \Closure $closure, int $timeout = -1): mixed
    {
        $pool = null;
        wait_for(function () use (&$pool, $name) {
            $pool = self::idle($name);
            return $pool !== null;
        }, $timeout);
        return call_user_func($closure, $pool);
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
     * @param string $name
     * @param int $index 索引
     * @param object|array|resource|mixed $element
     */
    public function __construct(string $name, int $index, mixed $element)
    {
        if (static::get($name, $index)) {
            throw new PoolException("Pool $name#$index already exists. ", -2);
        }
        $this->_name  = $name;
        $this->_index = $index;
        $this->setForce(false);
        $this->setIdle(true);
        $this->_element = match (true) {
            is_object($element)                     => clone $element,
            is_array($element)                      => self::_deepCopyArray($element),
            is_callable($element)                   => call_user_func($element),
            default                                 => $element,
        };
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
     * @return resource|object|array|mixed
     */
    public function getElement(): mixed
    {
        return $this->_element;
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
     * @param \Closure|null $closure
     * @return void
     */
    public function wait(?\Closure $closure = null): void
    {
        wait_for(function () {
            return $this->isIdle();
        });
        if ($closure) {
            call_user_func($closure, $this);
        }
    }

}