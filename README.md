# webman-swow

## 简介

webman-swow是一个webman开发框架生态下的协程基建支撑插件，主要实现以下功能：

1. 支持workerman 4.x的swow协程驱动能力，兼容workerman 5.x版本自带的swow协程驱动；
2. 支持workerman 4.x的swoole协程驱动能力，兼容workerman 5.x版本自带的swoole协程驱动；
3. 实现coroutine web server，用于实现具备协程能力的web框架基建

## 说明

1. workerman 4.x/5.x驱动下的webman框架无法完整使用swoole的协程能力，所以使用CoroutineWebServer来替代webman自带的webServer
2. workerman 4.x下还未有官方支持的swow协程驱动，本插件提供SwowEvent事件驱动支撑workerman 4.x下的协程能力
3. 由于配置event-loop等操作相较于普通开发会存在一定的心智负担，所以本插件提供了`event_loop()`函数，用于根据当前环境自动选择合适的事件驱动

## 使用

### swow

1. 使用`composer require workbunny/webman-swow`安装插件包
2. 使用`./vendor/bin/swow-builder`安装swow拓展，注意请关闭swoole环境
3. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanSwow\event_loop()`，
`event_loop()`函数会根据当前环境自行判断当前的workerman版本，自动选择合适的事件驱动
   - 当开启swow拓展时，workerman 4.x下使用SwowEvent事件驱动
   - 当开启swow拓展时，workerman 5.x下使用workerman自带的Swow事件驱动
   - 当未开启swow时，使用workerman自带的Event事件驱动
4. 使用`php -d extension=swow webman start`启动
5. webman自带的webServer协程化，可以关闭启动的CoroutineWebServer

**Tips：CoroutineWebServer可以在`config/plugin/workbunny/webman-swow/app.php`中通过`enable=false`关闭启动**

### swoole

1. 使用`pecl install swoole`安装稳定版swoole拓展
2. 建议不要将swoole加入php.ini配置文件
3. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanSwow\event_loop()`，
   `event_loop()`函数会根据当前环境自行判断当前的workerman版本，自动选择合适的事件驱动
   - 当开启swoole拓展时，workerman 4.x下使用SwooleEvent事件驱动
   - 当开启swoole拓展时，workerman 5.x下使用workerman自带的Swoole事件驱动
   - 当未开启swoole时，使用workerman自带的Event事件驱动
4. 使用`php -d extension=swoole webman start`启动
5. 通过`config/plugin/workbunny/webman-swow/process.php`启动的CoroutineWebServer可以用于协程环境开发，原服务还是BIO模式