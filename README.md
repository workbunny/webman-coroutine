# webman-coroutine

## 简介

webman-coroutine是一个webman开发框架生态下的协程基建支撑插件，主要实现以下功能：

1. 支持workerman 4.x的swow协程驱动能力，兼容workerman 5.x版本自带的swow协程驱动；
2. 支持workerman 4.x的swoole协程驱动能力，兼容workerman 5.x版本自带的swoole协程驱动；
3. 实现coroutine web server，用于实现具备协程能力的web框架基建
4. 支持自定义协程实现，如基于revolt等

## 说明

1. workerman 4.x/5.x驱动下的webman框架无法完整使用swoole的协程能力，所以使用CoroutineWebServer来替代webman自带的webServer
2. workerman 4.x下还未有官方支持的swow协程驱动，本插件提供SwowEvent事件驱动支撑workerman 4.x下的协程能力
3. 由于配置event-loop等操作相较于普通开发会存在一定的心智负担，所以本插件提供了`event_loop()`函数，用于根据当前环境自动选择合适的事件驱动

## 使用

- 安装插件包
```shell
composer require workbunny/webman-coroutine
```

**Tips: 目前在开发阶段，体验请使用dev-main分支**

### swow环境

1. 使用`./vendor/bin/swow-builder`安装swow拓展，注意请关闭swoole环境
2. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanCoroutine\event_loop()`，
`event_loop()`函数会根据当前环境自行判断当前的workerman版本，自动选择合适的事件驱动
   - 当开启swow拓展时，workerman 4.x下使用SwowEvent事件驱动
   - 当开启swow拓展时，workerman 5.x下使用workerman自带的Swow事件驱动
   - 当未开启swow时，使用workerman自带的Event事件驱动
3. 使用`php -d extension=swow webman start`启动
4. webman自带的webServer协程化，可以关闭启动的CoroutineWebServer

**Tips：CoroutineWebServer可以在`config/plugin/workbunny/webman-coroutine/app.php`中通过`enable=false`关闭启动**

### swoole环境

1. 使用`pecl install swoole`安装稳定版swoole拓展
2. 建议不要将swoole加入php.ini配置文件
3. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanCoroutine\event_loop()`，
   `event_loop()`函数会根据当前环境自行判断当前的workerman版本，自动选择合适的事件驱动
   - 当开启swoole拓展时，workerman 4.x下使用SwooleEvent事件驱动
   - 当开启swoole拓展时，workerman 5.x下使用workerman自带的Swoole事件驱动
   - 当未开启swoole时，使用workerman自带的Event事件驱动
4. 使用`php -d extension=swoole webman start`启动
5. 通过`config/plugin/workbunny/webman-coroutine/process.php`启动的CoroutineWebServer可以用于协程环境开发，原服务还是BIO模式

### ripple环境

1. 使用`composer require cclilshy/p-ripple-drive`安装ripple驱动插件
2. 修改`config/server.php`配置
   - `'event_loop' => \Workbunny\WebmanCoroutine\event_loop()`自动判断，请勿开启swow、swoole，
   - `'event_loop' => \Workbunny\WebmanCoroutine\Factory::RIPPLE_FIBER`手动指定
3. 使用`php webman start`启动

**Tips：该环境协程依赖php-fiber，并没有自动hook系统的阻塞函数，但支持所有支持php-fiber的插件**

### 自定义环境

1. 实现`Workbunny\WebmanCoroutine\Handlers\HandlerInterface`接口，实现自定义协程处理逻辑
2. 通过`Workbunny\WebmanCoroutine\Factory::register(HandlerInterface $handler)`注册你的协程处理器
3. 修改`config/server.php`中`'event_loop' => {你的事件循环类}`
4. 启动CoroutineWebServer，接受处理协程请求

**Tips：`\Workbunny\WebmanCoroutine\event_loop()`自动判断加载顺序按`\Workbunny\WebmanCoroutine\Factory::$_handlers`的顺序执行available()择先**
**Tips：因为eventLoopClass与HandlerClass是一一对应的，所以建议不管是否存在相同的事件循环或者相同的处理器都需要继承后重命名**
