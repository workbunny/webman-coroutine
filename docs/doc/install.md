# 安装及环境配置

## 1. 基础依赖
- PHP >= 8.0
- workerman ^4.0 | workerman ^5.0

## 2. 插件安装

插件通过`composer`安装

```shell
composer require workbunny/webman-coroutine
```

## 3. 驱动安装及配置

`webman-coroutine`插件以最小内核的方式进行安装，使用以下驱动按需手动进行依赖安装

### [revolt/PHP-fiber](https://github.com/revoltphp/event-loop) 驱动

#### 依赖

- PHP >= 8.1
  > `PHP 8.1`及以上才支持`Fiber`协程
- revolt/event-loop ^1.0

#### 安装

- 通过`composer`安装

   ```shell
   composer require revolt/event-loop
   ```

### [swow](https://github.com/swow/swow) 驱动

#### 依赖

- PHP >= 8.0
- swow/swow ^1.0

#### 安装

- 通过`composer`安装

   ```shell
   composer require swow/swow
   ```

- 使用命令行安装

   ```shell
   php vendor/bin/swow-builder --rebuild --install 
   ```
   > `swow`安装问题请具体参考官方文档，https://docs.toast.run/swow/chs/

- 请勿将`swow`加入`php.ini`配置文件，建议使用`-d extension=swow`加载

### [swoole](https://github.com/swoole/swoole-src) 驱动

#### 依赖

- PHP >= 8.0
- pecl/swoole **建议使用`latest`**

#### 安装

- 通过`pecl`安装

   ```shell
   pecl install swoole
   ```
   > Tips：
   > - `PHP 8.4`还可以预览使用`pie`进行安装`pie install swoole
   > - `swoole`安装问题请具体参考官方文档，https://wiki.swoole.com/zh-cn/#/environment

- 请勿将`swoole`加入`php.ini`配置文件，建议使用`-d extension=swoole`加载

### [ripple](https://github.com/cloudtay/ripple) 驱动

#### 依赖

- PHP >= 8.1
- cclilshy/p-ripple-drive **建议使用`latest`**

#### 安装

- 通过`composer`安装

   ```shell
   composer require cclilshy/p-ripple-drive
   ```
  > Tips：
  > - `cclilshy/p-ripple-drive`与`swoole`的命名空间冲突，使用时请将`swoole`移除PHP.ini
  > - `cclilshy/p-ripple-drive`工具请参考官方文档，https://ripple.cloudtay.com/docs/intro
