# 自定义拓展

`webman-coroutine`提供了用于让自己的自定义服务/进程协程化的基础工具

## 驱动自定义

> 主驱动提供`Workbunny\WebmanCoroutine\Handlers\HandlerInterface`接口支持自定义驱动类

1. 实现`Workbunny\WebmanCoroutine\Handlers\HandlerInterface`接口，实现自定义协程处理逻辑
2. 通过`Workbunny\WebmanCoroutine\Factory::register(HandlerInterface $handler)`注册你的协程处理器
3. 修改`config/server.php`中`'event_loop' => {你的事件循环类}`
4. 启动`CoroutineWebServer` 接受处理协程请求

> 注：`\Workbunny\WebmanCoroutine\event_loop()`自动判断加载顺序按`\Workbunny\WebmanCoroutine\Factory::$_handlers`的顺序执行`available()`择先

> 注：因为`eventLoopClass`与`HandlerClass`是一一对应的，所以建议不管是否存在相同的事件循环或者相同的处理器都需要继承后重命名


## Utils自定义

> Utils下`Channel`、`Coroutine`、`WaitGroup`工具都提供了对应接口，支持自定义实现

### Channel

1. 实现`Workbunny\WebmanCoroutine\Utils\Channel\Handlers\ChannelInterface`接口，实现自定义通道逻辑
2. 通过`Workbunny\WebmanCoroutine\Utils\Channel\Channel::register(string $eventLoopClass, ChannelInterface $channelHandler)`注册你的通道处理器
3. 当驱动使用的是该`$eventLoopClass`时，`Channel`会自动使用其注册对应的Handler

### Coroutine

1. 实现`Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\CoroutineInterface`接口，实现自定义协程逻辑
2. 通过`Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine::register(string $eventLoopClass, CoroutineInterface $coroutineHandler)`注册你的协程处理器
3. 当驱动使用的是该`$eventLoopClass`时，`Coroutine`会自动使用其注册对应的Handler

### WaitGroup

1. 实现`Workbunny\WebmanCoroutine\Utils\WaitGroup\Handlers\WaitGroupInterface`接口，实现自定义等待逻辑
2. 通过`Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup::register(string $eventLoopClass, WaitGroupInterface $waitGroupHandler)`注册你的等待处理器
3. 当驱动使用的是该`$eventLoopClass`时，`WaitGroup`会自动使用其注册对应的Handler
