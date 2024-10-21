# Utils Description

## Features

- The `Utils` package provides a unified code tool, reducing the complexity of focusing on underlying drivers during development.
- The `Utils` package is compatible with both coroutine and non-coroutine environments on `workerman` and `webman`, eliminating the hassle of code intrusion due to driver switching and the issue of solution abandonment.
- The `Utils` package provides a unified interface, supporting custom driver injection to meet special development needs without causing code intrusion issues.
  > For custom driver injection, please refer to: [Custom Extensions](https://github.com/workbunny/webman-coroutine/tree/main/docs/doc/custom.md)

## Functions

- Coroutine Channel: [Utils/Channel](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Channel)
- Coroutine Wait Group: [Utils/WaitGroup](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/WaitGroup)
- Coroutine: [Utils/Coroutine](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Coroutine)
- Custom `Worker`: [Utils/Worker](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Worker)
- Object Pool: [Utils/Pool](https://github.com/workbunny/webman-coroutine/tree/main/src/Utils/Pool)

## Examples

### Basic Usage

> The following examples include the basic usage of coroutine channels, coroutine wait groups, and coroutines. 
> The general theoretical content of these tools can be referenced from similar tools in other languages.

- Common usage of coroutines
  ```php
  use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
  use Workbunny\WebmanCoroutine\Utils\WaitGroup\WaitGroup;

  $waitGroup = new WaitGroup();
  
  // Coroutine 1
  $waitGroup->add();
  $coroutine1 = new Coroutine(function () use ($waitGroup) {
    // do something 
    echo 1 . PHP_EOL;
    $waitGroup->done();
  });
  
  // Coroutine 2
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
  > - In a coroutine environment, 1 and 2 may not be output in sequence, but `done` will be output last
  > - In a non-coroutine environment, 1 and 2 will be output in sequence, with `done` being output last

- Using coroutine channels
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
  > - In a coroutine environment, `data 1` and `data 2` may not be received in sequence, but `done` will be output last
  > - In a non-coroutine environment, `data 1` and `data 2` will be received in sequence, with `done` being output last

### Custom `Worker`

> - Custom `Worker` includes the coroutine implementation of` Workerman`\`Worker` and upper-level application listening class process and normal process. 
> This requires some simple code intrusions and is compatible with pre-coroutine logic.
> - To use `Utils\Worker` as a custom process in the `webman` framework, you need to specify the process class in the process configuration: `'workerClass' => {Custom Process Class Based on Utils\Worker}`

#### Normal Process

- Original code, using workerman's worker to start 4 processes to output `start` and `stop`

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

- Modify as follows to achieve coroutinization

  ```php
  // Comment out the original Worker import
  //use Workerman\Worker;
  // Use Utils worker 
  use Workbunny\WebmanCoroutine\Utils\Worker\Worker;

  $worker = new Worker();
  // Specify eventLoop
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
  > Tips：The above code achieves the coroutinization of the execution logic of `onWorkerStart` and `onWorkerStop`

#### Process with Network Listening

- Original code, using `workerman`'s worker to start 4 processes to listen for http

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

- Modify as follows to achieve coroutinization

  ```php
  // Comment out the original Worker import
  //use Workerman\Worker;
  // Use Utils server
  use Workbunny\WebmanCoroutine\Utils\Worker\Server as Worker;
  
  $worker = new Worker('http://[::]:8080');
  // Specify eventLoop
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
  > - The above code achieves the coroutinization of the execution logic of `onMessage`, `onConnect`, and `onClose`
  > - Supports not only the http protocol but also ws, tcp, udp, and other protocols

### Object Pool

#### Description

- Scalar data in PHP is represented by `zval` in C, essentially residing on the C heap
  - In simple terms: with `$a=1`, both `$a` and `1` are stored in it
  - `zval` can be simply understood as the PHP stack
- Objects and other data in PHP are represented by `zheap` in C, essentially also residing on the C heap
  - In simple terms: with `$a=new stdClass()`, `$a` and the address of `new stdClass()` are stored in `zval`, while `new stdClass()` is stored in `zheap`
  - `zheap` can be simply understood as the PHP heap. It is a memory block allocated by the Zend memory allocator. `zheap` refers to all structures allocated by the memory allocator, such as `zend_object`.
  - For more on PHP heap data recovery strategies, refer to the PHP official documentation on GC and use functions starting with gc for operations
- Stacked coroutines will automatically manage register information and stack data for the context, but heap data is **not concurrently safe** in coroutines
- Object pools provide deep copy operations for PHP heap data with `partial support`
  - `callable | Closure` type data may reference context, making deep copying of referenced context unfeasible
  - `object` type may have static properties that cannot be recursively cloned
  - `resource` type involves resource handles, connections, etc., which cannot be cloned
  - `array` type is a special composite type, which may have a combination of the above conditions.
- The object pool provides locking functions for PHP heap data.

#### Example

- Pooled Copy

  ```php
  use Workbunny\WebmanCoroutine\Utils\Pool\Pool;

  $source = new class {
    public $id = 1;
  };

  // Pool two copies of the source object into the "normal-object" region, starting with index 1
  Pool::create('normal-object', 2, $source, true);

  // At this point, there are three source objects in the heap, with two in the "normal-object" region of the Pool

  // Wait to acquire an idle source object from the "normal-object" region and execute the callback
  // Due to the copy, the original source object's id will not be modified after callback execution
  Pool::waitForIdle('normal-object', function (Pool $pool) {
    $sourceObject = $pool->getElement();
    $sourceObject->id = 2;
  });

  // Output 1
  echo $source->id;

  // Acquire the object at index 1 in the region and wait for it to become idle before executing the callback
  $source1 = Pool::get('normal-object', 1);
  $source1->wait(function (Pool $pool) {
    $sourceObject = $pool->getElement();
    $sourceObject->id = 3;
  });

  // Output 1
  echo $source->id;
  // Output 3
  echo $source1->id;

  // Get the current idle object, returns null if none is available
  $source1 = Pool::idle('normal-object');
  try {
    $source1?->setIdle(false);
    // Execute some operations
  } finally {
    // Release
    $source1?->setIdle(true);
  }

  // Wait to acquire an idle object and lock it
  $source1 = Pool::getIdle('normal-object');
  try {
    // Execute some operations
  } finally {
    // Release
    $source1->setIdle(true);
  }

  // Wait for up to 10 seconds to acquire an idle object and lock it
  $source1 = Pool::getIdle('normal-object', 10);
  try {
    // Execute some operations
  } finally {
    // Release
    $source1->setIdle(true);
  }

  // Destroy the object at index 1 in the region
  Pool::destroy('normal-object', 1);

  // Forcefully destroy the object at index 2 in the region
  Pool::destroy('normal-object', 2, true);

  // Destroy all objects in the "normal-object" region
  Pool::destroy('normal-object');
  ```
  > Tips:
  > - Can be used for pooling object data during main thread initialization
  > - Can be used for deep copying objects, but is not fully supported; please refer to the documentation for details

- Resource object lock

  ```php
  use Workbunny\WebmanCoroutine\Utils\Pool\Pool;
  $source = new class {
    public $id = 1;
  }
  // Place the source object in the region named normal-object without copying, resulting in an additional reference to the source object in the PHP stack
  Pool::create('normal-object', 1, $source, false);
  
  // At this point, there is one source object in the heap data, and the normal-object region of the pool references the source object

  // Wait for an idle source object in the normal-object region, and execute the callback when one is available
  // Timeout is 10 seconds; if it exceeds this duration, a TimeoutException is thrown
  Pool::waitForIdle('normal-object', function (Pool $pool) {
    $sourceObject = $pool->getElement();
    $source->id = 2;
  }, 10);
  
  // ...
  ```
  > Tips:
  > - Can be used to lock operations on resource objects, preventing competition between coroutines
  > - In a coroutine environment, `waitForIdle` makes the current thread non-blocking, while in a non-coroutine environment, the current thread is blocking. This is implemented using the `wait_for()` function.

- Placeholder Initialization, Dynamic Pooling

  > When using the `webman` framework controller, repeated calls to the `Pool::create()` method will create duplicate regions and throw exceptions. We can use it as follows
  - Placeholder initialization in `bootstrap.php`
  ```php
  use Workbunny\WebmanCoroutine\Utils\Pool\Pool;
  
  // Placeholder for redis
  Pool::init('redis', false);
  // Placeholder for mysql
  Pool::init('mysql', false);
  // Placeholder for normal-object
  Pool::init('normal-object', false);
  // ...other
  
  // At this point, the Pool is just a placeholder
  ```
  - Then add objects in the corresponding logical region for dynamic pooling
  
  ```php
  use Workbunny\WebmanCoroutine\Utils\Pool\Pool;
  
  $pools = Pool::get('redis');
  // Determine if additional objects need to be added based on the pool size configuration, assuming you have such a configuration
  if (count($pools) < config('redis.pool_size')) {
    // Create a new Redis connection
    $redis = new RedisManager();
    // Append a Redis connection object without cloning the resource type
    Pool::append('redis', (int)array_key_last($pools) + 1, $redis, false);
  }
  
  // Wait for an idle redis
  $res = Pool::waitForIdle('redis', function (Pool $pool) {
    $redis = $pool->getElement();
    return $redis->client()->set('key', 'value');
  });
  
  // other
  
  // ...
  ```

- Debugger Assistant

  > Due to the special nature of some data, they may be unsafe in a coroutine environment. 
  > Therefore, some debugging assistants are provided to check whether the data has potential risks and insecurities.

  - Object has a static array property
  ```php
  $object = new class () {
    public static $arr = [1, 2, 3];
  };
  try {
    Debugger::validate($object);
  } catch (PoolDebuggerException $e) {
    // $e->getCode() = Debugger::ERROR_TYPE_STATIC_ARRAY
    // $e->getMessage() = 'Value can not be cloned [static array]. '
  }
  ```

  - Object has a static object property
  ```php
  $object = new class () {
    public static $object = null;

    public function __construct()
    {
       self::$object = new stdClass();
    }
  };
  try {
    Debugger::validate($object);
  } catch (PoolDebuggerException $e) {
    // $e->getCode() = Debugger::ERROR_TYPE_STATIC_OBJECT
    // $e->getMessage() = 'Value can not be cloned [static object]. '
  }
  ```

  - For more usage, please refer to the test case `tests/UtilsCase/Pool/DebuggerTest.php`

- more，TODO
