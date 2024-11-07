#### 中文 | [English](README_en.md)

<p align="center"><img width="260px" src="https://chaz6chez.cn/images/workbunny-logo.png" alt="workbunny"></p>

**<p align="center">workbunny/webman-coroutine</p>**

**<p align="center">🐇 Webman Coroutine Infrastructure Suite Compatible with Workerman. 🐇</p>**

# Webman Coroutine Infrastructure Suite Compatible with Workerman.

[![Build Status](https://github.com/workbunny//webman-coroutine/actions/workflows/CI.yml/badge.svg)](https://github.com/workbunny//webman-coroutine/actions)
[![Codecov](https://codecov.io/github/workbunny/webman-coroutine/branch/main/graph/badge.svg)](https://codecov.io/github/workbunny/webman-coroutine)
[![Latest Stable Version](https://badgen.net/packagist/v/workbunny//webman-coroutine/latest)](https://github.com/workbunny//webman-coroutine/releases)
[![PHP Version Require](https://badgen.net/packagist/php/workbunny//webman-coroutine)](https://github.com/workbunny//webman-coroutine/blob/main/composer.json)
[![License](https://badgen.net/packagist/license/workbunny//webman-coroutine)](https://github.com/workbunny//webman-coroutine/blob/main/LICENSE)

## 简介

> **🚀🐇 webman-coroutine 是一个支持 workerman / webman 开发框架生态下的协程基建支撑插件**

### 起源

- workerman 4.x 及基于其作为运行容器的 webman 框架不支持协程
- workerman 5.x 及基于其作为运行容器的 webman 框架不具备完备的协程能力
- workerman / webman 没有一个较为统一的协程使用方式，导致切换协程驱动的开发成本较高，试错成本较高
- 自行实现协程版worker、server开发成本较高，试错成本较高

> [workbunny/webman-coroutine 插件诞生缘由及协程开发分享](https://www.workerman.net/a/1769)

### 目的

- 提供 `workerman`/`webman` 多样的基础协程事件库，兼容支持`workerman 4.x`和`workerman 5.x`的协程驱动
  - [revolt/PHP-fiber](https://github.com/revoltphp/event-loop)
  - [swow](https://github.com/swow/swow)
  - [swoole](https://github.com/swoole/swoole-src)
  - [ripple](https://github.com/cloudtay/ripple)
  
- 提供 `workerman`/`webman` 统一的协程开发工具，兼容非协程环境
  - 协程通道：[Utils/Channel](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Channel)
  - 协程等待：[Utils/WaitGroup](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/WaitGroup)
  - 协程：[Utils/Coroutine](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Coroutine)
  - 协程化Worker：[Utils/Worker](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Worker)
  - 对象池：[Utils/Pool](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Pool)

### 愿景

1. 在 `workerman`/`webman` 开发环境下，提供一套简单的协程工具包，降低认知负荷。
2. 在 `workerman`/`webman` 开发环境下，尝试实现一套兼容协程与非协程开发的方案，让选择和摆脱方案更简单，避免更多的焦虑。
3. 在 `workerman`/`webman` 开发环境下，尽可能实现对官方组件的无侵入式协程化改造`(虽然很难，但也想试试)`。
4. 希望在代码的实现上能够给更多PHP开发带来一些帮助，甚至灵感。

## 安装

通过`composer`安装

```php
composer require workbunny/webman-coroutine
```

## 目录

```
|-- config                       # webman 配置文件
    |-- plugin
        |-- webman-coroutine
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
    |-- Pool                     # 对象池 驱动
    |-- RegisterMethods.php      # 驱动注册助手
|-- Factory                      # 入口类
|-- CoroutineWebServer.php       # webman 自定义http服务
|-- helpers.php                  # 入口助手          
```

## 文档

| 目录  |                                               地址                                               |
|:---:|:----------------------------------------------------------------------------------------------:|
| API |                 [Fucntion-APIs](https://workbunny.github.io/webman-coroutine/)                 |
| 教程  |   [PHP 协程入门](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/coroutine.md)    |
|  -  |      [安装及配置](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/install.md)      |
|  -  |      [助手函数](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/helpers.md)       |
|  -  | [`workerman`环境](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/workerman.md) |
|  -  |    [`webman`框架](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/webman.md)    |
|  -  |     [`Utils`说明](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/utils.md)     |
|  -  |      [自定义拓展](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/custom.md)       |

## 参与开发

- [Issues](https://github.com/workbunny/webman-coroutine/issues)
- [PR](https://github.com/workbunny/webman-coroutine/pulls)

### 规范

- 新特性提交请先提交feature-issue，再提交PR，避免重复开发；
- Bug修复请先提交bug-repo-issue，再提交PR，避免重复开发；

### 工具

- 代码格式化：`composer cs-fix`
- 静态检查：`composer php-stan`
- 测试与覆盖：`composer unit-test`，命令运行后会在项目创建的`coverage`目录下生成报告
- function-apis文档生成：
  - 使用`composer doc-install`或自行安装phpDocumentor
  - 在项目根目录使用`phpDocumentor`生成文档

## ♨️ 相关文章

* [webman如何使用swow事件驱动和协程？](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247496493&idx=1&sn=4ab95befc894d556eac26d405f354a40&chksm=fa51129dcd269b8b61fc5b1a15a9a23b99b61c0780b9a341dfe3733692e85a1bc5e323ee9775#rd)
* [PHP高性能纯协程网络通信引擎Swow](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247496428&idx=1&sn=5f1fef3a49e3ab20ea1fa43242ac8af7&chksm=fa51135ccd269a4aac1255323faeea670238777c37fec6fb6bdef0ead857ba492c1265c03bff#rd)
* [workerman5.0 和 swoole5.0 实现一键协程](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247492324&idx=1&sn=ac697103fe56d6054593ae6d1bdadb93&chksm=fa510354cd268a4298eee50483821fff3ebb52a923a6a67708759ea4c5836649c85700f9ad12#rd)
* [webman如何使用swoole事件驱动和协程？](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247489841&idx=1&sn=52e9a57e511870c68daa2b10b78bf3a2&chksm=fa52f881cd25719782e3162108426a127b80599df80633d5edcf164162a69dc3518a9ec9cd29#rd)

## 💕 致谢
> **💕感恩 workerman 和 swow 开发团队为 PHP 社区带来的创新和卓越贡献，让我们共同期待 PHP 在实时应用领域的更多突破！！！**
