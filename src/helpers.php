<?php

declare(strict_types=1);

namespace Workbunny\WebmanSwow;

/**
 * 根据环境加载event-loop
 *
 * @return string event-loop类名
 */
function event_loop(): string
{
    return Factory::find(true);
}
