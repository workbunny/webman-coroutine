# workerman环境中使用

`webman-coroutine`提供了用于workerman开发环境协程化的基础工具

## 使用

- 选择一个你所需要的协程驱动进行安装，请参考：[安装及环境配置](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/install.md)
- 将以下代码添加至你的入口文件

   ```php
   \Workerman\Worker::$eventLoopClass = \Workbunny\WebmanCoroutine\event_loop();
   ```
   > Tips：
   > - 对于`Utils\Worker`工具包的使用还需要做一些代码调整，详细参考：[`Utils`说明](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/utils.md)
   > - 其他`Utils`工具可以直接使用

## 说明

- `workerman`环境下不支持`\config`配置使用
- `workerman`环境下不支持`CoroutineWebServer`进程服务
- `workerman`环境支持`Utils`工具包下的所有工具类
   > `Utils`工具包请参考：[`Utils`说明](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/utils.md)
