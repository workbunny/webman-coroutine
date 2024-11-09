# 协程的观测和管理

`webman-coroutine`中提供的关于协程的部分来自`Utils/Coroutine`和`Factory::sleep()`

## `Utils/Coroutine` 协程工具

> `Utils/Coroutine`会在每次构造的时候将注入一个WeakMap，并储存`id`、`startTime`

- 获取当前进程所有协程

```php
$weakMap = \Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine::getCoroutinesWeakMap();
```
> Tips: 方法返回一个储存所有通过`Utils/Coroutine`创建的协程的`WeakMap`

- 退出当前进程所有协程

```php
$weakMap = \Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine::getCoroutinesWeakMap();
/**
 * @var \Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\CoroutineInterface $coroutine
 * @var array<string, mixed> $info ['id' => 协程id, 'startTime' => 开始时间]
 */
foreach ($weakMap as $coroutine => $info) {
   $coroutine->kill(new \Workbunny\WebmanCoroutine\Exceptions\KilledException());
}
```
> Tips: 
> - `kill`方法并不会立即退出协程，而是在该协程下一次唤起的时候触发，抛出一个异常
> - 各协程驱动各略有不同，如：swoole驱动在协程遇到文件IO事件时并不会立即退出，也不会在下次唤起时抛出异常

## `Handler::$suspension`挂起事件

> 所有基于`Factory::sleep()`创建的挂起事件既是`Handler::$suspension`，包括`sleep()`、`wait_for()`和`Factory::waitFor()`

- 获取当前进程所有挂起事件

```php
$weakMap = \Workbunny\WebmanCoroutine\Factory::getSuspensionsWeakMap();
```
> Tips: 方法返回一个储存所有通过`Factory`创建的协程的`WeakMap`

- 添加/设置一个挂起事件至`WeakMap`
```php
$weakMap = \Workbunny\WebmanCoroutine\Factory::setSuspensionsWeakMap();
```

- 退出当前进程所有挂起事件

```php
$weakMap = \Workbunny\WebmanCoroutine\Factory::getSuspensionsWeakMap();
/**
 * @var mixed $suspension
 * @var array<string, mixed> $info ['id' => 协程id, 'startTime' => 开始时间, 'event' => 挂起事件|NULL]
 */
foreach ($weakMap as $suspension => $info) {
   \Workbunny\WebmanCoroutine\Factory::kill($suspension);
}
```

> Tips:
> - `kill`方法并不会立即退出协程，而是在该挂起下一次被唤起的时候触发，抛出一个`KilledException`
> - 各协程驱动各略有不同，如：`swoole`驱动在协程遇到文件IO事件时并不会立即退出，挂起事件会抛出`KilledException`

## 观测/管理实践

### 采样监听方案

1. 在服务进程启动时创建定时器
2. 定时器实现`Coroutine::getCoroutinesWeakMap()`和`Factory::getSuspensionsWeakMap()`的采样，并以进程pid为区分输出至日志

> Tips: 可以自定义实现对协程或挂起事件的startTime比对，合理杀死过长挂起的协程/事件

### 遥控管理方案

1. 在进程中实现命令对应的控制逻辑，例如：`kill`、`dump`、`check`等
2. 在服务进程启动时通过`redis`/`apcu`对通道进行监听，注册命令对应的控制监听
3. 遥控cli程序通过对通道的`pub`发送指定命令进行控制

> Tips: `webman`/`workerman` 环境基于`apcu`的共享缓存插件推荐：https://www.workerman.net/plugin/133