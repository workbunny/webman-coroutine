# webman-swow

## 简介

webman默认使用基于ext-event拓展的workerman作为驱动运行，在请求中如果遇到Blocking-I/O业务则会阻塞当前进程，
webman-swow提供了支持workerman 4.x的swow事件驱动，使用swow事件驱动可获得以下能力：

1. web-server自动升级为Non-blocking I/O服务
2. 基于swow的协程开发能力

## 说明

1. 支持workerman 4.x驱动版本的webman框架使用swow协程
2. 额外提供一个swow web server（其实直接使用webman server即可）

## 使用

1. 使用`composer require workbunny/webman-swow`安装插件包
2. 使用`./vendor/bin/swow-builder`安装swow拓展，注意请关闭swoole环境
3. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanSwow\event_loop()`，
`event_loop()`函数会根据当前环境自行判断当前的workerman版本，自动选择合适的事件驱动
   - 当开启swow拓展时，workerman 4.x下使用SwowEvent事件驱动
   - 当开启swow拓展时，workerman 5.x下使用workerman自带的Swow事件驱动
   - 当未开启swow时，使用workerman自带的Event事件驱动
4. 使用`php -d extension=swow webman start`启动

**Tips：自带的swow web server可以在`config/plugin/workbunny/webman-swow/app.php`中通过`enable=false`关闭启动**