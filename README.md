# webman-swow

## 简介

1. 支持workerman 4.x驱动版本的webman框架使用swow协程
2. 额外提供一个swow web server（其实直接使用webman server即可）

## 说明

1. 使用`composer require workbunny/webman-swow 0.0.*`安装预览版插件包
2. 使用`./vendor/bin/swow-builder`安装swow拓展，注意请关闭swoole环境
3. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanSwow\SwowEvent::class,`
4. 使用`php -d extension=swow webman start`启动