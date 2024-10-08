<p align="center"><img width="260px" src="https://chaz6chez.cn/images/workbunny-logo.png" alt="workbunny"></p>

**<p align="center">workbunny/webman-coroutine</p>**

**<p align="center">🐇 Webman Coroutine Infrastructure Suite Compatible with Workerman. 🐇</p>**

# Webman Coroutine Infrastructure Suite Compatible with Workerman.

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

> [workbunny/webman-coroutine 插件诞生缘由及协程开发分享](https://www.workerman.net/a/1769)

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

### 助手函数说明

- `event_loop()` 用于自动判断当前环境适合的event loop和协程驱动
    > 环境的自动判定按照`Factory::$_handlers`的顺序择先加载
- `package_installed` 用于判定当前环境是否安装对应composer包
- `is_coroutine_env` 用于判断当前环境是否为workbunny协程环境
    > 安装workbunny/webman-coroutine后自动会注册环境变量`WORKBUNNY_COROUTINE=1`

## 文档

| 目录  |                               地址                               |
|:---:|:--------------------------------------------------------------:|
| API | [Fucntion-APIs](https://workbunny.github.io/webman-coroutine/) |
| 教程  |               [PHP 协程入门](docs/doc/coroutine.md)                |
|  -  |            [workerman 环境中使用](docs/doc/workerman.md)            |
|  -  |               [webman 框架中使用](docs/doc/webman.md)               |
|  -  |                    [自定义拓展](docs/doc/custom.md)                     |

## ♨️ 相关文章

* [webman如何使用swow事件驱动和协程？](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247496493&idx=1&sn=4ab95befc894d556eac26d405f354a40&chksm=fa51129dcd269b8b61fc5b1a15a9a23b99b61c0780b9a341dfe3733692e85a1bc5e323ee9775#rd)
* [PHP高性能纯协程网络通信引擎Swow](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247496428&idx=1&sn=5f1fef3a49e3ab20ea1fa43242ac8af7&chksm=fa51135ccd269a4aac1255323faeea670238777c37fec6fb6bdef0ead857ba492c1265c03bff#rd)
* [workerman5.0 和 swoole5.0 实现一键协程](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247492324&idx=1&sn=ac697103fe56d6054593ae6d1bdadb93&chksm=fa510354cd268a4298eee50483821fff3ebb52a923a6a67708759ea4c5836649c85700f9ad12#rd)
* [webman如何使用swoole事件驱动和协程？](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247489841&idx=1&sn=52e9a57e511870c68daa2b10b78bf3a2&chksm=fa52f881cd25719782e3162108426a127b80599df80633d5edcf164162a69dc3518a9ec9cd29#rd)

## 💕 致谢
>> **💕感恩 workerman 和 swow 开发团队为 PHP 社区带来的创新和卓越贡献，让我们共同期待 PHP 在实时应用领域的更多突破！！！**
