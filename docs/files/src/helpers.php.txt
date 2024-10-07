<?php
/**
 * @author workbunny/Chaz6chez
 * @email chaz6chez1993@outlook.com
 */
declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

// 设置环境变量
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
 * 判断是否composer安装了指定包
 *
 * @param string $packageName 包名，如 "workerman/workerman"
 * @return bool 是否安装
 */
function package_installed(string $packageName): bool
{
    $composerFile = dirname(__DIR__) . '/composer.json';
    if (!file_exists($composerFile)) {
        return false;
    }
    $composerData = json_decode(file_get_contents($composerFile), true);

    return isset($composerData['require'][$packageName]);
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
