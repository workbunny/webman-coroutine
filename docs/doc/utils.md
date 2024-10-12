# Utils说明

## 特点

- `Utils`包提供了一个较为统一的代码工具，使得您在开发代码时无须关注底层驱动，减低使用难度；
- `Utils`包兼容`workerman`、`webman`上的协程与非协程环境，无需纠结因驱动切换带来的代码侵入问题，无需关注方案逃离问题；
- `Utils`包提供了统一的`interface`,支持自定义注入驱动，满足特殊开发需求，且不会因为自定义带来代码侵入问题
  > 自定义注入驱动请参考：[自定义拓展](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/custom.md)

## 功能

- 协程通道：[Utils/Channel](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Channel)
- 协程等待：[Utils/WaitGroup](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/WaitGroup)
- 协程：[Utils/Coroutine](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Coroutine)
- 自定义`Worker`：[Utils/Worker](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Worker)
- 对象池：[Utils/Pool](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Pool)

## 示例

### 基础使用

- 常见的协程使用
  ```php
  use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
  use Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup;

  $waitGroup = new WaitGroup();
  
  // 协程1
  $waitGroup->add();
  $coroutine1 = new Coroutine(function () use ($waitGroup) {
    // do something 
    echo 1 . PHP_EOL;
    $waitGroup->done();
  });
  
  // 协程2
  $waitGroup->add();
  $coroutine2 = new Coroutine(function () use ($waitGroup) {
    // do something 
    echo 2 . PHP_EOL;
    $waitGroup->done();
  });
  
  $waitGroup->wait();
  echo 'done' . PHP_EOL;
  ```
  > Tips:
  > - 协程环境不一定会顺序输出1、2，但最后会输出`done`
  > - 非协程环境会顺序输出1、2，最后输出`done`

- 协程通道的使用
  ```php
  use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
  use Workbunny\WebmanCoroutine\Utils\Channel\Channel;

  $channel = new Channel();
  
  $coroutine1 = new Coroutine(function () use ($channel) {
    // do something
    
    $channel->push([
        // some data 1
    ]);
  });
  
  $coroutine2 = new Coroutine(function () use ($channel) {
    // do something
    
    $channel->push([
        // some data 2
    ]);
  });
  
  while (1) {
    if (!$data = $channel->pop()) {
        break;
    }
    // use $data do something
  }
  echo 'done' . PHP_EOL;
  ```
  > Tips:
  > - 协程环境不一定会顺序拿到`data 1`、`data 2`，但最后会输出`done`
  > - 非协程环境会顺序拿到`data 1`、`data 2`，最后输出`done`

### 自定义`Worker`

#### 普通进程

- 原代码，使用workerman的worker启动4个进程输出start和stop

  ```php

  use Workerman\Worker;

  $worker = new Worker();
  $worker->name = 'normal-worker';
  $worker->count = 4;
  $worker->onWorkerStart = function () {
      echo 'start' . PHP_EOL;
  }
  $worker->onWorkerStop = function () {
      echo 'stop' . PHP_EOL;
  }
  Worker::runAll();
  ```

- 按如下修改即可协程化

  ```php
  // 注释原Worker引入
  //use Workerman\Worker;
  //使用Utils worker 
  use Workbunny\WebmanCoroutine\Utils\Worker\Worker;

  $worker = new Worker();
  // 增加eventLoop的指定
  \Workerman\Worker::$eventLoopClass = \Workbunny\WebmanCoroutine\event_loop();

  $worker->name = 'normal-worker';
  $worker->count = 4;
  $worker->onWorkerStart = function () {
      echo 'start' . PHP_EOL;
  }
  $worker->onWorkerStop = function () {
      echo 'stop' . PHP_EOL;
  }
  Worker::runAll();
  ```
  > Tips：上述代码即可协程化进程的`onWorkerStart`、`onWorkerStop`执行逻辑

#### 带网络监听的进程

- 原代码，使用workerman的worker启动4个进程监听http

  ```php
  
  use Workerman\Worker;
  
  $worker = new Worker('http://[::]:8080');
  $worker->name = 'normal-worker';
  $worker->count = 4;
  $worker->onConnect = function () {
      // do something
  }
  $worker->onClose = function () {
      // do something
  }
  $worker->onMessage = function () {
      // do something
  }
  Worker::runAll();
  ```

- 按如下修改即可协程化

  ```php
  // 注释原Worker引入
  //use Workerman\Worker;
  //使用Utils server 
  use Workbunny\WebmanCoroutine\Utils\Worker\Server as Worker;
  
  $worker = new Worker('http://[::]:8080');
  // 增加eventLoop的指定
  \Workerman\Worker::$eventLoopClass = \Workbunny\WebmanCoroutine\event_loop();
  
  $worker->name = 'normal-worker';
  $worker->count = 4;
  $worker->onConnect = function () {
      // do something
  }
  $worker->onClose = function () {
      // do something
  }
  $worker->onMessage = function () {
      // do something
  }
  Worker::runAll();
  ```
  > Tips：
  > - 上述代码即可协程化进程的`onMessage`、`onConnect`、`onClose`执行逻辑
  > - 除了http协议，还支持ws、tcp、udp等协议

### 对象池

// todo
