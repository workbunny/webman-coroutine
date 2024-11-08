<?php

declare(strict_types=1);

namespace Workbunny\Tests\UtilsCase\Coroutine;

use Mockery;
use Workbunny\Tests\TestCase;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Coroutine;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\CoroutineInterface;
use Workbunny\WebmanCoroutine\Utils\Coroutine\Handlers\DefaultCoroutine;

class CoroutineTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testConstruct()
    {
        $executed = false;
        $func = function () use (&$executed) {
            $executed = true;
        };

        $mockInterface = Mockery::mock(CoroutineInterface::class);
        $mockInterface->shouldReceive('__construct')
            ->with($func);

        $channel = Mockery::mock(Coroutine::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $channel->shouldReceive('__destruct')
            ->andReturnNull();

        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_interface');
        $property->setAccessible(true);
        $property->setValue($channel, $mockInterface);

        $channel->__construct($func);

        $this->assertInstanceOf(Coroutine::class, $channel);
        $this->assertTrue($executed);
    }

    public function testDestruct()
    {
        $func = function () {
            // 模拟闭包函数的执行
        };

        $mockInterface = Mockery::mock(CoroutineInterface::class);
        $mockInterface->shouldReceive('__construct')
            ->with($func);

        $channel = new Coroutine($func);

        $reflection = new \ReflectionClass($channel);
        $property = $reflection->getProperty('_interface');
        $property->setAccessible(true);
        $property->setValue($channel, $mockInterface);

        $channel->__construct($func);
        // 调用析构函数
        $channel->__destruct();

        $this->assertTrue(true);
    }

    public function testRegisterVerify()
    {
        $value = Mockery::mock(CoroutineInterface::class);
        $result = Coroutine::registerVerify($value);

        $this->assertEquals(CoroutineInterface::class, $result);

        $value = new \stdClass();
        $result = Coroutine::registerVerify($value);

        $this->assertFalse($result);
    }

    public function testUnregisterExecute()
    {
        $result = Coroutine::unregisterExecute('some_key');
        $this->assertTrue($result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testKill()
    {
        $this->assertEquals(0, Coroutine::getCoroutinesWeakMap()->count());
        $coroutine = new Coroutine($func = function () {});
        $this->assertEquals(1, Coroutine::getCoroutinesWeakMap()->count());

        Coroutine::kill($interface = $coroutine->getCoroutineInterface());

        $this->assertInstanceOf(DefaultCoroutine::class, $interface);

        // DefaultCoroutine在初始化时就执行，所以id为null，这里通过null来进行kill测试
        Coroutine::kill(null);
        $this->assertTrue(true);
    }
}
