## 自定义开发

`webman-coroutine`提供了用于让自己的自定义服务/进程协程化的基础工具

### 1. 自定义协程驱动

1. 实现`Workbunny\WebmanCoroutine\Handlers\HandlerInterface`接口，实现自定义协程处理逻辑
2. 通过`Workbunny\WebmanCoroutine\Factory::register(HandlerInterface $handler)`注册你的协程处理器
3. 修改`config/server.php`中`'event_loop' => {你的事件循环类}`
4. 启动`CoroutineWebServer` 接受处理协程请求

> 注：`\Workbunny\WebmanCoroutine\event_loop()`自动判断加载顺序按`\Workbunny\WebmanCoroutine\Factory::$_handlers`的顺序执行`available()`择先

> 注：因为`eventLoopClass`与`HandlerClass`是一一对应的，所以建议不管是否存在相同的事件循环或者相同的处理器都需要继承后重命名


### 2. 自定义进程协程化

> 注：考虑到 webman 框架默认不会启用注解代理，所以这里没有使用注解代理来处理协程化代理

#### 1. Worker 协程化

假设我们已经存在一个自定义服务类，如`MyProcess.php`

```php
namespace process;

class MyProcess {
    public function onWorkerStart() {
        // 具体业务逻辑
    }
    // ...
}
```

在`webman/workerman`环境中，`onWorkerStart()`是一个 worker 进程所必不可少的方法，
假设我们想要将它协程化，在不改动`MyProcess`的情况下，只需要新建一个`MyCoroutineProcess.php`

```php
namespace process;

use Workbunny\WebmanCoroutine\CoroutineWorkerInterface;
use Workbunny\WebmanCoroutine\CoroutineWorkerMethods;

class MyCoroutineProcess extends MyProcess implements CoroutineWorkerInterface {

    // 引入协程代理方法
    use CoroutineWorkerMethods;
}
```

此时的`MyCoroutineProcess`将拥有协程化的`onWorkerStart()`，将新建的`MyCoroutineProcess`添加到 webman 的自定义进程配置`config/process.php`中启动即可

#### 2. Server 协程化

> 代码样例：[CoroutineWebServer.php](src%2FCoroutineWebServer.php)

假设我们已经存在一个自定义服务类，如`MyServer.php`

```php
namespace process;

class MyServer {

    public function onMessage($connection, $data) {
        // 具体业务逻辑
    }

    // ...
}
```

在`webman/workerman`环境中，`onMessage()`是一个具备监听能力的进程所必不可少的方法，假设我们想要将它协程化，在不改动`MyServer`的情况下，只需要新建一个`MyCoroutineServer.php`

```php
namespace process;

use Workbunny\WebmanCoroutine\CoroutineServerInterface;
use Workbunny\WebmanCoroutine\CoroutineServerMethods;

class MyCoroutineServer extends MyServer implements CoroutineServerInterface {

    // 引入协程代理方法
    use CoroutineServerMethods;
}
```

此时的`MyCoroutineServer`将拥有协程化的`onMessage()`，将新建的`MyCoroutineServer`添加到 webman 的自定义进程配置`config/process.php`中启动即可

