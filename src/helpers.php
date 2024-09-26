<?php

declare(strict_types=1);

namespace Workbunny\WebmanSwow;


use Workerman\Worker;

/**
 * 根据环境加载event-loop
 *
 * @return string event-loop类名
 */
function event_loop(): string
{
    // supported workerman 5.x
    if (version_compare(Worker::VERSION, '5.0.0', '>=')) {
        if (extension_loaded('swow')) {
            return CoroutineWebServer::WORKERMAN_SWOW;
        }
        if (extension_loaded('swoole')) {
            return CoroutineWebServer::WORKERMAN_SWOOLE;
        }
    }
    // supported version < workerman 5.x
    else {
        if (extension_loaded('swow')) {
            return CoroutineWebServer::WORKBUNNY_SWOW;
        }
        if (extension_loaded('swoole')) {
            return CoroutineWebServer::WORKBUNNY_SWOOLE;
        }
    }

    return '';
}