<p align="center"><img width="260px" src="https://chaz6chez.cn/images/workbunny-logo.png" alt="workbunny"></p>

**<p align="center">workbunny/webman-coroutine</p>**

**<p align="center">🐇 Webman plugin for coroutine web server. 🐇</p>**

# Webman plugin for coroutine web server.

<div align="center">
    <a href="https://github.com/workbunny/webman-coroutine/actions">
        <img src="https://github.com/workbunny/webman-coroutine/actions/workflows/CI.yml/badge.svg" alt="Build Status">
    </a>
    <a href="https://github.com/workbunny/webman-coroutine/releases">
        <img alt="Latest Stable Version" src="https://badgen.net/packagist/v/workbunny/webman-coroutine/latest">
    </a>
    <a href="https://github.com/workbunny/webman-coroutine/blob/main/composer.json">
        <img alt="PHP Version Require" src="https://badgen.net/packagist/php/workbunny/webman-coroutine">
    </a>
    <a href="https://github.com/workbunny/webman-coroutine/blob/main/LICENSE">
        <img alt="GitHub license" src="https://badgen.net/packagist/license/workbunny/webman-coroutine">
    </a>

</div>

## 简介

> **🚀🐇 webman-coroutine 是一个支持 workerman / webman 开发框架生态下的协程基建支撑插件**

**主要实现以下功能**：

