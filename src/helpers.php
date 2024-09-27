<?php

declare(strict_types=1);

namespace Workbunny\WebmanCoroutine;

/**
 * 根据环境加载event-loop
 *
 * @return string event-loop类名
 */
function event_loop(): string
{
    return Factory::find(true);
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
