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

> 以下示例包含了协程通道、协程等待、协程的基础使用方式，关于其工具的大致理论内容可以参考同类型其他语言的相同工具。

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

> - 自定义`Worker`包含了对`Workerman\Worker`及上层应用`监听类进程`、`普通进程`的协程化实现，需要一些简单的代码入侵，兼容协程化前的逻辑。
> - 在`webman框架`中使用`Utils\Worker`作为自定义进程需要在`process`配置中指定进程类：`'workerClass' => {基于Utils\Worker实现的自定义进程类}`

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

#### 说明

- PHP中的标量数据在C中是以`zval`体现的，本质上是在C堆上的
  - 简单理解：以`$a=1`举例，`$a`和`1`都保存在其中
  - `zval`可以简单理解为PHP栈
- PHP中的对象等数据是在C中以`zheap`体现的，本质上也是在C堆上
  - 简单理解：以`$a=new stdClass()`举例，`$a`和`new stdClass()的地址`保存在`zval`其中,而`new stdClass()`保存在`zheap`中
  - `zheap`可以简单理解为PHP堆
  - PHP堆的相关数据回收策略，可以详见PHP官方文档关于GC部分，可使用gc开头的函数进行操作
- `有栈协程`对于上下文会自动管理`寄存器信息`和`栈数据`，除此之外的`堆数据`是**非协程并发安全**的
- 对象池提供了对PHP堆数据的深拷贝操作`不完全支持`
  - `callable | Closure` 类型数据因为可能存在引用上下文，无法对引用的上下文进行深拷贝
  - `object` 类型可能存在静态属性，静态属性无法递归clone
  - `resource` 类型存在资源句柄、连接等信息，所以无法clone
  - `array` 类型是一种特殊的复合类型，可能存在以上所有情况的竞合
- 对象池提供了对PHP堆数据的锁功能

#### 示例

- 池化拷贝

  ```php
  use Workbunny\WebmanCoroutine\Utils\Pool\Pool;
  $source = new class {
    public $id = 1;
  }
  // 池化拷贝2个source的对象，放入名为normal-object的区域，区域索引以1开始
  Pool::create('normal-object', 2, $source, true);
  
  // 此时堆数据中存在三个source对象，其中Pool池的normal-object区域存在两个source对象
  
  // 等待获取normal-object区域闲置的source对象，获取到时，执行回调
  // 由于拷贝，回调执行后不会修改原有source对象的id
  Pool::waitForIdle('normal-object', function ($sourceObject) {
    $sourceObject->id = 2;
  });
  // 输出 1
  echo $source->id;
  
  // 获取区域索引为1的对象，等待闲置时执行回调
  $source1 = Pool::get('normal-object', 1);
  $source1->wait(function ($sourceObject) {
    $sourceObject->id = 3;
  });
  // 输出 1
  echo $source->id;
  // 输出 3
  echo $source1->id;
  
  // 获取当前闲置对象，未获取到时返回null
  $source1 = Pool::idle('normal-object');
  
  // 销毁区域索引为1的对象
  Pool::destroy('normal-object', 1);
  // 强制销毁区域索引为2的对象
  Pool::destroy('normal-object', 2， true);
  // 销毁区域normal-object所有对象
  Pool::destroy('normal-object');
  ```
  > Tips:
  > - 可用于主线程初始化时对象数据的池化操作
  > - 可用于对对象的深拷贝操作`不完全支持，请参考说明`

- 资源对象锁

  ```php
  use Workbunny\WebmanCoroutine\Utils\Pool\Pool;
  $source = new class {
    public $id = 1;
  }
  // 将source的对象放入名为normal-object的区域，不进行拷贝，PHP栈中多出一次对source对象的引用
  Pool::create('normal-object', 1, $source, false);
  
  // 此时堆数据中存在1个source对象，其中Pool池的normal-object区域是引用的source对象

  // 等待获取normal-object区域闲置的source对象，获取到时，执行回调
  // 超时时间10秒，如果超时则抛出TimeoutException
  Pool::waitForIdle('normal-object', function ($source) {
    $source->id = 2;
  }, 10);
  
  // ...
  ```
  > Tips:
  > - 可用于为资源对象的操作加锁，避免协程间的竞争状态
  > - `waitForIdle`在协程环境下当前线程非阻塞，非协程环境下当前线程阻塞，基建使用`wait_for()`函数实现

- 更多使用，TODO
