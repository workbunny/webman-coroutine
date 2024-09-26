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
        return extension_loaded('swow') ? 'Workerman\Events\Swow' : '';
    }
    // supported version < workerman 5.x
    else {
        return extension_loaded('swow') ? SwowEvent::class : '';
    }
}