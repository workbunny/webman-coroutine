<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Worker;

use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
use Workerman\Worker;

abstract class AbstractWorker extends Worker
{
    /**
     * 协程组件
     *
     * @var Coroutine
     */
    protected Coroutine $_coroutine;

    /**
     * 构造
     *
     * @param string $socket_name
     * @param array $context_option
     */
    public function __construct(string $socket_name = '', array $context_option = array())
    {
        // 初始化协程组件
        $this->_coroutine = new Coroutine();
        // 父类构造
        parent::__construct($socket_name, $context_option);
    }

    /**
     * 获取协程组件
     *
     * @return Coroutine
     */
    public function getCoroutine(): Coroutine
    {
        return $this->_coroutine;
    }

    /** @inheritdoc  */
    public function run(): void
    {
        // 加载__runInit__开头的初始化方法
        $traits = class_uses($this, false);
        foreach ($traits as $trait) {
            $methods = (new \ReflectionClass($trait))->getMethods(\ReflectionMethod::IS_PRIVATE);
            foreach ($methods as $method) {
                $methodName = $method->getName();
                if (str_starts_with($methodName, '__runInit__') and method_exists($this, $methodName)) {
                    $this->$methodName();
                }
            }
        }
        // 运行
        parent::run();
    }
}
