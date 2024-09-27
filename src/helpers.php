<?php

declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

/**
 * 根据环境加载event-loop
 *
 * @param string|null $expectEventLoopClass 如果传入期待的eventloop而判定当前环境不支持则返回空字符串使用默认eventloop
 * @return string
 */
function event_loop(?string $expectEventLoopClass = null): string
{
    return $expectEventLoopClass
        // 如果传入期待值，则会对期待值判定
        ? Factory::get($expectEventLoopClass, true, true)
        // 否则根据环境自动判定
        : Factory::find(true);
}

/**
 * 判断是否composer安装了指定包
 *
 * @param string $packageName
 * @return bool
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
