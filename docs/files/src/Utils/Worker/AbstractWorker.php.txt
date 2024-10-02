<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine\Utils\Worker;

use Workbunny\WebmanCoroutine\Factory;
use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;
use Workerman\Worker;

abstract class AbstractWorker extends Worker
{
    /** @inheritdoc  */
    public function run(): void
    {
        // 加载环境
        /** @var HandlerInterface $handler */
        $handler = Factory::getCurrentHandler();
        $handler::initEnv();
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
