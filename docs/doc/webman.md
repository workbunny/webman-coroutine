# webman框架中使用

`webman-coroutine`插件提供了对webman框架协程化的一等支撑

## 配置 

### app.php

- enable : (true/false), 是否启用协程webServer
- port : (int), 协程webServer默认端口
- channel_size : (int), 每个connection的channel容量
- consumer_count : (int), 每个connection的消费者数量

### process.php

根据`app.enable`开关启用协程webServer进程

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
            'request_class' => Request::class,
            'logger'        => Log::channel(), // 日志实例
            'app_path'      => app_path(), // app目录位置
            'public_path'   => public_path(), // public目录位置
        ],
    ],
] : [];

```

## 使用

### 默认驱动支持

- swow
- swoole
- ripple

### swow

1. 使用`./vendor/bin/swow-builder`安装`swow`拓展
   - 注意请关闭`swoole`环境
   - 请勿将`swow`加入`php.ini`配置文件
2. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanCoroutine\event_loop()`，`event_loop()`函数会根据当前环境自行判断当前的 workerman 版本，自动选择合适的事件驱动
    - 当开启`swow`拓展时，`workerman 4.x`下使用`SwowEvent`事件驱动
    - 当开启`swow`拓展时，`workerman 5.x`下使用`workerman`自带的`Swow`事件驱动
    - 当未开启`swow`时，使用`workerman`自带的`Event`事件驱动
3. 使用`php -d extension=swow webman start` 或 `php -d extension=swow start.php start`启动

> Tips：`swow`安装问题请具体参考官方文档，https://docs.toast.run/swow/chs/

### swoole

1. 使用`pecl install swoole` 或者 源码编译安装稳定版 swoole 拓展
   - 请注意关闭`swow`环境
   - 请勿将`swoole`加入`php.ini`配置文件
2. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanCoroutine\event_loop()`，`event_loop()`函数会根据当前环境自行判断当前的 workerman 版本，自动选择合适的事件驱动
    - 当开启 swoole 拓展时，workerman 4.x 下使用 SwooleEvent 事件驱动
    - 当开启 swoole 拓展时，workerman 5.x 下使用 workerman 自带的 Swoole 事件驱动
    - 当未开启 swoole 时，使用 workerman 自带的 Event 事件驱动
3. 使用`php -d extension=swoole webman start` 或 `php -d extension=swoole start.php start`启动

> Tips：`swoole`安装问题请具体参考官方文档，https://wiki.swoole.com/zh-cn/#/environment

### ripple

1. 使用`composer require cclilshy/p-ripple-drive`安装 ripple 驱动插件
   - ripple驱动基于revolt (PHP-fiber)，建议安装event拓展提高性能
   - 请勿安装`swoole`拓展，否则会导致`swoole`和`ripple`命名空间冲突
2. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanCoroutine\event_loop(Factory::RIPPLE_FIBER)`自动判断
3. 使用`php webman start` 或 `php start.php start`启动

> 注：该环境协程依赖`revolt`，并没有自动`hook`系统的阻塞函数，但支持所有支持`revolt`的插件

## 开发

### 协程web服务

> webman开发框架下支持`CoroutineWebServer`自定义web服务进程

- config/workbunny/webman-coroutine/process.php 进程配置
- src/CoroutineWebServer.php 协程web服务源码
  - 默认实现onConnect、onMessage、onClose协程化
  - 非侵入，使用webman框架自带的加载逻辑

### Utils 协程工具

> webman开发框架下支持该插件的所有Utils

- Channel：统一的通道驱动，兼容非协程环境
- Coroutine：统一的协程驱动，兼容非协程环境
- WaitGroup：统一的等待驱动，兼容非协程环境
- Worker：统一的进程驱动，兼容非协程环境

> Tips：兼容非协程的统一驱动支持相同代码自由切换于各个环境，避免侵入式代码修改

**example：以下代码在任意环境都能正常运行**

```php
use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
use Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup;

$waitGroup = new WaitGroup();
// 协程1
$waitGroup->add();
$coroutine1 = new Coroutine(fucntion () use ($waitGroup) {
    echo 1 . PHP_EOL;
    $waitGroup->done();
})
// 协程2
$waitGroup->add();
$coroutine2 = new Coroutine(fucntion () use ($waitGroup) {
    echo 2 . PHP_EOL;
    $waitGroup->done();
})
$waitGroup->add();
$coroutine3 = new Coroutine(fucntion () use ($waitGroup) {
    echo 3 . PHP_EOL;
    $waitGroup->done();
})
$waitGroup->wait();
echo 'done' . PHP_EOL;
```

- 协程环境不会顺序输出1、2、3，但最后会输出`done`
- 非协程环境会顺序输出1、2、3，最后输出`done`
