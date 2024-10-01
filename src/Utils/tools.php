<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * @param string $targetClass
 * @param string $sourceClass
 * @return void
 * @throws ReflectionException
 */
function add_class_annotations(string $targetClass, string $sourceClass): void
{
    $reflection = new ReflectionClass($sourceClass);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    $annotations = [];
    foreach ($methods as $method) {
        if (str_starts_with($method->getName(), '__')) {
            continue;
        }
        $params = array_map(function($param) {
            return $param->getType() . " $" . $param->getName();
        }, $method->getParameters());
        $annotations[] = "@method " . $method->getReturnType() . " " . $method->getName() . "(" . implode(', ', $params) . ")";
    }

    $annotationString = "/**\n * " . implode("\n * ", $annotations) . "\n */\n";
    $filePath = (new ReflectionClass($targetClass))->getFileName();
    $fileContent = file_get_contents($filePath);

    // 获取类的完整名称，包括命名空间
    $targetClassName = (new ReflectionClass($targetClass))->getShortName();
    $classPosition = strpos($fileContent, 'class ' . $targetClassName);

    if ($classPosition !== false) {
        // 在类定义之前查找注释位置
        $beforeClassPosition = strrpos(substr($fileContent, 0, $classPosition), "\n") + 1;
        $newContent = substr_replace($fileContent, $annotationString, $beforeClassPosition, 0);
        file_put_contents($filePath, $newContent);
    } else {
        echo "未找到类定义\n";
    }
}

try {
    add_class_annotations(
        \Workbunny\WebmanCoroutine\Utils\Channel\Channel::class,
        \Workbunny\WebmanCoroutine\Utils\Channel\Handlers\ChannelInterface::class
    );

    add_class_annotations(
        \Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup::class,
        \Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\WaitGroupInterface::class
    );

    add_class_annotations(
        \Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine::class,
        \Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\CoroutineInterface::class
    );
    echo "success\n";
} catch (Throwable $e) {
    echo 'failed: ' . $e->getMessage() . "\n";
}
