# webman框架中使用

`webman-coroutine`插件提供了对webman框架协程化的一等支撑

### 配置

webman开发框架下除了协程工具外还会根据框架插件加载策略加载配置文件中的对应进程

#### app.php

- enable : (true/false), 是否启用协程webServer
- port : (int), 协程webServer默认端口
- consumer_count : (int), 每个connection的消费者数量

#### process.php

以webman自定义进程方式启动一个基于框架App加载的协程化web服务

```php

return config('plugin.workbunny.webman-coroutine.app.enable', false) ? [
    'coroutine-web-server' => [
        'handler'     => CoroutineWebServer::class,
        'listen'      => 'http://[::]:' . config('plugin.workbunny.webman-coroutine.app.port', 8717),
        'count'       => cpu_count(),
        'user'        => '',
        'group'       => '',
        'reusePort'   => true,
        'constructor' => [
            'requestClass' => Request::class,
            'logger'        => Log::channel(), // 日志实例
            'appPath'      => app_path(), // app目录位置
            'publicPath'   => public_path(), // public目录位置
        ],
    ],
] : [];

```

## 使用

- 选择一个你所需要的协程驱动进行安装，请参考：[安装及环境配置](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/install.md)
- 将以下代码添加至`webman`配置`config\server.php`

   ```php
   // ...
   'event_loop' => \Workbunny\WebmanCoroutine\event_loop(),
   // ...
   ```
  > 注：对于`Utils\Worker`工具包需要`webman-framework`最新版支持`workerClass`参数，详见 https://github.com/walkor/webman-framework/pull/110

## 说明

- `webman`环境支持`\config`配置使用
- `webman`环境支持`CoroutineWebServer`进程服务
  - `CoroutineWebServer`进程对`onConnect`、`onMessage`、`onClose`进行了协程化
  - `CoroutineWebServer`进程是对`App`类的代理，并非侵入式改造，支持`webman`框架升级
  - `CoroutineWebServer`进程支持`consumer_count`参数，用于限制每个连接的协程消费者数量，当数量达到上限时onMessage会进行协程等待，直到有空闲协程才恢复socket的监听
- `webman`环境支持`Utils`工具包下的所有工具类
  > `Utils`工具包请参考：[`Utils`说明](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/utils.md)