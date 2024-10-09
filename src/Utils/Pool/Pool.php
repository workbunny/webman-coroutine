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
     * @var object|array|null
     */
    protected object|array|null $_element;

    /**
     * 是否空闲
     *
     * @var bool
     */
    protected bool $_idle;

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
        if ($pools) {
            foreach ($pools as $in => $pool) {
                // Pool
                if ($pool instanceof Pool) {
                    if (!$force) {
                        wait_for(function () use ($pool) {
                            return $pool->isIdle();
                        });
                    }
                    unset(self::$pools[$name][$in]);
                    continue;
                }
                // 非Pool则为数组
                foreach ($pool as $i => $p) {
                    if (!$force) {
                        wait_for(function () use ($p) {
                            return $p->isIdle();
                        });
                    }
                    unset(self::$pools[$name][$i]);
                }
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
        $this->_name = $name;
        $this->_index = $index;
        $this->setIdle(true);
        $this->_element = match (true) {
            is_object($element), is_array($element) => clone $element,
            is_callable($element)                   => call_user_func($element),
            default                                 => $element,
        };
    }

    /**
     * 析构等待销毁
     */
    public function __destruct()
    {
        wait_for(function () {
            return $this->isIdle();
        });
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

}