1. 支持`workerman 4.x`的 [swow](https://github.com/swow/swow) 协程驱动能力，兼容`workerman 5.x`版本自带的`swow`协程驱动；
2. 支持`workerman 4.x`的 [swoole](https://github.com/swoole/swoole-src) 协程驱动能力，兼容`workerman 5.x`版本自带的`swoole`协程驱动；
3. 支持 [ripple](https://github.com/cloudtay/ripple) 协程驱动能力，兼容`revolt (PHP-fiber)`协程生态；
4. 提供`coroutine web server` 用于实现具备协程能力的web服务；
5. 支持纯 workerman 环境，支持 webman 开发框架

## 安装

通过`composer`安装

```php
composer require workbunny/webman-coroutine
```
> 注: 目前在alpha阶段

## 说明

1. `workerman 4.x/5.x`驱动下的 webman 框架无法完整使用`swoole`的协程能力，所以使用`CoroutineWebServer`来替代`webman`自带的`webServer`
2. `workerman 4.x`下还未有官方支持的`swow`协程驱动，本插件提供`SwowEvent`事件驱动支撑`workerman 4.x`下的协程能力
3. 由于配置`event-loop`等操作相较于普通开发会存在一定的心智负担，所以本插件提供了`event_loop()`函数，用于根据当前环境自动选择合适的事件驱动
4. workerman开发环境下支持使用所有 Utils

### 目录说明

```
|-- config                       # webman 配置文件
    |-- plugin
        |-- webman-push-server
            |-- app.php          # 主配置信息
            |-- process.php      # 启动进程
|-- Events                       # workerman-4.x 事件驱动文件
|-- Exceptions                   # 异常
|-- Handlers                     # 入口主驱动
|-- Utils                        # 工具包
    |-- Channel                  # 通道 驱动
    |-- Coroutine                # 协程 驱动
    |-- WaitGroup                # wait group 驱动
    |-- Worker                   # worker 驱动
    |-- RegisterMethods.php      # 驱动注册助手
|-- Factory                      # 入口类
|-- helpers.php                  # 入口助手          
```

### 配置说明

- enable : (true/false), 是否启用协程webServer
- port : (int), 协程webServer默认端口
- channel_size : (int), 每个connection的channel容量
- consumer_count : (int), 每个connection的消费者数量

> 注: 配置只在webman框架下自动加载生效

## 使用

> 本插件主要是webman开发框架的协程基建包，但同时也提供纯 workerman 环境的协程化能力

### webman开发框架

#### 1. swow 环境

1. 使用`./vendor/bin/swow-builder`安装`swow`拓展，注意请关闭`swoole`环境
2. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanCoroutine\event_loop()`，
   `event_loop()`函数会根据当前环境自行判断当前的 workerman 版本，自动选择合适的事件驱动
   - 当开启`swow`拓展时，`workerman 4.x`下使用`SwowEvent`事件驱动
   - 当开启`swow`拓展时，`workerman 5.x`下使用`workerman`自带的`Swow`事件驱动
   - 当未开启`swow`时，使用`workerman`自带的`Event`事件驱动
3. 使用`php -d extension=swow webman start`启动
4. webman 自带的 webServer 协程化，可以关闭启动的`CoroutineWebServer`

> 注：`CoroutineWebServer`可以在`config/plugin/workbunny/webman-coroutine/app.php`中通过`enable=false`关闭启动

#### 2. swoole 环境

1. 使用`pecl install swoole`安装稳定版 swoole 拓展
2. 建议不要将`swoole`加入`php.ini`配置文件
3. 修改`config/server.php`中`'event_loop' => \Workbunny\WebmanCoroutine\event_loop()`，
   `event_loop()`函数会根据当前环境自行判断当前的 workerman 版本，自动选择合适的事件驱动
   - 当开启 swoole 拓展时，workerman 4.x 下使用 SwooleEvent 事件驱动
   - 当开启 swoole 拓展时，workerman 5.x 下使用 workerman 自带的 Swoole 事件驱动
   - 当未开启 swoole 时，使用 workerman 自带的 Event 事件驱动
4. 使用`php -d extension=swoole webman start`启动
5. 通过`config/plugin/workbunny/webman-coroutine/process.php`启动的 CoroutineWebServer 可以用于协程环境开发，原服务还是 BIO 模式

#### 3. ripple 环境

1. 使用`composer require cclilshy/p-ripple-drive`安装 ripple 驱动插件
2. 修改`config/server.php`配置
   - `'event_loop' => \Workbunny\WebmanCoroutine\event_loop(Factory::RIPPLE_FIBER)`自动判断，请勿开启 swow、swoole，
   - `'event_loop' => \Workbunny\WebmanCoroutine\Factory::RIPPLE_FIBER`手动指定
3. 使用`php webman start`启动

> 注：该环境协程依赖`php-fiber`，并没有自动`hook`系统的阻塞函数，但支持所有支持`php-fiber`的插件

### workerman开发环境

- Workbunny\WebmanCoroutine\Utils\Channel 提供协程通道的实现
- Workbunny\WebmanCoroutine\Utils\Coroutine 提供协程的实现
- Workbunny\WebmanCoroutine\Utils\WaitGroup 提供 wait group 实现
- Workbunny\WebmanCoroutine\Utils\Worker 提供 worker 实现
  - 将原有的Workerman\Worker使用Workbunny\WebmanCoroutine\Utils\Worker\Worker替换，
  即可获得协程化`onWorkerStart`、`onWorkerStop`的Worker进程 
  - 将原有的Workerman\Worker使用Workbunny\WebmanCoroutine\Utils\Worker\Server替换，
  即可获得协程化`onConnect`、`onClose`、`onMessage`的Server进程，支持TCP/UDP/WebSocket/UnixSocket

> 注：以上工具实现的代码支持在协程/非协程下使用，也就是说通过协程方法写的代码可以运行在非协程环境下

## 文档

|      目录       |                                地址                                 |
|:-------------:|:-----------------------------------------------------------------:|
| Fucntion APIs | [Fucntion APIs 文档](https://workbunny.github.io/webman-coroutine/) |
|      教程       |                   [协程入门](docs/doc/coroutine.md)                   |
|               |                    [自定义开发](docs/doc/custom.md)                    |

## ♨️ 相关文章

* [webman如何使用swow事件驱动和协程？](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247496493&idx=1&sn=4ab95befc894d556eac26d405f354a40&chksm=fa51129dcd269b8b61fc5b1a15a9a23b99b61c0780b9a341dfe3733692e85a1bc5e323ee9775#rd)
* [PHP高性能纯协程网络通信引擎Swow](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247496428&idx=1&sn=5f1fef3a49e3ab20ea1fa43242ac8af7&chksm=fa51135ccd269a4aac1255323faeea670238777c37fec6fb6bdef0ead857ba492c1265c03bff#rd)
* [workerman5.0 和 swoole5.0 实现一键协程](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247492324&idx=1&sn=ac697103fe56d6054593ae6d1bdadb93&chksm=fa510354cd268a4298eee50483821fff3ebb52a923a6a67708759ea4c5836649c85700f9ad12#rd)
* [webman如何使用swoole事件驱动和协程？](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247489841&idx=1&sn=52e9a57e511870c68daa2b10b78bf3a2&chksm=fa52f881cd25719782e3162108426a127b80599df80633d5edcf164162a69dc3518a9ec9cd29#rd)

## 💕 致谢
>> **💕感恩 workerman 和 swow 开发团队为 PHP 社区带来的创新和卓越贡献，让我们共同期待 PHP 在实时应用领域的更多突破！！！**
