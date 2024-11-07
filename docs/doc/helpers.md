# 助手函数

## `event_loop()` 环境函数

> 该函数用于根据当前环境自定加载合适的事件驱动及`Utils`驱动

- 环境的自动判定按照`Factory::$_handlers`的顺序通过`HandlerInterface::isAvailable()`判定择先加载
- `event_loop()`可以指定倾向的协程驱动优先加载，如：`event_loop('SwowEvent::class')`
- 系统未找到合适协程驱动时，使用`Workerman\Worker::$globalEvent`默认加载，`Utils`则使用非协程模式启动

## `wait_for` 协程等待函数

> - 该函数用于通过协程非阻塞的等待一个业务的判定返回，并且支持超时和协程事件分配
> - 指定协程事件的`wait_for`可以通过`wakeup`函数唤醒
> - **`wait_for`使用类似`Best Effort`的方式去执行，也就意味当已经发生超时时，依然会尝试执行一次回调验证，如果失败则抛出超时异常**
> - 指定事件使用自约定字符串，请保持唯一性，否则以第一次执行生效

### 1. 协程等待`Cache::exists('your_key')`查询结果，永久等待

> 永久等待的时候，协程出让切换间隔存在0-2ms的随机延迟，存在N次协程出让调度

```php
\Workbunny\WebmanCoroutine\wait_for(function () {
    return Cache::exists('your_key');
});

```
### 2. 协程等待`Cache::exists('your_key')`查询结果，等待10秒

```php
\Workbunny\WebmanCoroutine\wait_for(function () {
    return Cache::exists('your_key');
}, timeout: 10);

```

### 3. 协程等待`Cache::exists('your_key')`查询结果，永久等待，指定事件

> 当指定事件等待时，仅出让一次等待，直到`wakeup`唤醒

```php
// 挂起
\Workbunny\WebmanCoroutine\wait_for(function () {
    return Cache::exists('your_key');
}, event: 'cache.wait.your_key');

// 唤醒
\Workbunny\WebmanCoroutine\wakeup('cache.wait.your_key');

```

### 4. 协程等待`Cache::exists('your_key')`查询结果，等待10秒，指定事件

> 当指定事件等待时，仅出让一次，等待10秒，在等待期间内没有发生`wakeup`唤醒时则抛出超时异常

```php
// 挂起
\Workbunny\WebmanCoroutine\wait_for(function () {
    return Cache::exists('your_key');
}, timeout: 10, event: 'cache.wait.your_key');

// 唤醒
\Workbunny\WebmanCoroutine\wakeup('cache.wait.your_key');

```

## `sleep` 协程等待函数

> - 该函数与`wait_for`类似，执行协程出让 **0 至 ∞** 秒，区别是`sleep`不接收判定回调函数入参，仅作协程出让等待
> - 指定协程事件的`sleep`可以通过`wakeup`函数唤醒
> - 指定事件使用自约定字符串，请保持唯一性，否则以第一次执行生效

### 1. 协程出让等待到下一次循环

> 协程出让切换间隔存在0-2ms的随机延迟，存在N次协程出让调度

```php
\Workbunny\WebmanCoroutine\sleep(0);

\Workbunny\WebmanCoroutine\sleep(-1);

```
### 2. 协程出让等待，永久等待，直到唤醒

```php
// 挂起
\Workbunny\WebmanCoroutine\sleep(-1, event: 'sleep.wait.wakeup');

// 唤醒
\Workbunny\WebmanCoroutine\wakeup('sleep.wait.wakeup');

```

### 3. 协程出让等待，等待10秒

```php
// 挂起
\Workbunny\WebmanCoroutine\sleep(10);

```

### 4. 协程出让等待，等待10秒，指定事件

```php
// 挂起
\Workbunny\WebmanCoroutine\sleep(10, event: 'sleep.wait.wakeup');

// 唤醒
\Workbunny\WebmanCoroutine\wakeup('sleep.wait.wakeup');

```

## `wakeup` 用于唤起指定事件的协程出让

## `is_coroutine_env` 用于判断当前环境是否为workbunny协程环境

> 安装workbunny/webman-coroutine后自动会注册环境变量`WORKBUNNY_COROUTINE=1`

## `package_installed` 用于判定当前环境是否安装对应composer包
