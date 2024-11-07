#### [中文](README.md) | English

<p align="center"><img width="260px" src="https://chaz6chez.cn/images/workbunny-logo.png" alt="workbunny"></p>

**<p align="center">workbunny/webman-coroutine</p>**

**<p align="center">🐇 Webman Coroutine Infrastructure Suite Compatible with Workerman. 🐇</p>**

# Webman Coroutine Infrastructure Suite Compatible with Workerman.

[![Build Status](https://github.com/workbunny//webman-coroutine/actions/workflows/CI.yml/badge.svg)](https://github.com/workbunny//webman-coroutine/actions)
[![Codecov](https://codecov.io/github/workbunny/webman-coroutine/branch/main/graph/badge.svg)](https://codecov.io/github/workbunny/webman-coroutine)
[![Latest Stable Version](https://badgen.net/packagist/v/workbunny//webman-coroutine/latest)](https://github.com/workbunny//webman-coroutine/releases)
[![PHP Version Require](https://badgen.net/packagist/php/workbunny//webman-coroutine)](https://github.com/workbunny//webman-coroutine/blob/main/composer.json)
[![License](https://badgen.net/packagist/license/workbunny//webman-coroutine)](https://github.com/workbunny//webman-coroutine/blob/main/LICENSE)

> This document was translated by AI. 
> If there are any inaccuracies or if any part is unclear, please refer to the original Chinese document or email for clarification. 
> Corrections to the document are welcome.

## Introduction

> **🚀🐇 webman-coroutine is a coroutine infrastructure support plugin for the `workerman`/`webman` development framework ecosystem**

### Origin

- `Workerman 4.x` and the `webman` framework based on it as a runtime container do not support coroutines
- `Workerman 5.x` and the `webman` framework based on it as a runtime container do not have complete coroutine capabilities
- There is no unified way to use coroutines in `workerman`/`webman`, leading to high development and trial costs when switching coroutine drivers
- Implementing coroutine versions of workers and servers on your own has high development and trial costs

> [The origin of the workbunny/webman-coroutine plugin and coroutine development sharing](https://www.workerman.net/a/1769)

### Purpose

- Provide various basic coroutine event libraries for `workerman`/`webman`, compatible with coroutine drivers for workerman 4.x and workerman 5.x
  - [revolt/PHP-fiber](https://github.com/revoltphp/event-loop)
  - [swow](https://github.com/swow/swow)
  - [swoole](https://github.com/swoole/swoole-src)
  - [ripple](https://github.com/cloudtay/ripple)

- Provide unified coroutine development tools for `workerman`/`webman`, compatible with non-coroutine environments
  - Coroutine channel: [Utils/Channel](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Channel)
  - Coroutine wait group: [Utils/WaitGroup](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/WaitGroup)
  - Coroutine: [Utils/Coroutine](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Coroutine)
  - Coroutine Worker: [Utils/Worker](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Worker)
  - Object pool: [Utils/Pool](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Pool)

### Vision

1. Provide a simple coroutine toolkit in the `workerman`/`webman` development environment to reduce cognitive load.
2. Attempt to implement a solution in the `workerman`/`webman` development environment that is compatible with both coroutine and non-coroutine development, making it easier to choose and abandon solutions, reducing anxiety.
3. Try to achieve non-intrusive coroutine modifications to official components in the `workerman`/`webman` development environment (although it’s difficult, we want to try).
4. Hope to provide some help, or even inspiration, to more PHP developers with the implementation of our code.

## Installation

Install via composer:

```php
composer require workbunny/webman-coroutine
```

## Directory

```
|-- config                       # webman configuration files
|   |-- plugin
|   |-- webman-coroutine
|       |-- app.php          # main configuration
|       |-- process.php      # start process
|-- Events                       # workerman-4.x event-driven files
|-- Exceptions                   # exceptions
|-- Handlers                     # main driver entry
|-- Utils                        # toolkit
|   |-- Channel                  # channel driver
|   |-- Coroutine                # coroutine driver
|   |-- WaitGroup                # wait group driver
|   |-- Worker                   # worker driver
|   |-- Pool                     # object pool driver
|   |-- RegisterMethods.php      # driver registration helper
|-- Factory                      # entry class
|-- CoroutineWebServer.php       # custom http server for webman
|-- helpers.php                  # entry helper
```

### Helper Functions

- `event_loop()` used to automatically determine the suitable event loop and coroutine driver for the current environment 
    > The environment is automatically determined in the order of Factory::$_handlers
- `package_installed` used to determine whether the corresponding composer package is installed in the current environment
- `wait_for` used for non-blocking waiting of corresponding conditions in the process (usually the result of child coroutine execution)
- `is_coroutine_env` used to determine whether the current environment is a workbunny coroutine environment 
    > After installing `workbunny/webman-coroutine`, the environment variable `WORKBUNNY_COROUTINE=1` will be automatically registered

## Documentation

| Directory  |                                                    Address                                                     |
|:---:|:--------------------------------------------------------------------------------------------------------------:|
| API |                         [Fucntion-APIs](https://workbunny.github.io/webman-coroutine/)                         |
| Tutorial  | [Introduction to PHP Coroutine](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/coroutine.md) |
|  -  | [Installation and Configuration](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/install.md)  |
|  -  |     [Helper Functions](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/helpers.md)     |
|  -  |    [`workerman` Environment](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/workerman.md)    |
|  -  |        [`webman` Framework](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/webman.md)        |
|  -  |     [Explanation of `Utils`](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/utils-en.md)     |
|  -  |        [Custom Extensions](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/custom.md)         |

## Participate in Development

- [Issues](https://github.com/workbunny/webman-coroutine/issues)
- [PR](https://github.com/workbunny/webman-coroutine/pulls)

### Specifications

- For new feature submissions, please submit a feature issue before submitting a PR to avoid duplicate development;

- For bug fixes, please submit a bug report issue before submitting a PR to avoid duplicate development;

### Tools

- Code formatting: `composer cs-fix`
- Static analysis: `composer php-stan`
- Testing and coverage: `composer unit-test`, the command will generate a report in the `coverage` directory created in the project
- function-apis documentation generation:
  - Use `composer doc-install` or install `phpDocumentor` yourself
  - Use `phpDocumentor` to generate documentation in the root directory of the project

## ♨️ Related Articles

* [How to use swow event-driven and coroutine in webman?](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247496493&idx=1&sn=4ab95befc894d556eac26d405f354a40&chksm=fa51129dcd269b8b61fc5b1a15a9a23b99b61c0780b9a341dfe3733692e85a1bc5e323ee9775#rd)
* [Swow: A high-performance pure coroutine network communication engine for PHP](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247496428&idx=1&sn=5f1fef3a49e3ab20ea1fa43242ac8af7&chksm=fa51135ccd269a4aac1255323faeea670238777c37fec6fb6bdef0ead857ba492c1265c03bff#rd)
* [How to implement one-click coroutine with workerman5.0 and swoole5.0](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247492324&idx=1&sn=ac697103fe56d6054593ae6d1bdadb93&chksm=fa510354cd268a4298eee50483821fff3ebb52a923a6a67708759ea4c5836649c85700f9ad12#rd)
* [How to use swoole event-driven and coroutine in webman?](https://mp.weixin.qq.com/s?__biz=MzUzMDMxNTQ4Nw==&mid=2247489841&idx=1&sn=52e9a57e511870c68daa2b10b78bf3a2&chksm=fa52f881cd25719782e3162108426a127b80599df80633d5edcf164162a69dc3518a9ec9cd29#rd)

## Acknowledgements
> Grateful to the workerman and swow development teams for their innovation and excellent contributions to the PHP community. Let's look forward to more breakthroughs in PHP for real-time applications!!!