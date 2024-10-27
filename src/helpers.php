<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

// 设置环境变量
use Composer\InstalledVersions;
use Workbunny\WebmanCoroutine\Exceptions\TimeoutException;
use Workbunny\WebmanCoroutine\Handlers\HandlerInterface;

putenv('WORKBUNNY_COROUTINE=1');

/**
 * 根据环境加载event-loop
 *
 * @param string|null $expectEventLoopClass 如果传入期待的eventloop而判定当前环境不支持则返回空字符串使用默认eventloop
 * @return string 事件loop类名
 */
function event_loop(?string $expectEventLoopClass = null): string
{
    Factory::init($expectEventLoopClass);

    return Factory::getCurrentEventLoop();
}

/**
 * 协程等待
 *
 * @see HandlerInterface::waitFor()
 * @param \Closure|null $action 等待回调
 * @param float|int $timeout 超时时间
 * @return void
 * @throws TimeoutException
 */
function wait_for(?\Closure $action, float|int $timeout = -1): void
{
    if (($handler = Factory::getCurrentHandler()) === null) {
        Factory::init(null);
        /** @var HandlerInterface $handler */
        $handler = Factory::getCurrentHandler();
    }
    $handler::waitFor($action, $timeout);
}

/**
 * 协程睡眠
 *
 * @see HandlerInterface::sleep()
 * @param float|int $timeout
 * @param string|null $event 协程事件名
 * @return void
 */
function sleep(float|int $timeout = 0, ?string $event = null): void
{
    if (($handler = Factory::getCurrentHandler()) === null) {
        Factory::init(null);
        /** @var HandlerInterface $handler */
        $handler = Factory::getCurrentHandler();
    }
    $handler::sleep($timeout, $event);
}

/**
 * 协程唤醒
 *
 * @see HandlerInterface::wakeup()
 * @param string $event 协程事件名
 * @return void
 */
function wakeup(string $event): void
{
    if (($handler = Factory::getCurrentHandler()) === null) {
        Factory::init(null);
        /** @var HandlerInterface $handler */
        $handler = Factory::getCurrentHandler();
    }
    $handler::wakeup($event);
}

/**
 * 判断是否composer安装了指定包
 *
 * @param string $packageName 包名，如 "workerman/workerman"
 * @return bool 是否安装
 */
function package_installed(string $packageName): bool
{
    return InstalledVersions::isInstalled($packageName);
}

/**
 * 判断是否在workbunny协程环境
 *
 * @return bool
 */
function is_coroutine_env(): bool
{
    return !getenv('WORKBUNNY_COROUTINE') === false;
}
